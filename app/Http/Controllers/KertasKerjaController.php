<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\KertasKerjaExport;
use App\Imports\KertasKerjaImport;
use Maatwebsite\Excel\Facades\Excel;

class KertasKerjaController extends Controller
{
    public function index()
    {
        if (auth()->user()->role && auth()->user()->role->name === 'Superadmin') {
            $assignments = \App\Models\SuratTugas::whereHas('programKerja')
                ->with(['jenisPenugasan', 'template', 'kertasKerja'])
                ->latest()
                ->get();
        } else {
            // 1. Ambil ST di mana user ditugaskan dan memiliki Program Kerja
            $assignments = \App\Models\SuratTugas::whereHas('programKerja')
                ->whereHas('personel', function($q) {
                    $q->where('user_id', auth()->id());
                })->with(['jenisPenugasan', 'template', 'kertasKerja'])
            ->latest()
            ->get();
        }

        return view('kertas-kerja.index', compact('assignments'));
    }

    public function generate($st_id)
    {
        $st = \App\Models\SuratTugas::findOrFail($st_id);
        
        // Validasi: Cek apakah user ada di dalam tim (Bypass untuk Superadmin)
        $isTim = \App\Models\StPersonel::where('st_id', $st->id)
            ->where('user_id', auth()->id())
            ->exists();

        $isSuperadmin = auth()->user()->role && auth()->user()->role->name === 'Superadmin';

        if (!$isTim && !$isSuperadmin) {
            abort(403, 'Anda tidak terdaftar dalam tim penugasan ini.');
        }

        // Cek apakah KK sudah ada untuk ST ini
        $kk = \App\Models\KertasKerja::where('st_id', $st->id)->first();

        if (!$kk) {
            $kk = \App\Models\KertasKerja::create([
                'st_id' => $st->id,
                'user_id' => auth()->id(),
                'template_id' => $st->template_id,
                'judul_kk' => 'Kertas Kerja - ' . $st->jenisPenugasan->nama,
                'status_approval' => 'Draft',
            ]);
        }

        return redirect()->route('kertas-kerja.edit', $kk->id);
    }

    public function edit(\App\Models\KertasKerja $kertasKerja)
    {
        $user = auth()->user();
        $isSuperadmin = $user->hasRole('Superadmin');
        
        // Ambil User Role spesifik pada ST ini
        $roleInTeam = trim((string)\App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
            ->where('user_id', $user->id)
            ->value('role_dalam_tim'));

        $isCreator = $kertasKerja->user_id == $user->id;
        $isKetua = $roleInTeam == 'Ketua Tim';

    // Logika Izin Kolaboratif:
    // 1. Superadmin OR Rendal: Selalu Diizinkan (Hanya Lihat untuk Rendal kecuali dispesifikkan lain)
    // 2. Draft/Revisi: Semua Anggota Tim (Yang ditugaskan ke ST ini)
    // 3. Review Ketua: Anggota (Pembuat) DAN Ketua Tim
    // 4. Terkunci (Locked): Read-only untuk Anggota Tim
    
    $canEdit = false;
    $isMemberOfTeam = \App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
        ->where('user_id', $user->id)
        ->exists();

    $isRendal = $user->hasRole('Rendal') || $user->hasRole('Admin Perwakilan');

    if ($isSuperadmin) {
        $canEdit = true;
    } elseif ($isRendal) {
        $canEdit = false; // Rendal bisa melihat tapi tidak bisa mengedit konten secara langsung
    } elseif ($isMemberOfTeam) {
        // LOGIKA IZIN BERTINGKAT (Tampilan Kolaboratif)
        // 1. Posisi Ketua Tim (Draft / Revisi / Review Ketua)
        if ($kertasKerja->status_approval == 'Draft' || 
            str_starts_with($kertasKerja->status_approval, 'Revisi') || 
            $kertasKerja->status_approval == 'Review Ketua') {
            
            if ($isCreator || $isKetua || str_contains(strtolower($roleInTeam), 'anggota')) $canEdit = true;
        }
        // 2. Posisi Dalnis
        elseif ($kertasKerja->status_approval == 'Review Dalnis') {
            $roleInTeam = trim((string)\App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
                ->where('user_id', $user->id)
                ->value('role_dalam_tim'));
            if ($roleInTeam == 'Dalnis') $canEdit = true;
        }
        // 3. Posisi Korwas
        elseif ($kertasKerja->status_approval == 'Review Korwas') {
            $roleInTeam = trim((string)\App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
                ->where('user_id', $user->id)
                ->value('role_dalam_tim'));
            if ($roleInTeam == 'Korwas') $canEdit = true;
        }
    }

    // Jika bukan superadmin, bukan rendal, dan bukan tim, strictly 403
    if (!$isSuperadmin && !$isRendal && !$isMemberOfTeam) {
        abort(403, 'Anda tidak terdaftar dalam tim penugasan ini.');
    }

    $kertasKerja->load('template.indicators.teos', 'answers.details', 'answers.teos.findings');
    
    // Mengatur hierarki indikator
    $indicators = \App\Models\TemplateIndicator::where('template_id', $kertasKerja->template_id)
        ->whereNull('parent_id')
        ->with(['children' => function($q) {
            $q->orderBy('id')->with(['criteria', 'langkahs', 'teos.causes.recommendations', 'children' => function($q2) {
                $q2->orderBy('id')->with(['criteria', 'langkahs', 'teos.causes.recommendations']);
            }]);
        }])
        ->orderBy('id')
        ->get();

    return view('kertas-kerja.form', compact('kertasKerja', 'indicators', 'canEdit'));
    }

