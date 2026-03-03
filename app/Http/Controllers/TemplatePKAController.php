<?php

namespace App\Http\Controllers;

use App\Models\ProgramKerja;
use App\Models\PkLangkah;
use App\Models\JenisPenugasan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TemplatePKAController extends Controller
{
    /**
     * Daftar Template PKA (st_id IS NULL).
     */
    public function index()
    {
        $templates = ProgramKerja::whereNull('st_id')
            ->withCount('langkah')
            ->with('creator')
            ->latest()
            ->get();

        return view('template-pka.index', compact('templates'));
    }

    /**
     * Form buat template PKA baru.
     */
    public function create()
    {
        $jenisPenugasans = JenisPenugasan::all();
        return view('template-pka.create', compact('jenisPenugasans'));
    }

    /**
     * Simpan template PKA baru.
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tujuan' => 'nullable|string',
            'ruang_lingkup' => 'nullable|string',
            'metodologi' => 'nullable|string',
        ]);

        $pka = ProgramKerja::create([
            'st_id' => null, // Template — tanpa ST
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tujuan' => $request->tujuan,
            'ruang_lingkup' => $request->ruang_lingkup,
            'metodologi' => $request->metodologi,
            'created_by' => auth()->id(),
            'status' => 'draft',
        ]);

        return redirect()->route('template-pka.show', $pka->id)
            ->with('success', 'Template PKA dibuat. Silakan tambahkan langkah-langkah.');
    }

    /**
     * Detail Template PKA (builder langkah).
     */
    public function show($id)
    {
        $template = ProgramKerja::whereNull('st_id')->findOrFail($id);
        $langkahRoot = $template->langkahRoot()->with(['children.children', 'kkTemplate', 'children.kkTemplate', 'children.children.kkTemplate'])->get();

        // Hitung statistik
        $totalLangkah = $template->langkah()->count();

        // Tambahkan template KK yang tersedia
        $kkTemplates = \App\Models\KkTemplate::where('is_active', true)->whereNull('jenis_penugasan_id')->orWhereHas('jenisPenugasan')->get();

        return view('template-pka.show', compact('template', 'langkahRoot', 'totalLangkah', 'kkTemplates'));
    }

    /**
     * Form edit template PKA.
     */
    public function edit($id)
    {
        $template = ProgramKerja::whereNull('st_id')->findOrFail($id);
        $jenisPenugasans = JenisPenugasan::all();
        return view('template-pka.edit', compact('template', 'jenisPenugasans'));
    }

    /**
     * Update template PKA.
     */
    public function update(Request $request, $id)
    {
        $template = ProgramKerja::whereNull('st_id')->findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'status' => 'nullable|in:draft,published',
        ]);

        $template->update($request->only(['judul', 'deskripsi', 'tujuan', 'ruang_lingkup', 'metodologi', 'status']));

        return redirect()->route('template-pka.show', $template->id)
            ->with('success', 'Template diperbarui.');
    }

    /**
     * Hapus template PKA.
     */
    public function destroy($id)
    {
        $template = ProgramKerja::whereNull('st_id')->findOrFail($id);

        // Periksa apakah ada PKA aktif yang di-clone dari template ini
        // (Kita tidak melacak source_template_id, jadi cukup periksa apakah statusnya published)
        if ($template->status === 'published') {
            return back()->with('error', 'Template yang sudah Published tidak bisa dihapus. Ubah ke Draft dulu.');
        }

        $template->langkah()->delete();
        $template->delete();

        return redirect()->route('template-pka.index')
            ->with('success', 'Template PKA dihapus.');
    }

    // ─── Langkah Management (AJAX) ──────────────────────────

    /**
     * Tambah langkah ke template.
     */
    public function storeLangkah(Request $request, $id)
    {
        $template = ProgramKerja::whereNull('st_id')->findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'jenis_prosedur' => 'nullable|string',
            'target_hari' => 'nullable|integer|min:1',
            'parent_id' => 'nullable|exists:pk_langkah,id',
            'kk_template_id' => 'nullable|exists:kk_templates,id',
        ]);

        $maxUrutan = PkLangkah::where('program_kerja_id', $template->id)
            ->where('parent_id', $request->parent_id)
            ->max('urutan') ?? 0;

        $langkah = PkLangkah::create([
            'program_kerja_id' => $template->id,
            'parent_id' => $request->parent_id ?: null,
            'urutan' => $maxUrutan + 1,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'jenis_prosedur' => $request->jenis_prosedur,
            'target_hari' => $request->target_hari,
            'kk_template_id' => $request->kk_template_id,
            'status' => 'pending',
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'langkah' => $langkah]);
        }
        return back()->with('success', 'Langkah ditambahkan.');
    }

    /**
     * Update langkah template.
     */
    public function updateLangkah(Request $request, $id)
    {
        $langkah = PkLangkah::findOrFail($id);

        $data = $request->only(['judul', 'deskripsi', 'jenis_prosedur', 'target_hari']);
        $langkah->update(array_filter($data, fn($v) => $v !== null));

        if ($request->ajax()) {
            return response()->json(['success' => true, 'langkah' => $langkah]);
        }
        return back()->with('success', 'Langkah diperbarui.');
    }

    /**
     * Hapus langkah template.
     */
    public function destroyLangkah(Request $request, $id)
    {
        $langkah = PkLangkah::findOrFail($id);

        // Hapus turunan juga
        PkLangkah::where('parent_id', $langkah->id)->delete();
        $langkah->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Langkah dihapus.');
    }
}
