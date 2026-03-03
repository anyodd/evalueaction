<?php

namespace App\Http\Controllers;

use App\Models\ProgramKerja;
use App\Models\PkLangkah;
use App\Models\PkAssignment;
use App\Models\SuratTugas;
use App\Models\StPersonel;
use App\Models\KertasKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProgramKerjaController extends Controller
{
    /**
     * Daftar Program Kerja (filtered by role).
     */
    public function index()
    {
        $user = auth()->user();
        $roleName = $user->role->name;

        $query = ProgramKerja::whereNotNull('st_id') // Exclude templates
            ->with(['suratTugas.perwakilan', 'suratTugas.jenisPenugasan', 'creator', 'langkah'])->latest();

        if (in_array($roleName, ['Superadmin', 'Rendal'])) {
            // See all
        } elseif (in_array($roleName, ['Admin Perwakilan', 'Korwas'])) {
            $query->whereHas('suratTugas', fn($q) => $q->where('perwakilan_id', $user->perwakilan_id));
        } else {
            // Ketua Tim, Anggota, Dalnis — only see PKA for their ST assignments
            $query->whereHas('suratTugas.personel', fn($q) => $q->where('user_id', $user->id));
        }

        $programKerja = $query->get();

        return view('program-kerja.index', compact('programKerja'));
    }

    /**
     * Form buat Program Kerja baru.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $roleName = $user->role->name;

        // Get available Surat Tugas
        $stQuery = SuratTugas::with(['perwakilan', 'jenisPenugasan']);

        if (in_array($roleName, ['Superadmin', 'Rendal'])) {
            // all
        } elseif (in_array($roleName, ['Admin Perwakilan', 'Korwas'])) {
            $stQuery->where('perwakilan_id', $user->perwakilan_id);
        } else {
            $stQuery->whereHas('personel', fn($q) => $q->where('user_id', $user->id));
        }

        $suratTugas = $stQuery->latest()->get();
        $selectedStId = $request->get('st_id');

        return view('program-kerja.create', compact('suratTugas', 'selectedStId'));
    }

    /**
     * Simpan Program Kerja baru + langkah-langkah.
     */
    public function store(Request $request)
    {
        $request->validate([
            'st_id' => 'required|exists:surat_tugas,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'ruang_lingkup' => 'nullable|string',
            'metodologi' => 'nullable|string',
            'tgl_mulai' => 'nullable|date',
            'tgl_selesai' => 'nullable|date|after_or_equal:tgl_mulai',
        ]);

        $pka = DB::transaction(function () use ($request) {
            $createdPka = ProgramKerja::create([
                'st_id' => $request->st_id,
                'judul' => $request->judul,
                'deskripsi' => $request->deskripsi,
                'tujuan' => $request->tujuan,
                'ruang_lingkup' => $request->ruang_lingkup,
                'metodologi' => $request->metodologi,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'created_by' => auth()->id(),
                'status' => 'draft',
            ]);

            // Auto-generate Langkah Kerja Standar from SuratTugas -> KkTemplate
            $createdPka->load('suratTugas');
            $templateId = $createdPka->suratTugas->template_id;

            if ($templateId) {
                // Get all Parameter Indicators (level 3) for this Template
                $indicators = \App\Models\TemplateIndicator::where('template_id', $templateId)->get();
                $indicatorIds = $indicators->pluck('id');

                if ($indicatorIds->isNotEmpty()) {
                    // Get all target langkah standards
                    $langkahStandars = \App\Models\TemplateLangkah::whereIn('indicator_id', $indicatorIds)->get();

                    if ($langkahStandars->isNotEmpty()) {
                        // Group by indicator_id to manage starting urutan per indicator
                        $langkahCounter = [];

                        $insertData = [];
                        foreach ($langkahStandars as $ls) {
                            $indId = $ls->indicator_id;
                            if(!isset($langkahCounter[$indId])) $langkahCounter[$indId] = 1;
                            
                            $insertData[] = [
                                'program_kerja_id' => $createdPka->id,
                                'urutan' => $langkahCounter[$indId]++,
                                'judul' => $ls->uraian,
                                'jenis_prosedur' => $ls->jenis_prosedur,
                                'template_indicator_id' => $indId,
                                'from_template' => true,
                                'is_mandatory' => true,
                                'status' => 'pending',
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }

                        if (!empty($insertData)) {
                            PkLangkah::insert($insertData);
                        }
                    }
                }
            }

            return $createdPka;
        });

        return redirect()->route('program-kerja.show', $pka->id)
            ->with('success', 'Program Kerja berhasil dibuat beserta Langkah Kerja Standar.');
    }

    /**
     * Detail Program Kerja.
     */
    public function show($id)
    {
        $pka = ProgramKerja::with([
            'suratTugas.perwakilan',
            'suratTugas.jenisPenugasan',
            'suratTugas.personel.user',
            'creator',
            'langkahRoot.children',
            'langkahRoot.assignments.user',
            'langkahRoot.kertasKerja',
            'langkahRoot.kkTemplate',
            'langkahRoot.children.kkTemplate',
        ])->findOrFail($id);

        $this->authorizeAccess($pka);

        // Get available Kertas Kerja for this ST (for linking)
        $kertasKerjaList = KertasKerja::where('st_id', $pka->st_id)->get();

        // Get team members for assignment
        $teamMembers = StPersonel::where('st_id', $pka->st_id)
            ->with('user')
            ->get();

        // Determine user capabilities
        $user = auth()->user();
        $roleName = $user->role->name;
        $roleInTeam = StPersonel::where('st_id', $pka->st_id)
            ->where('user_id', $user->id)
            ->value('role_dalam_tim');

        $canManage = in_array($roleName, ['Superadmin']) 
            || in_array($roleInTeam, ['Ketua Tim', 'Dalnis']);

        // Fetch KK Template Indicators hierarchy (Aspek -> Indikator -> Parameter)
        $kkTemplateId = $pka->suratTugas->template_id ?? null;
        $templateIndicators = collect();
        if ($kkTemplateId) {
            $templateIndicators = \App\Models\TemplateIndicator::where('template_id', $kkTemplateId)
                ->whereNull('parent_id') // Get root / Level 1 (Aspek)
                ->with(['children.children' => function($q) {
                    $q->with('criteria'); // Load criteria for Level 3 (Parameter)
                }])
                ->get();
        }

        // Fetch all langkahs for this PKA to group them by template_indicator_id
        $semuaLangkah = \App\Models\PkLangkah::where('program_kerja_id', $pka->id)
            ->with(['assignments.user', 'kertasKerja', 'templateIndicator', 'children'])
            ->orderBy('urutan')
            ->get();
            
        // Group by template_indicator_id. Langkah without indicator will be grouped under '' (empty string)
        $langkahByIndicator = $semuaLangkah->groupBy('template_indicator_id');

        return view('program-kerja.show', compact('pka', 'kertasKerjaList', 'teamMembers', 'canManage', 'templateIndicators', 'langkahByIndicator'));
    }

    /**
     * Form edit Program Kerja.
     */
    public function edit($id)
    {
        $pka = ProgramKerja::with(['suratTugas'])->findOrFail($id);
        $this->authorizeAccess($pka);

        $user = auth()->user();
        $roleName = $user->role->name;

        $stQuery = SuratTugas::with(['perwakilan', 'jenisPenugasan']);
        if (in_array($roleName, ['Superadmin', 'Rendal'])) {
            // all
        } elseif (in_array($roleName, ['Admin Perwakilan', 'Korwas'])) {
            $stQuery->where('perwakilan_id', $user->perwakilan_id);
        } else {
            $stQuery->whereHas('personel', fn($q) => $q->where('user_id', $user->id));
        }

        $suratTugas = $stQuery->latest()->get();

        return view('program-kerja.edit', compact('pka', 'suratTugas'));
    }

    /**
     * Update Program Kerja.
     */
    public function update(Request $request, $id)
    {
        $pka = ProgramKerja::findOrFail($id);
        $this->authorizeAccess($pka);

        $request->validate([
            'st_id' => 'required|exists:surat_tugas,id',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'ruang_lingkup' => 'nullable|string',
            'metodologi' => 'nullable|string',
            'status' => 'nullable|in:draft,active,completed,archived',
            'tgl_mulai' => 'nullable|date',
            'tgl_selesai' => 'nullable|date|after_or_equal:tgl_mulai',
        ]);

        $pka->update([
            'st_id' => $request->st_id,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tujuan' => $request->tujuan,
            'ruang_lingkup' => $request->ruang_lingkup,
            'metodologi' => $request->metodologi,
            'status' => $request->status ?? $pka->status,
            'tgl_mulai' => $request->tgl_mulai,
            'tgl_selesai' => $request->tgl_selesai,
        ]);

        return redirect()->route('program-kerja.show', $pka->id)
            ->with('success', 'Program Kerja berhasil diperbarui!');
    }

    /**
     * Hapus Program Kerja (draft only).
     */
    public function destroy($id)
    {
        $pka = ProgramKerja::findOrFail($id);
        $this->authorizeAccess($pka);

        if ($pka->status !== 'draft') {
            return back()->with('error', 'Hanya Program Kerja berstatus Draft yang dapat dihapus.');
        }

        $pka->delete();

        return redirect()->route('program-kerja.index')
            ->with('success', 'Program Kerja berhasil dihapus!');
    }

    /**
     * Store new Langkah for a PKA.
     */
    public function storeLangkah(Request $request, $id)
    {
        $pka = ProgramKerja::findOrFail($id);
        $this->authorizeAccess($pka);

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis_prosedur' => 'nullable|string',
            'target_hari' => 'nullable|integer|min:1',
            'template_indicator_id' => 'nullable|exists:template_indicators,id',
        ]);

        $maxUrutan = PkLangkah::where('program_kerja_id', $pka->id)
            ->where('template_indicator_id', $request->template_indicator_id)
            ->max('urutan') ?? 0;

        PkLangkah::create([
            'program_kerja_id' => $pka->id,
            'urutan' => $maxUrutan + 1,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis_prosedur' => $request->jenis_prosedur,
            'target_hari' => $request->target_hari,
            'template_indicator_id' => $request->template_indicator_id,
            'from_template' => false,
        ]);

        return redirect()->route('program-kerja.show', $pka->id)
            ->with('success', 'Langkah Kerja berhasil ditambahkan!');
    }

    /**
     * Delete a Langkah.
     */
    public function destroyLangkah($id)
    {
        $langkah = PkLangkah::with('programKerja')->findOrFail($id);
        $pka = $langkah->programKerja;
        $this->authorizeAccess($pka);

        if ($langkah->assignments()->count() > 0) {
            return back()->with('error', 'Langkah tidak dapat dihapus karena sudah memiliki penugasan.');
        }

        if ($langkah->kertas_kerja_id) {
            return back()->with('error', 'Langkah tidak dapat dihapus karena sudah dihubungkan dengan Kertas Kerja.');
        }

        $langkah->delete();

        return redirect()->route('program-kerja.show', $pka->id)
            ->with('success', 'Langkah Kerja berhasil dihapus!');
    }

    /**
     * Assign langkah ke anggota tim.
     */
    public function assignLangkah(Request $request)
    {
        $request->validate([
            'pk_langkah_id' => 'required|exists:pk_langkah,id',
            'user_id' => 'required|exists:users,id',
            'catatan' => 'nullable|string',
            'tgl_deadline' => 'nullable|date',
        ]);

        $langkah = PkLangkah::with('programKerja.suratTugas')->findOrFail($request->pk_langkah_id);

        // Verify user is part of the team
        $isTeamMember = StPersonel::where('st_id', $langkah->programKerja->st_id)
            ->where('user_id', $request->user_id)
            ->exists();

        if (!$isTeamMember) {
            return response()->json(['success' => false, 'message' => 'User bukan anggota tim ST ini.'], 422);
        }

        $assignment = PkAssignment::updateOrCreate(
            [
                'pk_langkah_id' => $request->pk_langkah_id,
                'user_id' => $request->user_id,
            ],
            [
                'assigned_by' => auth()->id(),
                'catatan' => $request->catatan,
                'tgl_deadline' => $request->tgl_deadline,
                'status' => 'assigned',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Langkah berhasil ditugaskan!',
            'assignment' => $assignment->load('user'),
        ]);
    }

    /**
     * Assign semua langkah dalam satu parameter ke anggota tim.
     */
    public function bulkAssignLangkah(Request $request)
    {
        $request->validate([
            'program_kerja_id' => 'required|exists:program_kerja,id',
            'parameter_id' => 'required|exists:template_indicators,id',
            'user_id' => 'required|exists:users,id',
        ]);

        $pka = ProgramKerja::with('suratTugas')->findOrFail($request->program_kerja_id);

        // Verify user is part of the team
        $isTeamMember = StPersonel::where('st_id', $pka->st_id)
            ->where('user_id', $request->user_id)
            ->exists();

        if (!$isTeamMember) {
            return response()->json(['success' => false, 'message' => 'User bukan anggota tim ST ini.'], 422);
        }

        // Get all langkah work within this parameter
        $langkahs = PkLangkah::where('program_kerja_id', $pka->id)
            ->where('template_indicator_id', $request->parameter_id)
            ->get();

        if ($langkahs->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada langkah kerja di parameter ini.'], 404);
        }

        DB::beginTransaction();
        try {
            foreach ($langkahs as $langkah) {
                // If the langkah is already assigned to this user, skip or update. 
                // Using updateOrCreate ensures they own it.
                // Note: the current system architecture allows multiple assignments per langkah, 
                // but usually it's one person per step. We will just add the assignment for this user.
                
                // First delete existing assignments for this step to avoid duplicates if 
                // we only want 1 assignee per step as standard practice in bulk
                PkAssignment::where('pk_langkah_id', $langkah->id)->delete();

                PkAssignment::create([
                    'pk_langkah_id' => $langkah->id,
                    'user_id' => $request->user_id,
                    'assigned_by' => auth()->id(),
                    'status' => 'assigned',
                ]);
            }
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Seluruh langkah pada parameter tersebut berhasil ditugaskan!',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan sistem.'], 500);
        }
    }

    /**
     * Update status langkah.
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,skipped',
            'catatan_hasil' => 'nullable|string',
        ]);

        $langkah = PkLangkah::with('programKerja')->findOrFail($id);

        $langkah->update([
            'status' => $request->status,
            'catatan_hasil' => $request->catatan_hasil ?? $langkah->catatan_hasil,
            'tgl_mulai' => $request->status === 'in_progress' && !$langkah->tgl_mulai ? now() : $langkah->tgl_mulai,
            'tgl_selesai' => $request->status === 'completed' ? now() : $langkah->tgl_selesai,
        ]);

        // Also update assignment status if exists for current user
        PkAssignment::where('pk_langkah_id', $id)
            ->where('user_id', auth()->id())
            ->update(['status' => $request->status === 'completed' ? 'completed' : 'in_progress']);

        // Auto-complete PKA if all langkah done
        $pka = $langkah->programKerja;
        $allCompleted = $pka->langkah()->whereNotIn('status', ['completed', 'skipped'])->count() === 0;
        if ($allCompleted && $pka->langkah()->count() > 0) {
            $pka->update(['status' => 'completed']);
        }

        return response()->json([
            'success' => true,
            'message' => 'Status langkah berhasil diperbarui!',
            'langkah' => $langkah->fresh(),
            'pka_progress' => $pka->progressPercentage(),
        ]);
    }



    /**
     * Remove assignment.
     */
    public function removeAssignment(Request $request)
    {
        $request->validate([
            'assignment_id' => 'required|exists:pk_assignment,id',
        ]);

        PkAssignment::destroy($request->assignment_id);

        return response()->json([
            'success' => true,
            'message' => 'Penugasan berhasil dihapus.',
        ]);
    }

    /**
     * Cetak Program Kerja.
     */
    public function print($id)
    {
        $pka = ProgramKerja::with([
            'suratTugas.perwakilan',
            'suratTugas.jenisPenugasan',
            'suratTugas.personel.user',
            'creator',
            'langkahRoot.children.assignments.user',
            'langkahRoot.assignments.user',
            'langkahRoot.kertasKerja',
        ])->findOrFail($id);

        return view('program-kerja.print', compact('pka'));
    }

    /**
     * Otorisasi akses berdasarkan role dan perwakilan.
     */
    private function authorizeAccess(ProgramKerja $pka)
    {
        $user = auth()->user();
        $roleName = $user->role->name;

        if (in_array($roleName, ['Superadmin', 'Rendal'])) {
            return; // Full access
        }

        if (in_array($roleName, ['Admin Perwakilan', 'Korwas'])) {
            if ($pka->suratTugas->perwakilan_id !== $user->perwakilan_id) {
                abort(403, 'Anda tidak memiliki akses ke Program Kerja ini.');
            }
            return;
        }

        // Ketua Tim, Anggota, Dalnis
        $isMember = StPersonel::where('st_id', $pka->st_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isMember) {
            abort(403, 'Anda bukan anggota tim untuk Program Kerja ini.');
        }
    }

    /**
     * Clone langkah from a template PKA into an active PKA.
     * All cloned langkah are marked with from_template = true.
     */
    private function cloneTemplateLangkah($templateId, $targetPkaId, $sourceParentId = null, $targetParentId = null)
    {
        $langkahList = PkLangkah::where('program_kerja_id', $templateId)
            ->where('parent_id', $sourceParentId)
            ->orderBy('urutan')
            ->get();

        foreach ($langkahList as $langkah) {
            $newLangkah = PkLangkah::create([
                'program_kerja_id' => $targetPkaId,
                'parent_id' => $targetParentId,
                'urutan' => $langkah->urutan,
                'judul' => $langkah->judul,
                'deskripsi' => $langkah->deskripsi,
                'jenis_prosedur' => $langkah->jenis_prosedur,
                'target_hari' => $langkah->target_hari,
                'kk_template_id' => $langkah->kk_template_id,
                'status' => 'pending',
                'from_template' => true,
            ]);

            // Recursively clone children
            $this->cloneTemplateLangkah($templateId, $targetPkaId, $langkah->id, $newLangkah->id);
        }
    }
}