    public function update(Request $request, \App\Models\KertasKerja $kertasKerja)
    {
        $user = auth()->user();
        $isSuperadmin = $user->hasRole('Superadmin');
        
        // Ambil User Role spesifik pada ST ini
        $roleInTeam = trim((string)\App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
            ->where('user_id', $user->id)
            ->value('role_dalam_tim'));

        $isCreator = $kertasKerja->user_id == $user->id;
        $isKetua = $roleInTeam == 'Ketua Tim';

        $canEdit = false;
        $canEdit = false;
        if ($isSuperadmin) $canEdit = true;
        else {
             // LOGIKA IZIN BERTINGKAT
             if ($kertasKerja->status_approval == 'Draft' || 
                str_starts_with($kertasKerja->status_approval, 'Revisi') || 
                $kertasKerja->status_approval == 'Review Ketua') {
                if ($isCreator || $isKetua || str_contains(strtolower($roleInTeam), 'anggota')) $canEdit = true;
            }
            elseif ($kertasKerja->status_approval == 'Review Dalnis') {
                $roleInTeam = \App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
                    ->where('user_id', $user->id)
                    ->value('role_dalam_tim');
                if ($roleInTeam == 'Dalnis') $canEdit = true;
            }
            elseif ($kertasKerja->status_approval == 'Review Korwas') {
                $roleInTeam = \App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
                    ->where('user_id', $user->id)
                    ->value('role_dalam_tim');
                if ($roleInTeam == 'Korwas') $canEdit = true;
            }
        }

        if (!$canEdit) {
            abort(403, 'Akses simpan dikunci pada status ini.');
        }

        $data = $request->input('answers', []);
        $files = $request->file('answers', []);
        
        foreach ($data as $indikatorId => $val) {
            $nilai = $val['nilai'] ?? 0;
            $catatan = $val['catatan'] ?? null;
            $evidenceLink = $val['evidence_link'] ?? null;
            $evidenceFile = null;

            // Tangani Upload File
            if ($request->hasFile("answers.$indikatorId.evidence_file")) {
                $file = $request->file("answers.$indikatorId.evidence_file");
                // Simpan di Google Drive folder 'evidence'
                // Pastikan disk 'google' telah dikonfigurasi
                try {
                    $path = $file->store('evidence', 'google');
                    $evidenceFile = $path;
                } catch (\Exception $e) {
                    // Fallback atau log error jika GDrive gagal, mungkin ke local public
                    \Log::error("GDrive Upload Failed: " . $e->getMessage());
                }
            }

            // Tangani Logika Perhitungan Kriteria (Parameter Level 3)
            if (isset($val['criteria']) && is_array($val['criteria'])) {
                $answer = \App\Models\KkAnswer::firstOrCreate(
                    ['kertas_kerja_id' => $kertasKerja->id, 'indikator_id' => $indikatorId],
                    ['nilai' => 0]
                );

                // Update eviden jika ada file baru diupload, jika tidak biarkan yang lama
                $updateData = ['catatan' => $catatan, 'evidence_link' => $evidenceLink];
                if ($evidenceFile) {
                    $updateData['evidence_file'] = $evidenceFile;
                }
                $answer->update($updateData);

                // Sinkronisasi detail Checklist
                // Kita TIDAK lagi menghapus untuk mempertahankan file eviden kecuali kita mengimplementasikan soft delete atau sinkronisasi pintar
                // \App\Models\KkAnswerDetail::where('kk_answer_id', $answer->id)->delete(); 

                $totalScoreSum = 0;
                $maxPossibleScore = 0;
                // $criteriaCount = count($val['criteria']); // Gunakan hitungan DB nanti

                foreach ($val['criteria'] as $criteriaId => $cData) {
                    // Struktur cData diubah menjadi array: ['value' => '...', 'catatan' => '...', 'link' => '...']
                    $choice = $cData['value'] ?? 'none';
                    $catatanCriteria = $cData['catatan'] ?? null;
                    $linkCriteria = $cData['link'] ?? null;

                    // Pilihan: 'full' (1.0), 'partial' (0.5), 'none' (0.0)
                    $score = 0;
                    if ($choice === 'full') $score = 1.0;
                    elseif ($choice === 'partial') $score = 0.5;
                    
                    // Tangani File Eviden per Kriteria
                    $evidenceFileCriteria = null;
                    if ($request->hasFile("answers.$indikatorId.criteria.$criteriaId.evidence")) {
                        $cFile = $request->file("answers.$indikatorId.criteria.$criteriaId.evidence");
                        try {
                            $path = $cFile->store('evidence', 'google');
                            $evidenceFileCriteria = $path;
                        } catch (\Exception $e) {
                            \Log::error("Criteria GDrive Upload Failed: " . $e->getMessage());
                            // Fallback ke Public Disk
                            try {
                                $path = $cFile->store('evidence', 'public');
                                $evidenceFileCriteria = $path;
                                // Opsional: Tampilkan peringatan (hanya sekali untuk menghindari spam)
                                if (!session()->has('warning_upload')) {
                                    session()->flash('warning_upload', 'Gagal upload ke Google Drive. File disimpan di server lokal.');
                                }
                            } catch (\Exception $ex) {
                                \Log::error("Local Upload Failed: " . $ex->getMessage());
                            }
                        }
                    }

                    // Siapkan Data Update
                    $updateArr = [
                        'answer_value' => $choice,
                        'score' => $score,
                        'catatan' => $catatanCriteria,
                        'evidence_link' => $linkCriteria,
                    ];

                    if ($evidenceFileCriteria) {
                        $updateArr['evidence_file'] = $evidenceFileCriteria;
                    }

                    \App\Models\KkAnswerDetail::updateOrCreate(
                        ['kk_answer_id' => $answer->id, 'criteria_id' => $criteriaId],
                        $updateArr
                    );
                    
                    $totalScoreSum += $score;
                }
                
                // Hitung Skor Parameter (0-100)
                // Ambil ulang total jumlah kriteria dari DB agar akurat
                $totalCriteriaDb = \App\Models\TemplateCriteria::where('indicator_id', $indikatorId)->count();
                if ($totalCriteriaDb > 0) {
                     // Normalisasi ke 100
                    $nilai = ($totalScoreSum / $totalCriteriaDb) * 100;
                } else {
                    $nilai = 0;
                }
                
                $answer->update(['nilai' => $nilai]);
                
            } else {
                // Input Standar (Manual / Teks)
                // Cek data yang ada untuk mempertahankan eviden jika tidak mengupload yang baru
                $answer = \App\Models\KkAnswer::firstOrCreate(
                    ['kertas_kerja_id' => $kertasKerja->id, 'indikator_id' => $indikatorId]
                );
                
                $updateData = ['nilai' => $nilai, 'catatan' => $catatan];
                $answer->update($updateData);
            }
        }
        
        // Hitung Ulang Rollup Hierarki
        $this->calculateMrRollup($kertasKerja);

        \App\Models\KertasKerjaAudit::create([
            'kertas_kerja_id' => $kertasKerja->id,
            'user_id' => $user->id,
            'action' => 'Simpan',
            'description' => 'Menyimpan/memperbarui data secara massal.',
        ]);

        return redirect()->route('kertas-kerja.index')
            ->with('success', 'Kertas Kerja berhasil disimpan!');
    }

    public function submit($id)
    {
        $kk = \App\Models\KertasKerja::findOrFail($id);
        $user = auth()->user();

        if ($kk->user_id !== $user->id && !$user->hasRole('Superadmin')) {
        return back()->with('error', 'Hanya pembuat (atau Superadmin) yang bisa mengajukan.');
    }

        if ($kk->status_approval !== 'Draft' && !str_starts_with($kk->status_approval, 'Revisi')) {
            return back()->with('error', 'Kertas kerja sudah diajukan.');
        }

        // Cek Role (dengan konversi string untuk kompatibilitas PHP 8.1)
    $roleInTeam = trim((string)\App\Models\StPersonel::where('st_id', $kk->st_id)
        ->where('user_id', $user->id)
        ->value('role_dalam_tim'));

        if ($roleInTeam == 'Ketua Tim') {
            // Ketua Tim melompati 'Review Ketua' dan langsung ke 'Review Dalnis'
            $kk->update(['status_approval' => 'Review Dalnis']);

            \App\Models\KertasKerjaAudit::create([
                'kertas_kerja_id' => $kk->id,
                'user_id' => $user->id,
                'action' => 'Kirim',
                'description' => 'Kertas kerja dikirim langsung ke Dalnis oleh Ketua Tim.',
            ]);
            
            // Beritahu Dalnis
            $dalnis = \App\Models\StPersonel::where('st_id', $kk->st_id)
                ->where('role_dalam_tim', 'Dalnis')
                ->get();
            foreach ($dalnis as $p) {
                $p->user->notify(new \App\Notifications\KertasKerjaSubmitted($kk, $user));
            }

            return back()->with('success', 'Kertas kerja berhasil dikirim ke Dalnis.');
        } else {
            // Anggota mengajukan ke Ketua
            $kk->update(['status_approval' => 'Review Ketua']);

            \App\Models\KertasKerjaAudit::create([
                'kertas_kerja_id' => $kk->id,
                'user_id' => $user->id,
                'action' => 'Kirim',
                'description' => 'Kertas kerja dikirim ke Ketua Tim.',
            ]);

            // Beritahu Ketua Tim
            $ketua = \App\Models\StPersonel::where('st_id', $kk->st_id)
                ->where('role_dalam_tim', 'Ketua Tim')
                ->get();
            foreach ($ketua as $p) {
                $p->user->notify(new \App\Notifications\KertasKerjaSubmitted($kk, $user));
            }

            return back()->with('success', 'Kertas kerja berhasil dilaporkan ke Ketua Tim.');
        }
    }

