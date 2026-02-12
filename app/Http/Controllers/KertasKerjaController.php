<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class KertasKerjaController extends Controller
{
    public function index()
    {
        $query = \App\Models\KertasKerja::with(['suratTugas', 'user'])->latest();
        
        if (!auth()->user()->role || (!in_array(auth()->user()->role->name, ['Superadmin', 'Rendal']))) {
            $user_perwakilan = auth()->user()->perwakilan_id;
            $query->whereHas('suratTugas', function($q) use ($user_perwakilan) {
                $q->where('perwakilan_id', $user_perwakilan);
            });
        }

        $kertasKerja = $query->get();
        return view('kertas-kerja.index', compact('kertasKerja'));
    }

    public function create()
    {
        $st_query = \App\Models\SuratTugas::query();
        
        if (!auth()->user()->role || (!in_array(auth()->user()->role->name, ['Superadmin', 'Rendal']))) {
            $st_query->where('perwakilan_id', auth()->user()->perwakilan_id);
        }

        $suratTugas = $st_query->get();
        return view('kertas-kerja.create', compact('suratTugas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'st_id' => 'required|exists:surat_tugas,id',
            'judul_kk' => 'required|string|max:255',
            'isi_kk' => 'nullable|string',
        ]);

        \App\Models\KertasKerja::create([
            'st_id' => $request->st_id,
            'user_id' => auth()->id(),
            'judul_kk' => $request->judul_kk,
            'isi_kk' => $request->isi_kk,
            'status_approval' => 'Draft',
        ]);

        return redirect()->route('kertas-kerja.index')
            ->with('success', 'Kertas Kerja berhasil disimpan!');
    }

}
