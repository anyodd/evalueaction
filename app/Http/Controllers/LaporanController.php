<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\KertasKerja;

class LaporanController extends Controller
{
    public function index()
    {
        // Fetch only Finalized Kertas Kerja
        // Eager load related Surat Tugas and Perwakilan
        $reports = KertasKerja::where('status_approval', 'Final')
            ->with(['suratTugas.perwakilan', 'user'])
            ->latest()
            ->get();

        return view('laporan.index', compact('reports'));
    }
}