    public function approve($id)
    {
        $kk = \App\Models\KertasKerja::findOrFail($id);
        $user = auth()->user();
        $stId = $kk->st_id;

        // Ambil User Role spesifik pada ST ini (dengan trim untuk keamanan)
        $roleInTeam = trim(\App\Models\StPersonel::where('st_id', $stId)
            ->where('user_id', $user->id)
            ->value('role_dalam_tim'));

        // Logika Validasi
        if ($kk->status_approval == 'Review Ketua') {
            if ($roleInTeam !== 'Ketua Tim' && !$user->hasRole('Superadmin')) { 
                 return back()->with('error', 'Anda bukan Ketua Tim untuk penugasan ini.');
            }
            $kk->update(['status_approval' => 'Review Dalnis']);

            // Catat Catatan Persetujuan
            \App\Models\ReviewNote::create([
                'kk_id' => $kk->id,
                'reviewer_id' => $user->id,
                'catatan' => 'Kertas kerja disetujui oleh Ketua Tim.',
                'status' => 'Approved',
            ]);

            \App\Models\KertasKerjaAudit::create([
                'kertas_kerja_id' => $kk->id,
                'user_id' => $user->id,
                'action' => 'Setuju',
                'description' => 'Disetujui Ketua Tim. Lanjut ke Dalnis.',
            ]);

            // Notify Dalnis
            $dalnis = \App\Models\StPersonel::where('st_id', $kk->st_id)
                ->where('role_dalam_tim', 'Dalnis')
                ->get();
            foreach ($dalnis as $p) {
                $p->user->notify(new \App\Notifications\KertasKerjaSubmitted($kk, $user));
            }

            return back()->with('success', 'Disetujui. Lanjut ke Dalnis.');
        }

        if ($kk->status_approval == 'Review Dalnis') {
            if ($roleInTeam !== 'Dalnis' && !$user->hasRole('Superadmin')) {
                 return back()->with('error', 'Anda bukan Dalnis untuk penugasan ini.');
            }
            $kk->update(['status_approval' => 'Review Korwas']);

            // Record Approval Note
            \App\Models\ReviewNote::create([
                'kk_id' => $kk->id,
                'reviewer_id' => $user->id,
                'catatan' => 'Kertas kerja disetujui oleh Dalnis.',
                'status' => 'Approved',
            ]);

            \App\Models\KertasKerjaAudit::create([
                'kertas_kerja_id' => $kk->id,
                'user_id' => $user->id,
                'action' => 'Setuju',
                'description' => 'Disetujui Dalnis. Lanjut ke Korwas.',
            ]);

            // Notify Korwas
            $korwas = \App\Models\StPersonel::where('st_id', $kk->st_id)
                ->where('role_dalam_tim', 'Korwas')
                ->get();
            foreach ($korwas as $p) {
                $p->user->notify(new \App\Notifications\KertasKerjaSubmitted($kk, $user));
            }

            return back()->with('success', 'Disetujui. Lanjut ke Korwas.');
        }

        if ($kk->status_approval == 'Review Korwas') {
            if ($roleInTeam !== 'Korwas' && !$user->hasRole('Superadmin')) {
                 return back()->with('error', 'Anda bukan Korwas untuk penugasan ini.');
            }
            $kk->update(['status_approval' => 'Final']);

            // Record Approval Note
            \App\Models\ReviewNote::create([
                'kk_id' => $kk->id,
                'reviewer_id' => $user->id,
                'catatan' => 'Kertas kerja disetujui oleh Korwas. Status FINAL.',
                'status' => 'Approved',
            ]);

            \App\Models\KertasKerjaAudit::create([
                'kertas_kerja_id' => $kk->id,
                'user_id' => $user->id,
                'action' => 'Setuju',
                'description' => 'Disetujui Korwas. Status dokumen menjadi Final.',
            ]);

            // Sync with PKA Langkah for this ST
            $pkaList = \App\Models\ProgramKerja::where('st_id', $kk->st_id)->get();
            foreach ($pkaList as $pka) {
                $langkahList = \App\Models\PkLangkah::where('program_kerja_id', $pka->id)->get();
                foreach ($langkahList as $langkah) {
                    if ($langkah->status !== 'completed') {
                        $langkah->update([
                            'status' => 'completed',
                            'tgl_selesai' => now(),
                            'catatan_hasil' => 'Kertas Kerja telah disetujui (Final).'
                        ]);
                        
                        // Update assignments status
                        \App\Models\PkAssignment::where('pk_langkah_id', $langkah->id)
                            ->update(['status' => 'completed']);
                    }
                }
                
                // Auto-complete PKA
                $allCompleted = $pka->langkah()->whereNotIn('status', ['completed', 'skipped'])->count() === 0;
                if ($allCompleted && $pka->langkah()->count() > 0) {
                    $pka->update(['status' => 'completed']);
                }
            }

            return back()->with('success', 'Disetujui. Kertas Kerja Final.');

        }

        return back()->with('error', 'Status tidak valid untuk persetujuan.');
    }

