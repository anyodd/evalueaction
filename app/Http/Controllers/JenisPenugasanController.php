<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisPenugasan;

class JenisPenugasanController extends Controller
{
    public function index()
    {
        $jenisPenugasan = JenisPenugasan::all();
        return view('jenis-penugasan.index', compact('jenisPenugasan'));
    }

    public function create()
    {
        return view('jenis-penugasan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:jenis_penugasan,kode',
        ]);

        JenisPenugasan::create($request->all());

        return redirect()->route('jenis-penugasan.index')
            ->with('success', 'Jenis Penugasan berhasil ditambahkan!');
    }

    public function edit(JenisPenugasan $jenisPenugasan)
    {
        return view('jenis-penugasan.edit', compact('jenisPenugasan'));
    }

    public function update(Request $request, JenisPenugasan $jenisPenugasan)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'kode' => 'required|string|max:50|unique:jenis_penugasan,kode,'.$jenisPenugasan->id,
        ]);

        $jenisPenugasan->update($request->all());

        return redirect()->route('jenis-penugasan.index')
            ->with('success', 'Jenis Penugasan berhasil diperbarui!');
    }

    public function destroy(JenisPenugasan $jenisPenugasan)
    {
        if ($jenisPenugasan->templates()->count() > 0) {
            return redirect()->route('jenis-penugasan.index')
                ->with('error', 'Tidak dapat dihapus karena sudah digunakan di Template!');
        }
        
        $hasSuratTugas = \App\Models\SuratTugas::where('jenis_penugasan_id', $jenisPenugasan->id)->exists();
        if ($hasSuratTugas) {
            return redirect()->route('jenis-penugasan.index')
                ->with('error', 'Tidak dapat dihapus karena sudah digunakan di Surat Tugas!');
        }

        $jenisPenugasan->delete();
        return redirect()->route('jenis-penugasan.index')
            ->with('success', 'Jenis Penugasan berhasil dihapus!');
    }
}
