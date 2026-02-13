<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KertasKerjaController extends Controller
{
    public function index()
    {
        if (auth()->user()->role && auth()->user()->role->name === 'Superadmin') {
            $assignments = \App\Models\SuratTugas::with(['jenisPenugasan', 'template', 'kertasKerja'])
                ->latest()
                ->get();
        } else {
            // 1. Get STs where user is assigned
            $assignments = \App\Models\SuratTugas::whereHas('personel', function($q) {
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
        
        // Validation: Check if user is in team (Bypass for Superadmin)
        $isTim = \App\Models\StPersonel::where('st_id', $st->id)
            ->where('user_id', auth()->id())
            ->exists();

        $isSuperadmin = auth()->user()->role && auth()->user()->role->name === 'Superadmin';

        if (!$isTim && !$isSuperadmin) {
            abort(403, 'Anda tidak terdaftar dalam tim penugasan ini.');
        }

        // Check if KK already exists for this ST
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
        
        // Get User Role in this specific ST
        $roleInTeam = \App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
            ->where('user_id', $user->id)
            ->value('role_dalam_tim');

        $isCreator = $kertasKerja->user_id == $user->id;
        $isKetua = $roleInTeam == 'Ketua Tim';

    // Collaborative Permission Logic:
    // 1. Superadmin: Always Allow
    // 2. Draft/Revisi: All Team Members (Assigned to this ST)
    // 3. Review Ketua: Anggota (Creator) AND Ketua Tim
    // 4. Locked: Read-only for Team members
    
    $canEdit = false;
    $isMemberOfTeam = \App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
        ->where('user_id', $user->id)
        ->exists();

    if ($isSuperadmin) $canEdit = true;
    elseif ($isMemberOfTeam) {
        if ($kertasKerja->status_approval == 'Draft' || str_starts_with($kertasKerja->status_approval, 'Revisi')) {
            $canEdit = true;
        }
        elseif ($kertasKerja->status_approval == 'Review Ketua') {
            if ($isCreator || $isKetua) $canEdit = true;
        }
    }

    // If not superadmin and not in team, strictly 403
    if (!$isSuperadmin && !$isMemberOfTeam) {
        abort(403, 'Anda tidak terdaftar dalam tim penugasan ini.');
    }

    $kertasKerja->load('template.indicators', 'answers.details');
    
    // Organize indicators hierarchy
    $indicators = \App\Models\TemplateIndicator::where('template_id', $kertasKerja->template_id)
        ->whereNull('parent_id')
        ->with(['children' => function($q) {
            $q->orderBy('id')->with(['criteria', 'children' => function($q2) {
                $q2->orderBy('id')->with('criteria');
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
        
        // Get User Role in this specific ST
        $roleInTeam = \App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
            ->where('user_id', $user->id)
            ->value('role_dalam_tim');

        $isCreator = $kertasKerja->user_id == $user->id;
        $isKetua = $roleInTeam == 'Ketua Tim';

        $canEdit = false;
        if ($isSuperadmin) $canEdit = true;
        elseif ($kertasKerja->status_approval == 'Draft' || str_starts_with($kertasKerja->status_approval, 'Revisi')) {
            if ($isCreator) $canEdit = true;
        }
        elseif ($kertasKerja->status_approval == 'Review Ketua') {
            if ($isCreator || $isKetua) $canEdit = true;
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

            // Handle File Upload
            if ($request->hasFile("answers.$indikatorId.evidence_file")) {
                $file = $request->file("answers.$indikatorId.evidence_file");
                // Store in Google Drive 'evidence' folder
                // Ensure 'google' disk is configured
                try {
                    $path = $file->store('evidence', 'google');
                    $evidenceFile = $path;
                } catch (\Exception $e) {
                    // Fallback or log error if GDrive fails, maybe to local public
                    \Log::error("GDrive Upload Failed: " . $e->getMessage());
                }
            }

            // Handle Criteria Tally Logic (Level 3 Parameter)
            if (isset($val['criteria']) && is_array($val['criteria'])) {
                $answer = \App\Models\KkAnswer::firstOrCreate(
                    ['kertas_kerja_id' => $kertasKerja->id, 'indikator_id' => $indikatorId],
                    ['nilai' => 0]
                );

                // Update evidence if new file uploaded, else keep old
                $updateData = ['catatan' => $catatan, 'evidence_link' => $evidenceLink];
                if ($evidenceFile) {
                    $updateData['evidence_file'] = $evidenceFile;
                }
                $answer->update($updateData);

                // Sync Checklist details
                // We DON'T delete anymore to preserve evidence files unless we implement soft delete or smart sync
                // \App\Models\KkAnswerDetail::where('kk_answer_id', $answer->id)->delete(); 

                $totalScoreSum = 0;
                $maxPossibleScore = 0;
                // $criteriaCount = count($val['criteria']); // Use DB count later

                foreach ($val['criteria'] as $criteriaId => $cData) {
                    // cData structure changed to array: ['value' => '...', 'catatan' => '...', 'link' => '...']
                    $choice = $cData['value'] ?? 'none';
                    $catatanCriteria = $cData['catatan'] ?? null;
                    $linkCriteria = $cData['link'] ?? null;

                    // Choice: 'full' (1.0), 'partial' (0.5), 'none' (0.0)
                    $score = 0;
                    if ($choice === 'full') $score = 1.0;
                    elseif ($choice === 'partial') $score = 0.5;
                    
                    // Handle Evidence File per Criteria
                    $evidenceFileCriteria = null;
                    if ($request->hasFile("answers.$indikatorId.criteria.$criteriaId.evidence")) {
                        $cFile = $request->file("answers.$indikatorId.criteria.$criteriaId.evidence");
                        try {
                            $path = $cFile->store('evidence', 'google');
                            $evidenceFileCriteria = $path;
                        } catch (\Exception $e) {
                            \Log::error("Criteria GDrive Upload Failed: " . $e->getMessage());
                            // Fallback to Public Disk
                            try {
                                $path = $cFile->store('evidence', 'public');
                                $evidenceFileCriteria = $path;
                                // Optional: Flash warning (only once to avoid spamming)
                                if (!session()->has('warning_upload')) {
                                    session()->flash('warning_upload', 'Gagal upload ke Google Drive. File disimpan di server lokal.');
                                }
                            } catch (\Exception $ex) {
                                \Log::error("Local Upload Failed: " . $ex->getMessage());
                            }
                        }
                    }

                    // Prepare Update Data
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
                
                // Calculate Parameter Score (0-100)
                // Re-fetch total criteria count from DB to be accurate
                $totalCriteriaDb = \App\Models\TemplateCriteria::where('indicator_id', $indikatorId)->count();
                if ($totalCriteriaDb > 0) {
                     // Normalize to 100
                    $nilai = ($totalScoreSum / $totalCriteriaDb) * 100;
                } else {
                    $nilai = 0;
                }
                
                $answer->update(['nilai' => $nilai]);
                
            } else {
                // Standard Input (Manual / Text)
                // Check existing to preserve evidence if not uploading new
                $answer = \App\Models\KkAnswer::firstOrCreate(
                    ['kertas_kerja_id' => $kertasKerja->id, 'indikator_id' => $indikatorId]
                );
                
                $updateData = ['nilai' => $nilai, 'catatan' => $catatan];
                $answer->update($updateData);
            }
        }
        
        // Recalculate Hierarchy Rollup
        $this->calculateMrRollup($kertasKerja);

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

        // Check Role (with string cast for PHP 8.1 compatibility)
    $roleInTeam = trim((string)\App\Models\StPersonel::where('st_id', $kk->st_id)
        ->where('user_id', $user->id)
        ->value('role_dalam_tim'));

        if ($roleInTeam == 'Ketua Tim') {
            // Ketua Tim skips 'Review Ketua' and goes straight to 'Review Dalnis'
            $kk->update(['status_approval' => 'Review Dalnis']);
            return back()->with('success', 'Kertas kerja berhasil dikirim ke Dalnis.');
        } else {
            // Anggota submits to Ketua
            $kk->update(['status_approval' => 'Review Ketua']);
            return back()->with('success', 'Kertas kerja berhasil dilaporkan ke Ketua Tim.');
        }
    }

    public function approve($id)
    {
        $kk = \App\Models\KertasKerja::findOrFail($id);
        $user = auth()->user();
        $stId = $kk->st_id;

        // Get User Role in this specific ST (with trim for safety)
        $roleInTeam = trim(\App\Models\StPersonel::where('st_id', $stId)
            ->where('user_id', $user->id)
            ->value('role_dalam_tim'));

        // Validation Logic
        if ($kk->status_approval == 'Review Ketua') {
            if ($roleInTeam !== 'Ketua Tim' && !$user->hasRole('Superadmin')) { // Allow Superadmin override
                 return back()->with('error', 'Anda bukan Ketua Tim untuk penugasan ini.');
            }
            $kk->update(['status_approval' => 'Review Dalnis']);
            return back()->with('success', 'Disetujui. Lanjut ke Dalnis.');
        }

        if ($kk->status_approval == 'Review Dalnis') {
            if ($roleInTeam !== 'Dalnis' && !$user->hasRole('Superadmin')) {
                 return back()->with('error', 'Anda bukan Dalnis untuk penugasan ini.');
            }
            $kk->update(['status_approval' => 'Review Korwas']);
            return back()->with('success', 'Disetujui. Lanjut ke Korwas.');
        }

        if ($kk->status_approval == 'Review Korwas') {
            if ($roleInTeam !== 'Korwas' && !$user->hasRole('Superadmin')) {
                 return back()->with('error', 'Anda bukan Korwas untuk penugasan ini.');
            }
            $kk->update(['status_approval' => 'Final']);
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
        if ($kk->status_approval == 'Review Ketua') {
             if ($roleInTeam !== 'Ketua Tim' && !$isSuperadmin) {
                 return back()->with('error', 'Anda bukan Ketua Tim untuk penugasan ini.');
             }
             $newStatus = 'Draft';
        } elseif ($kk->status_approval == 'Review Dalnis') {
             if ($roleInTeam !== 'Dalnis' && !$isSuperadmin) {
                 return back()->with('error', 'Anda bukan Dalnis untuk penugasan ini.');
             }
             $newStatus = 'Review Ketua';
        } elseif ($kk->status_approval == 'Review Korwas') {
             if ($roleInTeam !== 'Korwas' && !$isSuperadmin) {
                 return back()->with('error', 'Anda bukan Korwas untuk penugasan ini.');
             }
             $newStatus = 'Review Dalnis';
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
                'status' => 'Pending', // Or however you track notes
            ]);
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
            // Level 2: Indicators
            $indicators = $aspect->children;
            $indScores = [];
            $indWeights = [];

            foreach ($indicators as $ind) {
                // Level 3: Parameters
                $params = $ind->children;
                $paramScores = [];
                $paramWeights = [];

                foreach ($params as $param) {
                    // Get Score from DB
                    $ans = \App\Models\KkAnswer::where('kertas_kerja_id', $kk->id)
                        ->where('indikator_id', $param->id)
                        ->first();
                    
                    $score = $ans ? $ans->nilai : 0;
                    $weight = $param->bobot ?? 0;

                    $paramScores[] = $score * $weight;
                    $paramWeights[] = $weight;
                }

                // Calculate Indicator Score
                $totalParamWeight = array_sum($paramWeights);
                $indScore = $totalParamWeight > 0 ? (array_sum($paramScores) / $totalParamWeight) : 0; // Weighted Avg
                // If weight is percentage (e.g. 30), it's fine. 
                // If it was already 0-100, checking sum is enough.

                // Save Indicator Score (Virtual Answer)
                \App\Models\KkAnswer::updateOrCreate(
                    ['kertas_kerja_id' => $kk->id, 'indikator_id' => $ind->id],
                    ['nilai' => $indScore, 'catatan' => 'Auto-calculated ROLLUP']
                );

                $indScores[] = $indScore * ($ind->bobot ?? 0);
                $indWeights[] = ($ind->bobot ?? 0);
            }

            // Calculate Aspect Score
            $totalIndWeight = array_sum($indWeights);
            $aspectScore = $totalIndWeight > 0 ? (array_sum($indScores) / $totalIndWeight) : 0; // If weights are relative (e.g. 40, 60)
            
            // NOTE: If weights are absolute percentages of parent (e.g. 40% of Total), we might need different logic.
            // Assuming weights at each level sum to 100 (or relative ratio). 
            // example: Aspect 1 (40%), Aspect 2 (60%).
            // Inside Aspect 1: Ind A (50), Ind B (50).
            
            // Save Aspect Score
            \App\Models\KkAnswer::updateOrCreate(
                    ['kertas_kerja_id' => $kk->id, 'indikator_id' => $aspect->id],
                    ['nilai' => $aspectScore, 'catatan' => 'Auto-calculated ROLLUP']
            );

            $aspectScores[] = $aspectScore * ($aspect->bobot ?? 0);
            $aspectWeights[] = ($aspect->bobot ?? 0);
        }

        // Final Score
        $totalAspectWeight = array_sum($aspectWeights);
        $finalScore = $totalAspectWeight > 0 ? (array_sum($aspectScores) / $totalAspectWeight) : 0; 
        
        // If weights are like 40, 30, 30 (sum 100), dividing by 100 is correct.
        
        $kk->update(['nilai_akhir' => $finalScore]);
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
        // Validation
        $request->validate([
            'kk_id' => 'required|exists:kertas_kerja,id',
            'indicator_id' => 'required|exists:template_indicators,id',
            'criteria_id' => 'required|exists:template_criteria,id',
            'value' => 'required|in:full,partial,none',
        ]);

        $kk = \App\Models\KertasKerja::findOrFail($request->kk_id);
        
        // Authorization (Same as Header Edit/Update)
        $user = auth()->user();
        $roleInTeam = \App\Models\StPersonel::where('st_id', $kk->st_id)
            ->where('user_id', $user->id) 
            ->value('role_dalam_tim');

        $isCreator = $kk->user_id == $user->id;
        $isKetua = $roleInTeam == 'Ketua Tim';
        $isSuperadmin = $user->hasRole('Superadmin');

        $canEdit = false;
        $isMemberOfTeam = \App\Models\StPersonel::where('st_id', $kk->st_id)
            ->where('user_id', $user->id)
            ->exists();

        if ($isSuperadmin) $canEdit = true;
        elseif ($isMemberOfTeam) {
            if ($kk->status_approval == 'Draft' || str_starts_with($kk->status_approval, 'Revisi')) {
                 $canEdit = true; // All assigned personnel can save in Draft
            }
            elseif ($kk->status_approval == 'Review Ketua') {
                if ($isCreator || $isKetua) $canEdit = true;
            }
        }

        if (!$canEdit) {
             return response()->json(['success' => false, 'message' => 'Status dokumen ini (' . $kk->status_approval . ') terkunci untuk Anda.'], 403);
        }
        // Logic
        $indikatorId = $request->indicator_id;
        $criteriaId = $request->criteria_id;
        $val = $request->value;
        $catatan = $request->catatan;
        $link = $request->link;
        
        // 1. Find/Create Parent Answer
        $answer = \App\Models\KkAnswer::firstOrCreate(
            ['kertas_kerja_id' => $kk->id, 'indikator_id' => $indikatorId],
            ['nilai' => 0]
        );

        // 2. Score Logic
        $score = 0;
        if ($val === 'full') $score = 1.0;
        elseif ($val === 'partial') $score = 0.5;

        // 3. File Upload (with Fallback)
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

        // 5. Recalculate Parameter Score
        $totalCriteriaDb = \App\Models\TemplateCriteria::where('indicator_id', $indikatorId)->count();
        $currentScoreSum = $answer->details()->sum('score'); // Query fresh sum
        
        $newParamScore = 0;
        if ($totalCriteriaDb > 0) {
            $newParamScore = ($currentScoreSum / $totalCriteriaDb) * 100;
        }
        $answer->update(['nilai' => $newParamScore]);

        // 6. Recalculate Rollup
        $this->calculateMrRollup($kk);

        return response()->json([
            'success' => true,
            'message' => 'Data tersimpan.',
            'param_score' => number_format($newParamScore, 0),
            'evidence_file' => $evidenceFileCriteria, // Return path to update UI if needed
            'is_local' => $evidenceFileCriteria ? \Storage::disk('public')->exists($evidenceFileCriteria) : false
        ]);
    }
}