    public function reject(Request $request, $id)
    {
        $kk = \App\Models\KertasKerja::findOrFail($id);
        $user = auth()->user();
        $stId = $kk->st_id;
        $reason = $request->input('reason', 'Perlu perbaikan.');

        // Get User Role in this specific ST (with string cast for safety)
        $roleInTeam = trim((string)\App\Models\StPersonel::where('st_id', $stId)
            ->where('user_id', $user->id)
            ->value('role_dalam_tim'));

        $isSuperadmin = $user->hasRole('Superadmin');

        $newStatus = null;
        $notifyRoles = [];
        $notifySpecificUser = null;

        if ($kk->status_approval == 'Review Ketua') {
             if ($roleInTeam !== 'Ketua Tim' && !$isSuperadmin) {
                 return back()->with('error', 'Anda bukan Ketua Tim untuk penugasan ini.');
             }
              $newStatus = 'Draft';
              $notifyRoles = ['Anggota'];
              $notifySpecificUser = $kk->user_id; // Original creator
         } elseif ($kk->status_approval == 'Review Dalnis') {
              if ($roleInTeam !== 'Dalnis' && !$isSuperadmin) {
                  return back()->with('error', 'Anda bukan Dalnis untuk penugasan ini.');
              }
              // Dalnis rejects -> Back to Posisi Ketua
              $newStatus = 'Review Ketua'; 
              $notifyRoles = ['Ketua Tim'];
         } elseif ($kk->status_approval == 'Review Korwas') {
              if ($roleInTeam !== 'Korwas' && !$isSuperadmin) {
                  return back()->with('error', 'Anda bukan Korwas untuk penugasan ini.');
              }
              // Korwas rejects -> Back to Posisi Ketua (Directly)
              $newStatus = 'Review Ketua';
              $notifyRoles = ['Ketua Tim', 'Dalnis']; // Notify both
         } else {
            return back()->with('error', 'Status dokumen tidak valid untuk dikembalikan.');
        }

        if ($newStatus) {
            $kk->update(['status_approval' => $newStatus]);
            
            // Save Review Note if reason provided
            \App\Models\ReviewNote::create([
                'kk_id' => $kk->id,
                'reviewer_id' => $user->id,
                'catatan' => $reason,
                'status' => 'Pending',
            ]);

            \App\Models\KertasKerjaAudit::create([
                'kertas_kerja_id' => $kk->id,
                'user_id' => $user->id,
                'action' => 'Tolak',
                'description' => "Dokumen dikembalikan statusnya ke $newStatus. Catatan: $reason",
            ]);

            // Notify recipients
            $recipients = \App\Models\StPersonel::where('st_id', $kk->st_id)
                ->whereIn('role_dalam_tim', $notifyRoles)
                ->get();

            foreach ($recipients as $p) {
                $p->user->notify(new \App\Notifications\KertasKerjaReturned($kk, $user, $reason));
            }

            // Also notify original creator if back to Draft and not already in recipients
            if ($newStatus == 'Draft' && $kk->user_id) {
                $creator = \App\Models\User::find($kk->user_id);
                if ($creator && !$recipients->contains('user_id', $kk->user_id)) {
                    $creator->notify(new \App\Notifications\KertasKerjaReturned($kk, $user, $reason));
                }
            }
        }

        return back()->with('warning', 'Kertas kerja dikembalikan.');
    }

    private function calculateMrRollup(\App\Models\KertasKerja $kk)
    {
        // Get Template Hierarchy
        $template = $kk->template;
        if (!$template) return;

        // Level 1: Aspects
        $aspects = \App\Models\TemplateIndicator::where('template_id', $template->id)
            ->whereNull('parent_id')
            ->get();

        $aspectScores = [];
        $aspectWeights = [];

        foreach ($aspects as $aspect) {
            // Level 2: Indikator
            $indicators = $aspect->children;
            $indScores = [];
            $indWeights = [];

            foreach ($indicators as $ind) {
                // Level 3: Parameter
                $params = $ind->children;
                $paramScores = [];
                $paramWeights = [];

                foreach ($params as $param) {
                    // Ambil Skor dari DB
                    $ans = \App\Models\KkAnswer::where('kertas_kerja_id', $kk->id)
                        ->where('indikator_id', $param->id)
                        ->first();
                    
                    $score = $ans ? $ans->nilai : 0;
                    $weight = $param->bobot ?? 0;

                    $paramScores[] = $score * $weight;
                    $paramWeights[] = $weight;
                }

                // Hitung Skor Indikator
                $totalParamWeight = array_sum($paramWeights);
                $indScore = $totalParamWeight > 0 ? (array_sum($paramScores) / $totalParamWeight) : 0; // Rata-rata Tertimbang
                // Jika bobot berupa persentase (misal 30), tidak masalah. 
                // Jika sudah 0-100, mengecek jumlahnya sudah cukup.

                // Simpan Skor Indikator (Virtual Answer)
                \App\Models\KkAnswer::updateOrCreate(
                    ['kertas_kerja_id' => $kk->id, 'indikator_id' => $ind->id],
                    ['nilai' => $indScore, 'catatan' => 'Auto-calculated ROLLUP']
                );

                $indScores[] = $indScore * ($ind->bobot ?? 0);
                $indWeights[] = ($ind->bobot ?? 0);
            }

            // Hitung Skor Aspek
            $totalIndWeight = array_sum($indWeights);
            $aspectScore = $totalIndWeight > 0 ? (array_sum($indScores) / $totalIndWeight) : 0; // Jika bobot bersifat relatif (misal 40, 60)
            
            // CATATAN: Jika bobot adalah persentase mutlak dari induknya (misal 40% dari Total), kita mungkin butuh logika berbeda.
            // Asumsikan bobot pada tiap level berjumlah 100 (atau rasio relatif). 
            // contoh: Aspek 1 (40%), Aspek 2 (60%).
            // Di dalam Aspek 1: Ind A (50), Ind B (50).
            
            // Simpan Skor Aspek
            \App\Models\KkAnswer::updateOrCreate(
                    ['kertas_kerja_id' => $kk->id, 'indikator_id' => $aspect->id],
                    ['nilai' => $aspectScore, 'catatan' => 'Auto-calculated ROLLUP']
            );

            $aspectScores[] = $aspectScore * ($aspect->bobot ?? 0);
            $aspectWeights[] = ($aspect->bobot ?? 0);
        }

        // Skor Akhir
        $totalAspectWeight = array_sum($aspectWeights);
        $finalScore = $totalAspectWeight > 0 ? (array_sum($aspectScores) / $totalAspectWeight) : 0; 
        
        // Jika bobotnya seperti 40, 30, 30 (jumlah 100), membaginya dengan 100 adalah benar.
        
        $kk->update(['nilai_akhir' => $finalScore]);
    }

    /**
     * Hitung skor berbasis level untuk sebuah parameter.
     * Mengarahkan ke metode building_block atau criteria_fulfillment.
     */
    private function calculateLevelScore(\App\Models\KertasKerja $kk, $indikatorId)
    {
        $metode = $kk->template->metode_penilaian ?? 'tally';

        // Ambil kriteria dikelompokkan berdasarkan level
        $criteriaByLevel = \App\Models\TemplateCriteria::where('indicator_id', $indikatorId)
            ->orderBy('level')
            ->get()
            ->groupBy('level');

        if ($criteriaByLevel->isEmpty()) return 0;

        // Ambil jawaban beserta detail terbaru
        $answer = \App\Models\KkAnswer::where('kertas_kerja_id', $kk->id)
            ->where('indikator_id', $indikatorId)
            ->with('details')
            ->first();

        if (!$answer) return 0;

        if ($metode === 'building_block') {
            return $this->scoreBuildingBlock($answer, $criteriaByLevel);
        } else {
            return $this->scoreCriteriaFulfillment($answer, $criteriaByLevel);
        }
    }

