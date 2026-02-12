<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SuratTugasController extends Controller
{
    public function index()
    {
        $query = \App\Models\SuratTugas::with('admin', 'perwakilan')->latest();
        
        // Scoping per Perwakilan (kecuali Superadmin / Rendal)
        if (!auth()->user()->role || (!in_array(auth()->user()->role->name, ['Superadmin', 'Rendal']))) {
            $query->where('perwakilan_id', auth()->user()->perwakilan_id);
        }

        $suratTugas = $query->get();
        return view('surat-tugas.index', compact('suratTugas'));
    }
    
    public function create()
    {
        return view('surat-tugas.create');
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nomor_st' => 'required|string|max:255',
            'tgl_st' => 'required|date',
            'nama_objek' => 'required|string|max:255',
            'tahun_evaluasi' => 'required|integer',
        ]);

        \App\Models\SuratTugas::create([
            'nomor_st' => $request->nomor_st,
            'tgl_st' => $request->tgl_st,
            'nama_objek' => $request->nama_objek,
            'tahun_evaluasi' => $request->tahun_evaluasi,
            'admin_id' => auth()->id(),
            'perwakilan_id' => auth()->user()->perwakilan_id, // Store user's representative office
        ]);

        return redirect()->route('surat-tugas.index')
            ->with('success', 'Surat Tugas berhasil disimpan!');
    }

    public function edit(\App\Models\SuratTugas $surat_tuga)
    {
        // Check access
        if (!auth()->user()->role || (!in_array(auth()->user()->role->name, ['Superadmin', 'Rendal']))) {
            if ($surat_tuga->perwakilan_id !== auth()->user()->perwakilan_id) {
                abort(403);
            }
        }

        return view('surat-tugas.edit', compact('surat_tuga'));
    }

    public function update(Request $request, \App\Models\SuratTugas $surat_tuga)
    {
        $request->validate([
            'nomor_st' => 'required|string|max:255',
            'tgl_st' => 'required|date',
            'nama_objek' => 'required|string|max:255',
            'tahun_evaluasi' => 'required|integer',
        ]);

        $surat_tuga->update([
            'nomor_st' => $request->nomor_st,
            'tgl_st' => $request->tgl_st,
            'nama_objek' => $request->nama_objek,
            'tahun_evaluasi' => $request->tahun_evaluasi,
        ]);

        return redirect()->route('surat-tugas.index')
            ->with('success', 'Surat Tugas berhasil diperbarui!');
    }

    public function destroy(\App\Models\SuratTugas $surat_tuga)
    {
        $surat_tuga->delete();
        return redirect()->route('surat-tugas.index')
            ->with('success', 'Surat Tugas berhasil dihapus!');
    }

    public function print(\App\Models\SuratTugas $surat_tuga)
    {
        // Placeholder for print view
        return view('surat-tugas.print', compact('surat_tuga'));
    }
}
