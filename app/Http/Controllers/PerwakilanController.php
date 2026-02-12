<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PerwakilanController extends Controller
{
    public function index()
    {
        $perwakilan = \App\Models\Perwakilan::all();
        return view('perwakilan.index', compact('perwakilan'));
    }

    public function create()
    {
        return view('perwakilan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_wilayah' => 'required|string|max:10|unique:perwakilan',
            'nama_perwakilan' => 'required|string|max:255',
        ]);

        \App\Models\Perwakilan::create($request->all());

        return redirect()->route('perwakilan.index')
            ->with('success', 'Perwakilan berhasil ditambahkan!');
    }

    public function edit(\App\Models\Perwakilan $perwakilan)
    {
        return view('perwakilan.edit', compact('perwakilan'));
    }

    public function update(Request $request, \App\Models\Perwakilan $perwakilan)
    {
        $request->validate([
            'kode_wilayah' => 'required|string|max:10|unique:perwakilan,kode_wilayah,'.$perwakilan->id,
            'nama_perwakilan' => 'required|string|max:255',
        ]);

        $perwakilan->update($request->all());

        return redirect()->route('perwakilan.index')
            ->with('success', 'Perwakilan berhasil diperbarui!');
    }

    public function destroy(\App\Models\Perwakilan $perwakilan)
    {
        $perwakilan->delete();
        return redirect()->route('perwakilan.index')
            ->with('success', 'Perwakilan berhasil dihapus!');
    }
}