    /**
     * Building Block: Evaluasi level secara sekuensial.
     * Level N+1 hanya dievaluasi jika SEMUA kriteria pada Level N berstatus 'full' (Ya).
     * Tidak ada pilihan 'Sebagian' — hanya Ya/Tidak.
     * Skor = levelTercapai + pecahan dari level berikutnya (misal, 3.21)
     */
    private function scoreBuildingBlock($answer, $criteriaByLevel)
    {
        $achievedLevel = 0;

        foreach ($criteriaByLevel as $level => $criteria) {
            $totalCriteria = $criteria->count();
            $fulfilledCount = 0;

            foreach ($criteria as $c) {
                $detail = $answer->details->where('criteria_id', $c->id)->first();
                if ($detail && $detail->answer_value === 'full') {
                    $fulfilledCount++;
                }
            }

            if ($fulfilledCount === $totalCriteria) {
                // Semua kriteria terpenuhi → level tercapai
                $achievedLevel = $level;
            } else {
                // Parsial → hitung pecahan dan BERHENTI
                $fraction = $totalCriteria > 0
                    ? ($fulfilledCount / $totalCriteria)
                    : 0;
                return round($achievedLevel + $fraction, 2);
            }
        }

        return round((float)$achievedLevel, 2); // Semua level tercapai
    }

    /**
     * Criteria Fulfillment: Evaluasi independen untuk semua level.
     * Ya=1.0, Sebagian=0.5, Tidak=0.
     * Tiap level menyumbang hingga 1.0 ke skor akhir.
     * Skor = jumlah dari (pemenuhan_level) di seluruh level (maksimal 5.00)
     */
    private function scoreCriteriaFulfillment($answer, $criteriaByLevel)
    {
        $totalScore = 0;

        foreach ($criteriaByLevel as $level => $criteria) {
            $totalCriteria = $criteria->count();
            $scoreSum = 0;

            foreach ($criteria as $c) {
                $detail = $answer->details->where('criteria_id', $c->id)->first();
                if ($detail) {
                    if ($detail->answer_value === 'full') $scoreSum += 1.0;
                    elseif ($detail->answer_value === 'partial') $scoreSum += 0.5;
                }
            }

            $levelContribution = $totalCriteria > 0
                ? ($scoreSum / $totalCriteria)
                : 0;
            $totalScore += $levelContribution;
        }

        return round($totalScore, 2);
    }

