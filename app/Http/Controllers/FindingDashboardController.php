<?php

namespace App\Http\Controllers;

use App\Models\KkTeo;
use App\Models\KkFinding;
use App\Models\Perwakilan;
use App\Models\SuratTugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FindingDashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = $request->get('year', date('Y'));
        $perwakilanId = $request->get('perwakilan_id');

        // Base Query for TEOs linked to the selected year and perwakilan
        $query = KkTeo::query()
            ->join('kk_answers', 'kk_teos.kk_answer_id', '=', 'kk_answers.id')
            ->join('kertas_kerja', 'kk_answers.kertas_kerja_id', '=', 'kertas_kerja.id')
            ->join('surat_tugas', 'kertas_kerja.st_id', '=', 'surat_tugas.id')
            ->where('surat_tugas.tahun_evaluasi', $year);

        if ($perwakilanId) {
            $query->where('surat_tugas.perwakilan_id', $perwakilanId);
        }

        // 1. Top TEOs (Condition)
        $topTeos = (clone $query)
            ->select('teo', DB::raw('count(*) as total'))
            ->groupBy('teo')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 2. Top Root Causes
        $topCauses = KkFinding::query()
            ->join('kk_teos', 'kk_findings.kk_teo_id', '=', 'kk_teos.id')
            ->join('kk_answers', 'kk_teos.kk_answer_id', '=', 'kk_answers.id')
            ->join('kertas_kerja', 'kk_answers.kertas_kerja_id', '=', 'kertas_kerja.id')
            ->join('surat_tugas', 'kertas_kerja.st_id', '=', 'surat_tugas.id')
            ->where('surat_tugas.tahun_evaluasi', $year);

        if ($perwakilanId) {
            $topCauses->where('surat_tugas.perwakilan_id', $perwakilanId);
        }

        $topCausesData = (clone $topCauses)
            ->select('cause', DB::raw('count(*) as total'))
            ->groupBy('cause')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 3. Top Recommendations
        $topRecsData = (clone $topCauses)
            ->select('recommendation', DB::raw('count(*) as total'))
            ->groupBy('recommendation')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 4. Distribution by Aspect (Top Level Indicators)
        // This is a bit complex due to indicator hierarchy. 
        // We'll join with indicators and try to find the root parent names.
        $aspectDistribution = (clone $query)
            ->join('template_indicators', 'kk_answers.indikator_id', '=', 'template_indicators.id')
            ->select('template_indicators.uraian as indicator_name', DB::raw('count(*) as total'))
            // Note: Ideally we want the Top Aspect, but for now we take the direct indicator
            ->groupBy('template_indicators.uraian')
            ->orderByDesc('total')
            ->get();

        // 5. Distribution by Perwakilan (Region)
        $perwakilanDistribution = (clone $query)
            ->join('perwakilan', 'surat_tugas.perwakilan_id', '=', 'perwakilan.id')
            ->select('perwakilan.nama_perwakilan as region_name', DB::raw('count(*) as total'))
            ->groupBy('perwakilan.nama_perwakilan')
            ->orderByDesc('total')
            ->get();

        $stats = [
            'total_teo' => (clone $query)->count(),
            'total_finding' => (clone $topCauses)->count(),
            'total_st' => (clone $query)->distinct('surat_tugas.id')->count('surat_tugas.id'),
        ];

        $perwakilans = Perwakilan::all();
        $years = SuratTugas::distinct()->pluck('tahun_evaluasi');

        return view('findings.dashboard', compact(
            'topTeos', 'topCausesData', 'topRecsData', 
            'aspectDistribution', 'perwakilanDistribution', 
            'stats', 'perwakilans', 'years', 'year', 'perwakilanId'
        ));
    }
}
