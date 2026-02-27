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
        $roleInTeam = trim((string)\App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
            ->where('user_id', $user->id)
            ->value('role_dalam_tim'));

        $isCreator = $kertasKerja->user_id == $user->id;
        $isKetua = $roleInTeam == 'Ketua Tim';

    // Collaborative Permission Logic:
    // 1. Superadmin OR Rendal: Always Allow (View Only for Rendal unless specified)
    // 2. Draft/Revisi: All Team Members (Assigned to this ST)
    // 3. Review Ketua: Anggota (Creator) AND Ketua Tim
    // 4. Locked: Read-only for Team members
    
    $canEdit = false;
    $isMemberOfTeam = \App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
        ->where('user_id', $user->id)
        ->exists();

    $isRendal = $user->hasRole('Rendal') || $user->hasRole('Admin Perwakilan');

    if ($isSuperadmin) {
        $canEdit = true;
    } elseif ($isRendal) {
        $canEdit = false; // Rendal can view but not edit content directly
    } elseif ($isMemberOfTeam) {
        // TIERED PERMISSION LOGIC (Collaborative View)
        // 1. Posisi Ketua Tim (Draft / Revisi / Review Ketua)
        if ($kertasKerja->status_approval == 'Draft' || 
            str_starts_with($kertasKerja->status_approval, 'Revisi') || 
            $kertasKerja->status_approval == 'Review Ketua') {
            
            if ($isCreator || $isKetua) $canEdit = true;
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

    // If not superadmin, not rendal, and not in team, strictly 403
    if (!$isSuperadmin && !$isRendal && !$isMemberOfTeam) {
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
        $roleInTeam = trim((string)\App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
            ->where('user_id', $user->id)
            ->value('role_dalam_tim'));

        $isCreator = $kertasKerja->user_id == $user->id;
        $isKetua = $roleInTeam == 'Ketua Tim';

        $canEdit = false;
        $canEdit = false;
        if ($isSuperadmin) $canEdit = true;
        else {
             // TIERED PERMISSION LOGIC
             if ($kertasKerja->status_approval == 'Draft' || 
                str_starts_with($kertasKerja->status_approval, 'Revisi') || 
                $kertasKerja->status_approval == 'Review Ketua') {
                if ($isCreator || $isKetua) $canEdit = true;
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
            
            // Notify Dalnis
            $dalnis = \App\Models\StPersonel::where('st_id', $kk->st_id)
                ->where('role_dalam_tim', 'Dalnis')
                ->get();
            foreach ($dalnis as $p) {
                $p->user->notify(new \App\Notifications\KertasKerjaSubmitted($kk, $user));
            }

            return back()->with('success', 'Kertas kerja berhasil dikirim ke Dalnis.');
        } else {
            // Anggota submits to Ketua
            $kk->update(['status_approval' => 'Review Ketua']);

            // Notify Ketua Tim
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

        // Get User Role in this specific ST (with trim for safety)
        $roleInTeam = trim(\App\Models\StPersonel::where('st_id', $stId)
            ->where('user_id', $user->id)
            ->value('role_dalam_tim'));

        // Validation Logic
        if ($kk->status_approval == 'Review Ketua') {
            if ($roleInTeam !== 'Ketua Tim' && !$user->hasRole('Superadmin')) { 
                 return back()->with('error', 'Anda bukan Ketua Tim untuk penugasan ini.');
            }
            $kk->update(['status_approval' => 'Review Dalnis']);

            // Record Approval Note
            \App\Models\ReviewNote::create([
                'kk_id' => $kk->id,
                'reviewer_id' => $user->id,
                'catatan' => 'Kertas kerja disetujui oleh Ketua Tim.',
                'status' => 'Approved',
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

    /**
     * Calculate level-based score for a parameter.
     * Dispatches to building_block or criteria_fulfillment method.
     */
    private function calculateLevelScore(\App\Models\KertasKerja $kk, $indikatorId)
    {
        $metode = $kk->template->metode_penilaian ?? 'tally';

        // Get criteria grouped by level
        $criteriaByLevel = \App\Models\TemplateCriteria::where('indicator_id', $indikatorId)
            ->orderBy('level')
            ->get()
            ->groupBy('level');

        if ($criteriaByLevel->isEmpty()) return 0;

        // Get answer with fresh details
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
     * Building Block: Sequential level evaluation.
     * Level N+1 is only evaluated if ALL criteria at Level N are 'full' (Ya).
     * No 'Sebagian' option — only Ya/Tidak.
     * Score = achievedLevel + fraction of next level (e.g., 3.21)
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
                // All criteria met → level achieved
                $achievedLevel = $level;
            } else {
                // Partial → calculate fraction and STOP
                $fraction = $totalCriteria > 0
                    ? ($fulfilledCount / $totalCriteria)
                    : 0;
                return round($achievedLevel + $fraction, 2);
            }
        }

        return round((float)$achievedLevel, 2); // All levels achieved
    }

    /**
     * Criteria Fulfillment: Independent evaluation of all levels.
     * Ya=1.0, Sebagian=0.5, Tidak=0.
     * Each level contributes up to 1.0 to the score.
     * Score = sum of (level_fulfillment) across all levels (max 5.00)
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
            'value' => 'required|in:full,partial,none', // building_block only sends full/none
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

        if ($isSuperadmin) {
            $canEdit = true;
        } elseif ($isRendal) {
            $canEdit = false;
        } elseif ($isMemberOfTeam) {
            // TIERED PERMISSION LOGIC
            // 1. Posisi Ketua Tim (Draft / Revisi / Review Ketua)
            //    - Anggota (Creator) AND Ketua Tim can edit.
            if ($kertasKerja->status_approval == 'Draft' || 
                str_starts_with($kertasKerja->status_approval, 'Revisi') || 
                $kertasKerja->status_approval == 'Review Ketua') {
                
                if ($isCreator || $isKetua) $canEdit = true;
            }
            // 2. Posisi Dalnis (Review Dalnis)
            //    - Only Dalnis can edit.
            elseif ($kertasKerja->status_approval == 'Review Dalnis') {
                $roleInTeam = \App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
                    ->where('user_id', $user->id) 
                    ->value('role_dalam_tim');
                
                if ($roleInTeam == 'Dalnis') $canEdit = true;
            }
            // 3. Posisi Korwas (Review Korwas)
            //    - Only Korwas can edit.
            elseif ($kertasKerja->status_approval == 'Review Korwas') {
                $roleInTeam = \App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
                    ->where('user_id', $user->id) 
                    ->value('role_dalam_tim');

                if ($roleInTeam == 'Korwas') $canEdit = true;
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

        // 5. Recalculate Parameter Score (branching by scoring method)
        $metode = $kk->template->metode_penilaian ?? 'tally';
        $newParamScore = 0;

        if ($metode === 'tally') {
            // Legacy: percentage tally
            $totalCriteriaDb = \App\Models\TemplateCriteria::where('indicator_id', $indikatorId)->count();
            $currentScoreSum = $answer->details()->sum('score');
            if ($totalCriteriaDb > 0) {
                $newParamScore = ($currentScoreSum / $totalCriteriaDb) * 100;
            }
        } else {
            // Level-based scoring (building_block or criteria_fulfillment)
            $newParamScore = $this->calculateLevelScore($kk, $indikatorId);
        }

        $answer->update(['nilai' => $newParamScore]);

        // 6. Recalculate Rollup
        $this->calculateMrRollup($kk);

        // 7. Gather rollup scores for real-time UI update
        $kk->refresh();
        $rollupScores = \App\Models\KkAnswer::where('kertas_kerja_id', $kk->id)
            ->where('catatan', 'Auto-calculated ROLLUP')
            ->pluck('nilai', 'indikator_id');

        // Format score display based on method
        $paramScoreFormatted = $metode === 'tally'
            ? number_format($newParamScore, 0)
            : number_format($newParamScore, 2);

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

        // Check Authorization
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

    // QA Mode for Rendal & Team Response
    public function qa($id)
    {
        $kertasKerja = \App\Models\KertasKerja::findOrFail($id);
        $user = auth()->user();

        // Check Access: Rendal OR Team Member
        $isRendal = $user->hasRole('Rendal') || $user->hasRole('Admin Perwakilan') || $user->hasRole('Superadmin');
        
        $isMemberOfTeam = \App\Models\StPersonel::where('st_id', $kertasKerja->st_id)
            ->where('user_id', $user->id)
            ->exists();

        if (!$isRendal && !$isMemberOfTeam) {
            abort(403, 'Anda tidak memiliki akses ke halaman QA ini.');
        }

        // Reuse the edit logic/view but pass a flag 'isQaMode'
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

        $canEdit = false; // Regular edit disabled
        $isQaMode = true; // Enable QA View

        $canEdit = false; // Regular edit disabled
        $isQaMode = true; // Enable QA View
        $isQaFinal = $kertasKerja->status_qa == 'Final';

        // Determine permissions within QA Mode
        // Rendal: Can Edit QA Score/Note IF NOT FINAL.
        // Team: Can Edit Response IF NOT FINAL.
        $canEditQa = $isRendal && !$isQaFinal; 
        $canEditResponse = $isMemberOfTeam && !$isQaFinal; 

            return view('kertas-kerja.form', compact('kertasKerja', 'indicators', 'canEdit', 'isQaMode', 'canEditQa', 'canEditResponse', 'isQaFinal'));
    }

    public function finalizeQa(Request $request, $id)
    {
        \Log::info("Finalize QA Triggered for ID: $id by User: " . auth()->id());
        $kk = \App\Models\KertasKerja::findOrFail($id);
        $user = auth()->user();

        // Only Rendal/Superadmin can finalize
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

        // Only Rendal/Superadmin can UN-finalize
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

        // Only Superadmin, Korwas, Dalnis can un-finalize approval
        if (!$user->hasRole('Superadmin') && !$user->hasRole('Korwas') && !$user->hasRole('Dalnis')) {
             abort(403, 'Akses ditolak. Hanya Korwas/Dalnis yang dapat membatalkan status final.');
        }

        // Set status to 'Review Korwas' (Back to Korwas Position)
        // NOT Revisi, because Korwas wants to edit it or send it back manually.
        $kk->update([
            'status_approval' => 'Review Korwas',
            'status_qa' => 'Draft' // Reset QA too if it was Final
        ]);
        
        \Log::info("Approval Unfinalized (Revisi) by User: " . auth()->id() . " for KK ID: " . $id);

        return redirect()->route('kertas-kerja.index')->with('success', 'Status Final dibatalkan. Dokumen dikembalikan ke Posisi Korwas.');
    }

    public function updateTanggapanQa(Request $request)
    {
        $kkId = $request->input('kk_id');
        $user = auth()->user();
        
        // Prevent update if Final
        $kk = \App\Models\KertasKerja::findOrFail($kkId);
        if ($kk->status_qa == 'Final') {
            return response()->json(['success' => false, 'message' => 'QA sudah final. Tidak dapat diubah.'], 403);
        }

        // Authorization: Rendal/Superadmin OR Team Member
        $isRendal = $user->hasRole('Rendal') || $user->hasRole('Superadmin') || $user->hasRole('Admin Perwakilan');
        $isMemberOfTeam = \App\Models\StPersonel::where('st_id', $kk->st_id)->where('user_id', $user->id)->exists();

        if (!$isRendal && !$isMemberOfTeam) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access.'], 403);
        }

        $criteriaId = $request->input('criteria_id');
        $tanggapan = $request->input('tanggapan_qa');

        // Find existing answer/detail (must exist if QA is done, or at least structure exists)
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
        
        // Loop through inputs
        // Expected name="qa[criteria_id][score_qa]" and "qa[criteria_id][catatan_qa]"
        // But likely simpler to process individual AJAX requests or a bulk save.
        // Let's assume bulk save for now or use the existing updateSingle pattern adapted.
        
        // Actually, let's implement a single update method for QA similar to updateSingle
        // But for clarity let's check what the request has.
    }

    public function updateQaSingle(Request $request) 
    {
        $kkId = $request->input('kk_id');
        $criteriaId = $request->input('criteria_id');
        
        $kk = \App\Models\KertasKerja::findOrFail($kkId);
        
        // Ensure Answer Parent exists (it should, as only Final KKs are QA'd)
        // But to be safe:
        $criteria = \App\Models\TemplateCriteria::findOrFail($criteriaId);
        $answer = \App\Models\KkAnswer::firstOrCreate(
            ['kertas_kerja_id' => $kkId, 'indikator_id' => $criteria->indicator_id],
            ['nilai' => 0]
        );

        $detail = \App\Models\KkAnswerDetail::where('kk_answer_id', $answer->id)
            ->where('criteria_id', $criteriaId)
            ->first();

        if (!$detail) {
            // Should create detail if not exists (rare case)
             $detail = \App\Models\KkAnswerDetail::create([
                'kk_answer_id' => $answer->id,
                'criteria_id' => $criteriaId,
                'answer_value' => 'none',
                'score' => 0
            ]);
        }

        // Logic check: Is this a Value update (Radio) or Note update?
        if ($request->has('qa_value')) {
            $qaValue = $request->input('qa_value');
            
            // Calculate Score QA based on Value
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

        // Recalculate Parameter Score (QA)
        // Same logic as normal score but using score_qa
        // 1. Get all details for this answer
        $details = $answer->details;
        $totalCriteria = \App\Models\TemplateCriteria::where('indicator_id', $criteria->indicator_id)->count();
        
        // We need to handle null score_qa. If null, fallback to original score? 
        // Or treat as 0? Or user MUST fill all?
        // Assumption: If QA starts, it might be partial. 
        // Let's sum score_qa if not null, else use original score?
        // NO. Better to keep them independent. If score_qa is null, it counts as 0 or we init with original?
        // Let's assume for now: aggregate only known QA scores. 
        // BUT user expects to see a full score.
        // Better strategy: When creating QA record, maybe init with original?
        // OR: In calculation, if score_qa is null, use score (original).
        // This implies "No Correction" = "Agree with Team".
        
        $sumScoreQa = 0;
        foreach ($details as $d) {
            $val = $d->score_qa !== null ? $d->score_qa : $d->score; // Fallback to Team Score
            // Note: $d->score is 0-1 (e.g., 1.0 or 0.5), while we decided score_qa is 0-100 based on previous logic?
            // Wait, standard updateSingle uses 1.0/0.5 for score column.
            // AND then multiplies by 100 for parameter score.
            // Let's align. $detail->score in DB is likely float 0-1.
            // Let's check updateSingle: 
            // $score = 1.0; ... 'score' => $score
            // $newParamScore = ($currentScoreSum / $totalCriteriaDb) * 100;
            
            // So for QA:
            // if qa_value=full -> score_qa=1.0? Or 100?
            // The DB column score_qa is decimal(5,2).
            // Let's use 1.0 scale to be consistent with `score` column if we want to mix.
            // BUT the User Request said "100/50/0". 
            // If I change score_qa to 100, I can't mix with score easily.
            // Let's stick to 0-100 for score_qa as stored in DB for clarity in "Nilai QA".
            
            // Correction: The `input` view showed `value="{{ $scoreQa ... }}"`.
            // If we use Radio, we don't input score manually anymore.
            // Let's use 100 scale for `score_qa` column to avoid confusion.
            // And when using fallback, multiply original `score` (0-1) by 100.
            
            // wait, `score` in `kk_answer_details` might be 0-1.
            // Let's check updateSingle again.
            // Yes: $score = 1.0;
            
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
            'qa_score' => $detail->score_qa, // Send back so UI can update if needed (though UI handles radio)
            'param_score_qa' => number_format($newParamScoreQa, 2)
        ]);
    }

    private function calculateQaRollup(\App\Models\KertasKerja $kk)
    {
        // Clone of calculateMrRollup but for QA
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
                    
                    // Fallback: If nilai_qa is null, use nilai
                    $score = ($ans && $ans->nilai_qa !== null) ? $ans->nilai_qa : ($ans ? $ans->nilai : 0);
                    $weight = $param->bobot ?? 0;

                    $paramScores[] = $score * $weight;
                    $paramWeights[] = $weight;
                }

                $totalParamWeight = array_sum($paramWeights);
                $indScore = $totalParamWeight > 0 ? (array_sum($paramScores) / $totalParamWeight) : 0;
                
                // Save Indicator QA Score (We don't have separate table, so update KkAnswer virtual record)
                // Use updateOrCreate on KkAnswer
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