    public function exportExcel($id)
    {
        $kk = \App\Models\KertasKerja::with(['answers.indicator.parent.parent', 'suratTugas'])->findOrFail($id);
        $indicators = \App\Models\TemplateIndicator::with(['children.children'])
            ->where('template_id', $kk->suratTugas->template_id)
            ->whereNull('parent_id')
            ->orderBy('id')
            ->get();

        \App\Models\KertasKerjaAudit::create([
            'kertas_kerja_id' => $kk->id,
            'user_id' => auth()->id(),
            'action' => 'Export',
            'description' => 'Mengekspor Kertas Kerja ke Excel.',
        ]);

        $fileName = 'Kertas_Kerja_' . str_replace([' ', '/'], '_', $kk->judul_kk) . '.xls';

        $html = view('kertas-kerja.export-excel', compact('kk', 'indicators'))
                    ->with('kertasKerja', $kk)
                    ->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function importExcel(Request $request, $id)
    {
        $request->validate([
            'file_excel' => 'required'
        ]);

        $kk = \App\Models\KertasKerja::findOrFail($id);
        $file = $request->file('file_excel');

        \DB::beginTransaction();
        try {
            // Baca isi file
            $contents = file_get_contents($file->getRealPath());

            // Coba parsing sebagai tabel HTML (dari format ekspor kita)
            $dom = new \DOMDocument('1.0', 'UTF-8');
            @$dom->loadHTML('<?xml encoding="UTF-8">' . $contents);
            $rows = $dom->getElementsByTagName('tr');

            $updated = 0;
            $headerSkipped = false;

            foreach ($rows as $row) {
                $cells = $row->getElementsByTagName('td');
                if ($cells->length < 2) continue;

                // Lewati baris header
                $firstCell = trim($cells->item(0)->textContent);
                if (!$headerSkipped) {
                    // Cek apakah ini baris header (kolom ID)
                    if (strtoupper($firstCell) === 'ID' || !is_numeric($firstCell)) {
                        $headerSkipped = true;
                        continue;
                    }
                    $headerSkipped = true;
                }

                $indicatorId   = trim($cells->item(0)->textContent ?? '');
                $indicatorName = $cells->length > 1 ? trim($cells->item(1)->textContent ?? '') : '';
                $nilai         = $cells->length > 2 ? trim($cells->item(2)->textContent ?? '') : null;
                $catatan       = $cells->length > 3 ? trim($cells->item(3)->textContent ?? '') : null;
                $link          = $cells->length > 4 ? trim($cells->item(4)->textContent ?? '') : null;

                // Lewati baris non-parameter (baris yang mengandung "ASPEK:" atau "INDIKATOR:")
                if (str_contains($indicatorName, 'ASPEK:') || str_contains($indicatorName, 'INDIKATOR:')) {
                    continue;
                }

                if (!$indicatorId || !is_numeric($indicatorId)) continue;

                $indicator = \App\Models\TemplateIndicator::find((int)$indicatorId);
                if (!$indicator || $indicator->children()->count() > 0) continue;

                // Hanya perbarui jika nilai diberikan
                if ($nilai !== null && $nilai !== '') {
                    \App\Models\KkAnswer::updateOrCreate(
                        ['kertas_kerja_id' => $id, 'indikator_id' => (int)$indicatorId],
                        [
                            'nilai' => (float)$nilai,
                            'catatan' => $catatan ?: null,
                            'evidence_link' => $link ?: null,
                        ]
                    );
                    $updated++;
                }
            }

            // Hitung ulang rollup
            $this->calculateMrRollup($kk);

            \App\Models\KertasKerjaAudit::create([
                'kertas_kerja_id' => $kk->id,
                'user_id' => auth()->id(),
                'action' => 'Import',
                'description' => "Mengimpor data Kertas Kerja dari Excel. {$updated} parameter diperbarui.",
            ]);

            \DB::commit();
            return back()->with('success', "Berhasil mengimpor {$updated} parameter dari Excel!");
        } catch (\Exception $e) {
            \DB::rollBack();
            return back()->with('error', 'Gagal mengimpor data: ' . $e->getMessage());
        }
    }

    public function fetchReference(Request $request)
    {
        $request->validate([
            'ref_jenis_id' => 'required',
            'tahun' => 'required'
        ]);

        $st = \App\Models\SuratTugas::where('jenis_penugasan_id', $request->ref_jenis_id)
            ->where('tahun_evaluasi', $request->tahun)
            ->where('perwakilan_id', auth()->user()->perwakilan_id)
            ->whereHas('kertasKerja', function($q) {
                $q->whereNotNull('nilai_akhir');
            })
            ->latest()
            ->first();

        if (!$st) {
            return response()->json([
                'success' => false, 
                'message' => 'Tidak ditemukan Kertas Kerja dengan nilai akhir untuk jenis penugasan ini pada tahun ' . $request->tahun
            ]);
        }

        $kk = $st->kertasKerja()->whereNotNull('nilai_akhir')->latest()->first();

        return response()->json([
            'success' => true, 
            'nilai' => $kk->nilai_akhir,
            'source' => $st->nomor_st
        ]);
    }

    public function updateSingle(Request $request)
    {
        // Validasi
        $request->validate([
            'kk_id' => 'required|exists:kertas_kerja,id',
            'indicator_id' => 'required|exists:template_indicators,id',
            'criteria_id' => 'required|exists:template_criteria,id',
            'value' => 'required|in:full,partial,none', // building_block hanya mengirimkan full/none
        ]);

        $kk = \App\Models\KertasKerja::findOrFail($request->kk_id);
        
        // Otorisasi (Sama dengan Header Edit/Update)
        $user = auth()->user();
        $roleInTeam = \App\Models\StPersonel::where('st_id', $kk->st_id)
            ->where('user_id', $user->id) 
            ->value('role_dalam_tim');

        $isCreator = $kk->user_id == $user->id;
        $isKetua = $roleInTeam == 'Ketua Tim';
        $isSuperadmin = $user->hasRole('Superadmin');
        $isRendal = $user->hasRole('Rendal') || $user->hasRole('Admin Perwakilan');

        $canEdit = false;
        $isMemberOfTeam = \App\Models\StPersonel::where('st_id', $kk->st_id)
            ->where('user_id', $user->id)
            ->exists();

        if ($isSuperadmin) {
            $canEdit = true;
        } elseif ($isRendal) {
            $canEdit = false;
        } elseif ($isMemberOfTeam) {
            // LOGIKA IZIN BERTINGKAT
            // 1. Posisi Ketua Tim (Draft / Revisi / Review Ketua)
            //    - Anggota (Pembuat) DAN Ketua Tim bisa mengedit.
            if ($kk->status_approval == 'Draft' || 
                str_starts_with($kk->status_approval, 'Revisi') || 
                $kk->status_approval == 'Review Ketua') {
                
                if ($isCreator || $isKetua || str_contains(strtolower($roleInTeam), 'anggota')) $canEdit = true;
            }
            // 2. Posisi Dalnis (Review Dalnis)
            //    - Hanya Dalnis yang bisa mengedit.
            elseif ($kk->status_approval == 'Review Dalnis') {
                $roleInTeam = \App\Models\StPersonel::where('st_id', $kk->st_id)
                    ->where('user_id', $user->id) 
                    ->value('role_dalam_tim');
                
                if ($roleInTeam == 'Dalnis') $canEdit = true;
            }
            // 3. Posisi Korwas (Review Korwas)
            //    - Hanya Korwas yang bisa mengedit.
            elseif ($kk->status_approval == 'Review Korwas') {
                $roleInTeam = \App\Models\StPersonel::where('st_id', $kk->st_id)
                    ->where('user_id', $user->id) 
                    ->value('role_dalam_tim');

                if ($roleInTeam == 'Korwas') $canEdit = true;
            }
        }

        if (!$canEdit) {
             return response()->json(['success' => false, 'message' => 'Status dokumen ini (' . $kk->status_approval . ') terkunci untuk Anda.'], 403);
        }
        // Logika
        $indikatorId = $request->indicator_id;
        $criteriaId = $request->criteria_id;
        $val = $request->value;
        $catatan = $request->catatan;
        $link = $request->link;
        
        // 1. Cari/Buat Parent Answer
        $answer = \App\Models\KkAnswer::firstOrCreate(
            ['kertas_kerja_id' => $kk->id, 'indikator_id' => $indikatorId],
            ['nilai' => 0]
        );

        // 2. Logika Skor
        $score = 0;
        if ($val === 'full') $score = 1.0;
        elseif ($val === 'partial') $score = 0.5;

        // 3. File Upload (dengan Fallback)
        $evidenceFileCriteria = null;
        if ($request->hasFile('evidence')) {
            $cFile = $request->file('evidence');
            try {
                $path = $cFile->store('evidence', 'google');
                $evidenceFileCriteria = $path;
            } catch (\Exception $e) {
                \Log::error("Criteria GDrive Upload Failed (AJAX): " . $e->getMessage());
                try {
                    $path = $cFile->store('evidence', 'public');
                    $evidenceFileCriteria = $path;
                } catch (\Exception $ex) {
                    \Log::error("Local Upload Failed (AJAX): " . $ex->getMessage());
                    return response()->json(['success' => false, 'message' => 'Gagal upload file.'], 500);
                }
            }
        }

        // 4. Update Detail
        $updateArr = [
            'answer_value' => $val,
            'score' => $score,
            'catatan' => $catatan,
            'evidence_link' => $link,
        ];
        if ($evidenceFileCriteria) {
            $updateArr['evidence_file'] = $evidenceFileCriteria;
        }

        \App\Models\KkAnswerDetail::updateOrCreate(
            ['kk_answer_id' => $answer->id, 'criteria_id' => $criteriaId],
            $updateArr
        );

        // 5. Hitung ulang Skor Parameter (bercabang berdasarkan metode penilaian)
        $metode = $kk->template->metode_penilaian ?? 'tally';
        $newParamScore = 0;

        if ($metode === 'tally') {
            // Klasik: metode persentase
            $totalCriteriaDb = \App\Models\TemplateCriteria::where('indicator_id', $indikatorId)->count();
            $currentScoreSum = $answer->details()->sum('score');
            if ($totalCriteriaDb > 0) {
                $newParamScore = ($currentScoreSum / $totalCriteriaDb) * 100;
            }
        } else {
            // Penilaian berbasis level (building_block atau criteria_fulfillment)
            $newParamScore = $this->calculateLevelScore($kk, $indikatorId);
        }

        $answer->update(['nilai' => $newParamScore]);

        // 6. Hitung ulang Rollup
        $this->calculateMrRollup($kk);

        // 7. Kumpulkan skor rollup untuk pembaruan UI secara langsung
        $kk->refresh();
        $rollupScores = \App\Models\KkAnswer::where('kertas_kerja_id', $kk->id)
            ->where('catatan', 'Auto-calculated ROLLUP')
            ->pluck('nilai', 'indikator_id');

        // Format tampilan skor berdasarkan metode
        $paramScoreFormatted = $metode === 'tally'
            ? number_format($newParamScore, 0)
            : number_format($newParamScore, 2);
        
        $finalScore = $kk->nilai_akhir; // Berasumsi nilai_akhir diperbarui oleh calculateMrRollup

        $kk->update([
            'nilai_akhir' => $finalScore
        ]);

        \App\Models\KertasKerjaAudit::create([
            'kertas_kerja_id' => $kk->id,
            'user_id' => $user->id,
            'action' => 'Simpan',
            'description' => 'Mengubah satu item penilaian kriteria/indikator secara spesifik.',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Data tersimpan.',
            'param_score' => $paramScoreFormatted,
            'final_score' => number_format($kk->nilai_akhir ?? 0, 2),
            'rollup_scores' => $rollupScores,
            'metode' => $metode,
            'evidence_file' => $evidenceFileCriteria, 
            'is_local' => $evidenceFileCriteria ? \Storage::disk('public')->exists($evidenceFileCriteria) : false
        ]);
    }

    public function reviewSheet($id)
    {
        $kk = \App\Models\KertasKerja::with(['suratTugas', 'reviewNotes.reviewer'])->findOrFail($id);
        $user = auth()->user();

        // Cek Otorisasi
        $isSuperadmin = $user->hasRole('Superadmin');
        $isRendal = $user->hasRole('Rendal') || $user->hasRole('Admin Perwakilan');
        $isMemberOfTeam = \App\Models\StPersonel::where('st_id', $kk->st_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isSuperadmin && !$isRendal && !$isMemberOfTeam) {
            abort(403, 'Anda tidak memiliki akses ke lembar review ini.');
        }
        
        return view('kertas-kerja.review-sheet', compact('kk'));
    }

    // Mode QA untuk Rendal & Respon Tim
    public function qa($id)
    {
        $kertasKerja = \App\Models\KertasKerja::findOrFail($id);
        $user = auth()->user();

        // Cek Akses: Rendal ATAU Anggota Tim
        $isRendal = $user->hasRole('Rendal') || $user->hasRole('Admin Perwakilan') || $user->hasRole('Superadmin');
        
        $isMemberOfTeam = \App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isRendal && !$isMemberOfTeam) {
            abort(403, 'Anda tidak memiliki akses ke halaman QA ini.');
        }

        // Gunakan ulang logika/view edit tetapi dengan meneruskan flag 'isQaMode'
        $kertasKerja->load('template.indicators', 'answers.details');

        $indicators = \App\Models\TemplateIndicator::where('template_id', $kertasKerja->template_id)
            ->whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('id')->with(['criteria', 'children' => function($q2) {
                    $q2->orderBy('id')->with('criteria');
                }]);
            }])
            ->orderBy('id')
            ->get();

        $canEdit = false; // Edit biasa dinonaktifkan
        $isQaMode = true; // Aktifkan Tampilan QA

        $canEdit = false; // Edit biasa dinonaktifkan
        $isQaMode = true; // Aktifkan Tampilan QA
        $isQaFinal = $kertasKerja->status_qa == 'Final';

        // Tentukan hak akses dalam Mode QA
        // Rendal: Bisa Edit QA Score/Note JIKA BELUM FINAL.
        // Tim: Bisa Edit Respon JIKA BELUM FINAL.
        $canEditQa = $isRendal && !$isQaFinal; 
        $canEditResponse = $isMemberOfTeam && !$isQaFinal; 

            return view('kertas-kerja.form', compact('kertasKerja', 'indicators', 'canEdit', 'isQaMode', 'canEditQa', 'canEditResponse', 'isQaFinal'));
    }

    public function finalizeQa(Request $request, $id)
    {
        \Log::info("Finalize QA Triggered for ID: $id by User: " . auth()->id());
        $kk = \App\Models\KertasKerja::findOrFail($id);
        $user = auth()->user();

        // Hanya Rendal/Superadmin yang bisa finalisasi
        if (!$user->hasRole('Rendal') && !$user->hasRole('Admin Perwakilan') && !$user->hasRole('Superadmin')) {
            \Log::warning("Unauthorized Finalize Attempt by User: " . auth()->id() . " Role: " . ($user->role ? $user->role->name : 'None'));
            abort(403, 'Hanya Rendal yang dapat memfinalisasi QA.');
        }

        \Log::info("Authorized Finalize access. User Role: " . ($user->role ? $user->role->name : 'None'));

        $updated = $kk->update(['status_qa' => 'Final']);
        \Log::info("QA Finalized Result: " . ($updated ? 'Success' : 'Failed') . ". New Status: " . $kk->fresh()->status_qa);

            return redirect()->route('laporan.index')->with('success', 'Status QA berhasil difinalisasi.');
    }

    public function unfinalizeQa(Request $request, $id)
    {
        $kk = \App\Models\KertasKerja::findOrFail($id);
        $user = auth()->user();

        // Hanya Rendal/Superadmin yang bisa membatalkan finalisasi
        if (!$user->hasRole('Rendal') && !$user->hasRole('Superadmin')) {
             abort(403, 'Hanya Rendal/Superadmin yang dapat membatalkan finalisasi QA.');
        }

        $kk->update(['status_qa' => 'Draft']);
        \Log::info("QA Unfinalized by User: " . auth()->id() . " for KK ID: " . $id);

        return redirect()->route('laporan.index')->with('success', 'Status Final QA dibatalkan. Kembali ke Draft.');
    }

    public function unfinalizeApproval(Request $request, $id)
    {
        $kk = \App\Models\KertasKerja::findOrFail($id);
        $user = auth()->user();

        // Hanya Superadmin, Korwas, Dalnis yang bisa membatalkan finalisasi status (approval)
        if (!$user->hasRole('Superadmin') && !$user->hasRole('Korwas') && !$user->hasRole('Dalnis')) {
             abort(403, 'Akses ditolak. Hanya Korwas/Dalnis yang dapat membatalkan status final.');
        }

        // Atur status ke 'Review Korwas' (Kembali ke Posisi Korwas)
        // BUKAN Revisi, karena Korwas ingin mengeditnya atau mengembalikannya ke Dalnis/Ketua Tim secara manual.
        $kk->update([
            'status_approval' => 'Review Korwas',
            'status_qa' => 'Draft' // Reset QA juga jika sebelumnya Final
        ]);
        
        \Log::info("Approval Unfinalized (Revisi) by User: " . auth()->id() . " for KK ID: " . $id);

        return redirect()->route('kertas-kerja.index')->with('success', 'Status Final dibatalkan. Dokumen dikembalikan ke Posisi Korwas.');
    }

    public function updateTanggapanQa(Request $request)
    {
        $kkId = $request->input('kk_id');
        $user = auth()->user();
        
        // Cegah update jika Final
        $kk = \App\Models\KertasKerja::findOrFail($kkId);
        if ($kk->status_qa == 'Final') {
            return response()->json(['success' => false, 'message' => 'QA sudah final. Tidak dapat diubah.'], 403);
        }

        // Otorisasi: Rendal/Superadmin ATAU Anggota Tim
        $isRendal = $user->hasRole('Rendal') || $user->hasRole('Superadmin') || $user->hasRole('Admin Perwakilan');
        $isMemberOfTeam = \App\Models\StPersonel::where('st_id', $kk->st_id)->where('user_id', $user->id)->exists();

        if (!$isRendal && !$isMemberOfTeam) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $criteriaId = $request->input('criteria_id');
        $tanggapan = $request->input('tanggapan_qa');

        // Temukan jawaban/detail yang ada (seharusnya ada jika QA dikerjakan, atau minimal strukturnya ada)
        $criteria = \App\Models\TemplateCriteria::findOrFail($criteriaId);
        $answer = \App\Models\KkAnswer::firstOrCreate(
            ['kertas_kerja_id' => $kkId, 'indikator_id' => $criteria->indicator_id]
        );

        $detail = \App\Models\KkAnswerDetail::firstOrCreate(
            ['kk_answer_id' => $answer->id, 'criteria_id' => $criteriaId]
        );

        $detail->tanggapan_qa = $tanggapan;
        $detail->save();

        return response()->json(['success' => true, 'message' => 'Tanggapan tersimpan.']);
    }



    public function storeQa(Request $request, $id)
    {
        $kk = \App\Models\KertasKerja::findOrFail($id);
        
        // Lakukan pengulangan melalui input-input
        // Format yang diharapkan: name="qa[criteria_id][score_qa]" dan "qa[criteria_id][catatan_qa]"
        // Namun sepertinya lebih sederhana memproses setiap request AJAX secara individual atau secara bulk (massal).
        // Mari kita asumsikan simpan massal (bulk) untuk saat ini, atau kita gunakan pola updateSingle.
        
        // Sebenarnya, sebaiknya kita implementasikan metode update individual untuk QA yang mirip dengan updateSingle
        // Namun untuk kejelasannya, mari kita cek dulu isi request.
    }

    public function updateQaSingle(Request $request) 
    {
        $kkId = $request->input('kk_id');
        $criteriaId = $request->input('criteria_id');
        
        $kk = \App\Models\KertasKerja::findOrFail($kkId);
        
        // Pastikan Parent Answer ada (seharusnya begitu, karena hanya KK Final yang di-QA)
        // Namun untuk berjaga-jaga:
        $criteria = \App\Models\TemplateCriteria::findOrFail($criteriaId);
        $answer = \App\Models\KkAnswer::firstOrCreate(
            ['kertas_kerja_id' => $kkId, 'indikator_id' => $criteria->indicator_id],
            ['nilai' => 0]
        );

        $detail = \App\Models\KkAnswerDetail::where('kk_answer_id', $answer->id)
            ->where('criteria_id', $criteriaId)
            ->first();

        if (!$detail) {
            // Seharusnya membuat detail jika tidak ada (meski kasus ini jarang terjadi)
             $detail = \App\Models\KkAnswerDetail::create([
                'kk_answer_id' => $answer->id,
                'criteria_id' => $criteriaId,
                'answer_value' => 'none',
                'score' => 0
            ]);
        }

        // Cek Logika: Apakah ini pembaruan Skor QA (Radio) atau pembaruan Catatan QA?
        if ($request->has('qa_value')) {
            $qaValue = $request->input('qa_value');
            
            // Hitung Score QA berdasarkan Opsi Terpilih
            $scoreQa = 0;
            if ($qaValue === 'full') $scoreQa = 100;
            elseif ($qaValue === 'partial') $scoreQa = 50;
            
            $detail->qa_value = $qaValue;
            $detail->score_qa = $scoreQa;
        }

        if ($request->has('catatan_qa')) {
            $detail->catatan_qa = $request->input('catatan_qa');
        }

        $detail->save();

        // Hitung ulang Skor Parameter (QA)
        // Logikanya sama dengan skor normal tetapi menggunakan score_qa
        // 1. Ambil semua rincian detail untuk jawaban ini
        $details = $answer->details;
        $totalCriteria = \App\Models\TemplateCriteria::where('indicator_id', $criteria->indicator_id)->count();
        
        // Kita perlu menangani nilai score_qa jika null. Apakah harus fallback ke skor asli (Tim)? 
        // Atau dianggap 0? Atau apakah user DIHARUSKAN mengisi semua?
        // Asumsi: Jika proses QA baru dimulai, QA ini mungkin dilakukan bertahap (parsial). 
        // Apakah kita jumlahkan score_qa jika tidak null, jika null gunakan skor asli?
        // TIDAK. Lebih baik kita memisahkan logika perhitungannya secara independen.
        // Terlalu banyak pengecekan panjang jika nilai QA dihitung sebagai campuran.
        // Namun user mungkin menduga jika tidak disentuh nilainya tetap penuh.
        
        // Daripada membingungkan score QA dan form, fallback langsung ke Team Score:
        $sumScoreQa = 0;
        foreach ($details as $d) {    
            if ($d->score_qa !== null) {
                $sumScoreQa += $d->score_qa;
            } else {
                $sumScoreQa += ($d->score * 100);
            }
        }

        $newParamScoreQa = 0;
        if ($totalCriteria > 0) {
            $newParamScoreQa = $sumScoreQa / $totalCriteria;
        }

        $answer->update(['nilai_qa' => $newParamScoreQa]);

        // Rollup
        $this->calculateQaRollup($kk);

        return response()->json([
            'success' => true, 
            'message' => 'QA tersimpan.',
            'qa_score' => $detail->score_qa, // Kirim balik skor ini agar UI dapat diperbarui jika diperlukan
            'param_score_qa' => number_format($newParamScoreQa, 2)
        ]);
    }

    private function calculateQaRollup(\App\Models\KertasKerja $kk)
    {
        // Kliping dari calculateMrRollup tapi untuk level QA
        $template = $kk->template;
        if (!$template) return;

        $aspects = \App\Models\TemplateIndicator::where('template_id', $template->id)
            ->whereNull('parent_id')
            ->get();

        $aspectScores = [];
        $aspectWeights = [];

        foreach ($aspects as $aspect) {
            $indicators = $aspect->children;
            $indScores = [];
            $indWeights = [];

            foreach ($indicators as $ind) {
                $params = $ind->children;
                $paramScores = [];
                $paramWeights = [];

                foreach ($params as $param) {
                    $ans = \App\Models\KkAnswer::where('kertas_kerja_id', $kk->id)
                        ->where('indikator_id', $param->id)
                        ->first();
                    
                    // Fallback: Jika nilai_qa adalah null, gunakan nilai standar (nilai tim)
                    $score = ($ans && $ans->nilai_qa !== null) ? $ans->nilai_qa : ($ans ? $ans->nilai : 0);
                    $weight = $param->bobot ?? 0;

                    $paramScores[] = $score * $weight;
                    $paramWeights[] = $weight;
                }

                $totalParamWeight = array_sum($paramWeights);
                $indScore = $totalParamWeight > 0 ? (array_sum($paramScores) / $totalParamWeight) : 0;
                
                // Simpan Skor Tingkat Indikator QA (Virtual record seperti halnya KK asli)
                // Gunakan fungsi updateOrCreate terhadap tabel KkAnswer
                $ansInd = \App\Models\KkAnswer::firstOrCreate(
                    ['kertas_kerja_id' => $kk->id, 'indikator_id' => $ind->id]
                );
                $ansInd->update(['nilai_qa' => $indScore]);

                $indScores[] = $indScore * ($ind->bobot ?? 0);
                $indWeights[] = ($ind->bobot ?? 0);
            }

            $totalIndWeight = array_sum($indWeights);
            $aspectScore = $totalIndWeight > 0 ? (array_sum($indScores) / $totalIndWeight) : 0;
            
            $ansAsp = \App\Models\KkAnswer::firstOrCreate(
                    ['kertas_kerja_id' => $kk->id, 'indikator_id' => $aspect->id]
            );
            $ansAsp->update(['nilai_qa' => $aspectScore]);

            $aspectScores[] = $aspectScore * ($aspect->bobot ?? 0);
            $aspectWeights[] = ($aspect->bobot ?? 0);
        }

        $totalAspectWeight = array_sum($aspectWeights);
        $finalScore = $totalAspectWeight > 0 ? (array_sum($aspectScores) / $totalAspectWeight) : 0;

        $kk->update(['nilai_akhir_qa' => $finalScore]);
    }
}
