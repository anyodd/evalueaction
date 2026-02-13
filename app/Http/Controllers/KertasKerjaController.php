<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KertasKerjaController extends Controller
{
    public function index()
    {
        if (auth()->user()->role && auth()->user()->role->name === 'Superadmin') {
            $assignments = \App\Models\SuratTugas::with(['jenisPenugasan', 'template', 'kertasKerja' => function($q) {
                $q->where('user_id', auth()->id());
            }])->latest()->get();
        } else {
            // 1. Get STs where user is assigned
            $assignments = \App\Models\SuratTugas::whereHas('personel', function($q) {
                $q->where('user_id', auth()->id());
            })->with(['jenisPenugasan', 'template', 'kertasKerja' => function($q) {
                $q->where('user_id', auth()->id());
            }])->latest()->get();
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

        // Check if KK already exists
        $kk = \App\Models\KertasKerja::where('st_id', $st->id)
            ->where('user_id', auth()->id())
            ->first();

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
        // Authorization
        if ($kertasKerja->user_id !== auth()->id() && auth()->user()->role->name !== 'Superadmin') {
            abort(403);
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

        return view('kertas-kerja.form', compact('kertasKerja', 'indicators'));
    }

    public function update(Request $request, \App\Models\KertasKerja $kertasKerja)
    {
        if ($kertasKerja->user_id !== auth()->id() && auth()->user()->role->name !== 'Superadmin') {
            abort(403);
        }

        $data = $request->input('answers', []);
        
        foreach ($data as $indikatorId => $val) {
            $nilai = $val['nilai'] ?? null;
            $catatan = $val['catatan'] ?? null;

            // Handle Criteria Tally Logic
            if (isset($val['criteria']) && is_array($val['criteria'])) {
                // Save Answer first (or find existing)
                $answer = \App\Models\KkAnswer::firstOrCreate(
                    ['kertas_kerja_id' => $kertasKerja->id, 'indikator_id' => $indikatorId],
                    ['nilai' => 0]
                );

                // Sync Checklist details
                // First, delete old details for this answer
                \App\Models\KkAnswerDetail::where('kk_answer_id', $answer->id)->delete();

                $checkedCount = 0;
                foreach ($val['criteria'] as $criteriaId => $isChecked) {
                    if ($isChecked == 'on') {
                        \App\Models\KkAnswerDetail::create([
                            'kk_answer_id' => $answer->id,
                            'criteria_id' => $criteriaId,
                            'is_checked' => true
                        ]);
                        $checkedCount++;
                    }
                }
                
                // Calculate Score (0-100 based on percentage of criteria met)
                // Assuming all criteria are equal for now
                $totalCriteria = \App\Models\TemplateCriteria::where('indicator_id', $indikatorId)->count();
                if ($totalCriteria > 0) {
                    $nilai = ($checkedCount / $totalCriteria) * 100;
                } else {
                    $nilai = 0;
                }
                
                // Update the main answer value
                $answer->update(['nilai' => $nilai, 'catatan' => $catatan]);
                
            } else {
                // Standard Input (Manual / Text)
                \App\Models\KkAnswer::updateOrCreate(
                    [
                        'kertas_kerja_id' => $kertasKerja->id,
                        'indikator_id' => $indikatorId
                    ],
                    [
                        'nilai' => $nilai,
                        'catatan' => $catatan
                    ]
                );
            }
        }
        
        // Calculate Final Score (Simple Average of 0-100 scores)
        // Works for both Manual and Criteria based scores if normalized to 0-100
        $allAnswers = \App\Models\KkAnswer::where('kertas_kerja_id', $kertasKerja->id)
            ->whereHas('indicator', function($q) {
                $q->whereIn('tipe', ['score_manual', 'criteria_tally']);
            })
            ->get();
            
        if ($allAnswers->count() > 0) {
            $totalScore = $allAnswers->avg('nilai');
            $kertasKerja->update(['nilai_akhir' => $totalScore]);
        }

        return redirect()->route('kertas-kerja.index')
            ->with('success', 'Kertas Kerja berhasil disimpan!');
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
}
