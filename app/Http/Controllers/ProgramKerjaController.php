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

        // Published PKA templates for clone
        $pkaTemplates = ProgramKerja::whereNull('st_id')
            ->where('status', 'published')
            ->withCount('langkah')
            ->latest()
            ->get();

        return view('program-kerja.create', compact('suratTugas', 'selectedStId', 'pkaTemplates'));
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
            'template_id' => 'nullable|exists:program_kerja,id',
            'langkah' => 'nullable|array',
            'langkah.*.judul' => 'required|string|max:255',
            'langkah.*.deskripsi' => 'nullable|string',
            'langkah.*.jenis_prosedur' => 'nullable|string',
            'langkah.*.target_hari' => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $pka = ProgramKerja::create([
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

            // Clone langkah from template if template_id is provided
            if ($request->template_id) {
                $this->cloneTemplateLangkah($request->template_id, $pka->id);
            }

            // Save additional manual langkah
            if ($request->langkah) {
                $startUrutan = PkLangkah::where('program_kerja_id', $pka->id)->max('urutan') ?? 0;
                foreach ($request->langkah as $index => $langkahData) {
                    if (!empty($langkahData['judul'])) {
                        PkLangkah::create([
                            'program_kerja_id' => $pka->id,
                            'urutan' => $startUrutan + $index + 1,
                            'judul' => $langkahData['judul'],
                            'deskripsi' => $langkahData['deskripsi'] ?? null,
                            'jenis_prosedur' => $langkahData['jenis_prosedur'] ?? null,
                            'target_hari' => $langkahData['target_hari'] ?? null,
                            'from_template' => false,
                        ]);
                    }
                }
            }
        });

        return redirect()->route('program-kerja.index')
            ->with('success', 'Program Kerja berhasil dibuat!');
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

        return view('program-kerja.show', compact('pka', 'kertasKerjaList', 'teamMembers', 'canManage'));
    }

    /**
     * Form edit Program Kerja.
     */
    public function edit($id)
    {
        $pka = ProgramKerja::with(['suratTugas', 'langkahRoot.children'])->findOrFail($id);
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
            'langkah' => 'nullable|array',
            'langkah.*.id' => 'nullable|integer',
            'langkah.*.judul' => 'required|string|max:255',
            'langkah.*.deskripsi' => 'nullable|string',
            'langkah.*.jenis_prosedur' => 'nullable|string',
            'langkah.*.target_hari' => 'nullable|integer|min:1',
        ]);

        DB::transaction(function () use ($request, $pka) {
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

            // Sync langkah: keep existing with ID, create new, delete removed
            $existingIds = [];

            if ($request->langkah) {
                foreach ($request->langkah as $index => $langkahData) {
                    if (!empty($langkahData['judul'])) {
                        if (!empty($langkahData['id'])) {
                            // Update existing
                            $langkah = PkLangkah::find($langkahData['id']);
                            if ($langkah && $langkah->program_kerja_id == $pka->id) {
                                $langkah->update([
                                    'urutan' => $index + 1,
                                    'judul' => $langkahData['judul'],
                                    'deskripsi' => $langkahData['deskripsi'] ?? null,
                                    'jenis_prosedur' => $langkahData['jenis_prosedur'] ?? null,
                                    'target_hari' => $langkahData['target_hari'] ?? null,
                                ]);
                                $existingIds[] = $langkah->id;
                            }
                        } else {
                            // Create new
                            $newLangkah = PkLangkah::create([
                                'program_kerja_id' => $pka->id,
                                'urutan' => $index + 1,
                                'judul' => $langkahData['judul'],
                                'deskripsi' => $langkahData['deskripsi'] ?? null,
                                'jenis_prosedur' => $langkahData['jenis_prosedur'] ?? null,
                                'target_hari' => $langkahData['target_hari'] ?? null,
                            ]);
                            $existingIds[] = $newLangkah->id;
                        }
                    }
                }
            }

            // Delete removed langkah (only non-template, without assignments or KK links)
            PkLangkah::where('program_kerja_id', $pka->id)
                ->whereNotIn('id', $existingIds)
                ->where('from_template', false) // Protect template langkah
                ->whereDoesntHave('assignments')
                ->whereNull('kertas_kerja_id')
                ->delete();
        });

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
     * Link langkah ke Kertas Kerja.
     */
    public function linkKertasKerja(Request $request, $id)
    {
        $request->validate([
            'kertas_kerja_id' => 'nullable|exists:kertas_kerja,id',
        ]);

        $langkah = PkLangkah::findOrFail($id);

        $langkah->update([
            'kertas_kerja_id' => $request->kertas_kerja_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => $request->kertas_kerja_id
                ? 'Langkah berhasil dihubungkan dengan Kertas Kerja!'
                : 'Link Kertas Kerja berhasil dihapus.',
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
                'status' => 'pending',
                'from_template' => true,
            ]);

            // Recursively clone children
            $this->cloneTemplateLangkah($templateId, $targetPkaId, $langkah->id, $newLangkah->id);
        }
    }
}
