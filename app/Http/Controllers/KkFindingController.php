<?php

namespace App\Http\Controllers;

use App\Models\KkAnswer;
use App\Models\KkFinding;
use App\Models\KkTeo;
use App\Models\TemplateTeo;
use Illuminate\Http\Request;

class KkFindingController extends Controller
{
    public function storeTeo(Request $request)
    {
        $request->validate([
            'indicator_id' => 'required|exists:template_indicators,id',
            'teo' => 'required'
        ]);

        // Temukan atau buat KK Answer untuk indikator ini
        // Kita butuh ID Kertas Kerja. Bisa dikirim atau disimpulkan jika kita punya konteks indikator + auth, 
        // tapi untuk amannya kita asumsikan ID tersebut dikirim atau kita cari Answer yang tepat.
        // Di storeTeo kita mungkin juga butuh kk_id jika answer belum ada.
        
        $kkId = $request->kk_id; 
        if (!$kkId) {
            // Coba cari dari indicator -> template -> kk (tapi satu template bisa memiliki banyak KK)
            // Jadi kk_id lebih aman. Mari kita cek apakah ada di JS. 
            // Ya, data-kk ada di btn-manual-teo.
        }

        $answer = KkAnswer::firstOrCreate([
            'kertas_kerja_id' => $kkId,
            'indikator_id' => $request->indicator_id
        ]);

        $teo = KkTeo::create([
            'kk_answer_id' => $answer->id,
            'teo' => $request->teo,
            'template_teo_id' => $request->template_teo_id ?? null
        ]);

        return response()->json([
            'success' => true,
            'message' => 'TEO berhasil ditambahkan.',
            'teo' => $teo
        ]);
    }

    public function destroyTeo(KkTeo $teo)
    {
        $teo->delete(); // Hapus beruntun temuan-temuan terkait jika diatur di migration, atau hapus secara manual
        return response()->json(['success' => true, 'message' => 'TEO dihapus.']);
    }

    public function getTeoTemplateData(KkTeo $teo)
    {
        if (!$teo->template_teo_id) {
            return response()->json(['success' => false, 'message' => 'TEO ini tidak terhubung ke standar.']);
        }

        $templateTeo = TemplateTeo::with('causes.recommendations')->find($teo->template_teo_id);

        return response()->json([
            'success' => true,
            'data' => $templateTeo
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'kk_teo_id' => 'required|exists:kk_teos,id',
            'penyebab' => 'required',
            'rekomendasi' => 'required'
        ]);

        $finding = KkFinding::create([
            'kk_teo_id' => $request->kk_teo_id,
            'user_id' => auth()->id(),
            'cause' => $request->penyebab,
            'recommendation' => $request->rekomendasi
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Temuan berhasil ditambahkan.',
            'finding' => $finding
        ]);
    }

    public function destroy(KkFinding $finding)
    {
        $finding->delete();
        return response()->json(['success' => true, 'message' => 'Temuan dihapus.']);
    }
}
