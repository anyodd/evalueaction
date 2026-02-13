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
        $user_query = \App\Models\User::whereHas('role', function($q) {
            $q->where('name', '!=', 'Admin Perwakilan');
        });
        
        // Scope users for Admin Perwakilan
        if (auth()->user()->role && auth()->user()->role->name === 'Admin Perwakilan') {
            $user_query->where('perwakilan_id', auth()->user()->perwakilan_id);
        }

        $users = $user_query->orderBy('name')->get();
        $jenisPenugasan = \App\Models\JenisPenugasan::orderBy('kode')->get();
        return view('surat-tugas.create', compact('users', 'jenisPenugasan'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nomor_st' => 'required|string|max:255',
            'tgl_st' => 'required|date',
            'nama_objek' => 'required|string|max:255',
            'tahun_evaluasi' => 'required|integer',
            'tgl_mulai' => 'nullable|date',
            'tgl_selesai' => 'nullable|date|after_or_equal:tgl_mulai',
            'jenis_penugasan_id' => 'required|exists:jenis_penugasan,id',
            'personel' => 'required|array',
            'peran' => 'required|array',
        ]);

        // Find active template
        $template = \App\Models\KkTemplate::where('jenis_penugasan_id', $request->jenis_penugasan_id)
            ->where('tahun', $request->tahun_evaluasi)
            ->where('is_active', true)
            ->latest()
            ->first();

        \Illuminate\Support\Facades\DB::transaction(function() use ($request, $template) {
            $st = \App\Models\SuratTugas::create([
                'nomor_st' => $request->nomor_st,
                'tgl_st' => $request->tgl_st,
                'nama_objek' => $request->nama_objek,
                'tahun_evaluasi' => $request->tahun_evaluasi,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'jenis_penugasan_id' => $request->jenis_penugasan_id,
                'template_id' => $template ? $template->id : null,
                'admin_id' => auth()->id(),
                'perwakilan_id' => auth()->user()->perwakilan_id ?? 1, 
            ]);

            foreach ($request->personel as $key => $userId) {
                if ($userId) {
                    \App\Models\StPersonel::create([
                        'st_id' => $st->id,
                        'user_id' => $userId,
                        'role_dalam_tim' => $request->peran[$key] ?? 'Anggota',
                    ]);
                }
            }
        });

        return redirect()->route('surat-tugas.index')
            ->with('success', 'Surat Tugas dan Susunan Tim berhasil disimpan!');
    }

    public function edit(\App\Models\SuratTugas $surat_tuga)
    {
        // Check access
        if (!auth()->user()->role || (!in_array(auth()->user()->role->name, ['Superadmin', 'Rendal']))) {
            if ($surat_tuga->perwakilan_id !== auth()->user()->perwakilan_id) {
                abort(403);
            }
        }

        $user_query = \App\Models\User::whereHas('role', function($q) {
            $q->where('name', '!=', 'Admin Perwakilan');
        });

        if (auth()->user()->role && auth()->user()->role->name === 'Admin Perwakilan') {
            $user_query->where('perwakilan_id', auth()->user()->perwakilan_id);
        }
        $users = $user_query->orderBy('name')->get();

        $surat_tuga->load('personel');
        $jenisPenugasan = \App\Models\JenisPenugasan::orderBy('kode')->get();

        return view('surat-tugas.edit', compact('surat_tuga', 'users', 'jenisPenugasan'));
    }

    public function update(Request $request, \App\Models\SuratTugas $surat_tuga)
    {
        $request->validate([
            'nomor_st' => 'required|string|max:255',
            'tgl_st' => 'required|date',
            'nama_objek' => 'required|string|max:255',
            'tahun_evaluasi' => 'required|integer',
            'tgl_mulai' => 'nullable|date',
            'tgl_selesai' => 'nullable|date|after_or_equal:tgl_mulai',
            'jenis_penugasan_id' => 'required|exists:jenis_penugasan,id',
            'personel' => 'required|array',
            'peran' => 'required|array',
        ]);

        // Find active template (re-check if changed)
        $template = \App\Models\KkTemplate::where('jenis_penugasan_id', $request->jenis_penugasan_id)
            ->where('tahun', $request->tahun_evaluasi)
            ->where('is_active', true)
            ->latest()
            ->first();

        \Illuminate\Support\Facades\DB::transaction(function() use ($request, $surat_tuga, $template) {
            $surat_tuga->update([
                'nomor_st' => $request->nomor_st,
                'tgl_st' => $request->tgl_st,
                'nama_objek' => $request->nama_objek,
                'tahun_evaluasi' => $request->tahun_evaluasi,
                'tgl_mulai' => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'jenis_penugasan_id' => $request->jenis_penugasan_id,
                'template_id' => $template ? $template->id : null,
            ]);

            // Sync Personnel: Delete and Re-insert
            \App\Models\StPersonel::where('st_id', $surat_tuga->id)->delete();

            foreach ($request->personel as $key => $userId) {
                if ($userId) {
                    \App\Models\StPersonel::create([
                        'st_id' => $surat_tuga->id,
                        'user_id' => $userId,
                        'role_dalam_tim' => $request->peran[$key] ?? 'Anggota',
                    ]);
                }
            }
        });

        return redirect()->route('surat-tugas.index')
            ->with('success', 'Surat Tugas dan Susunan Tim berhasil diperbarui!');
    }

    public function destroy(\App\Models\SuratTugas $surat_tuga)
    {
        $surat_tuga->delete();
        return redirect()->route('surat-tugas.index')
            ->with('success', 'Surat Tugas berhasil dihapus!');
    }

    public function print(\App\Models\SuratTugas $surat_tuga)
    {
        $surat_tuga->load(['personel.user', 'admin', 'perwakilan']);
        
        // Ensure perwakilan data exists or use defaults/placeholders if needed for testing
        // Ideally data should be in DB.
        
        return view('surat-tugas.print', compact('surat_tuga'));
    }
}
