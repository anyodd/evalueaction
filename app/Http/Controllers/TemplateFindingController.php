<?php

namespace App\Http\Controllers;

use App\Models\TemplateIndicator;
use App\Models\TemplateTeo;
use App\Models\TemplateCause;
use App\Models\TemplateRecommendation;
use Illuminate\Http\Request;

class TemplateFindingController extends Controller
{
    // --- Operasi TEO ---
    public function storeTeo(Request $request, TemplateIndicator $indicator)
    {
        $request->validate(['teo' => 'required']);

        $teo = TemplateTeo::create([
            'indicator_id' => $indicator->id,
            'teo' => $request->teo,
        ]);

        return response()->json(['success' => true, 'message' => 'TEO ditambahkan.', 'teo' => $teo]);
    }

    public function updateTeo(Request $request, TemplateTeo $teo)
    {
        $request->validate(['teo' => 'required']);
        $teo->update(['teo' => $request->teo]);
        return response()->json(['success' => true, 'message' => 'TEO diupdate.', 'teo' => $teo]);
    }

    public function destroyTeo(TemplateTeo $teo)
    {
        $teo->delete();
        return response()->json(['success' => true, 'message' => 'TEO dihapus.']);
    }

    // --- Operasi Penyebab ---
    public function storeCause(Request $request, TemplateTeo $teo)
    {
        $request->validate(['uraian' => 'required']);
        $cause = $teo->causes()->create(['uraian' => $request->uraian]);

        // Jika rekomendasi spesifik dihubungkan dalam request yang sama (opsional)
        if ($request->recommendation_ids) {
            $cause->recommendations()->sync($request->recommendation_ids);
        }

        return response()->json(['success' => true, 'message' => 'Penyebab ditambahkan.', 'cause' => $cause]);
    }

    public function destroyCause(TemplateCause $cause)
    {
        $cause->delete();
        return response()->json(['success' => true, 'message' => 'Penyebab dihapus.']);
    }

    // --- Operasi Rekomendasi ---
    public function storeRecommendation(Request $request, TemplateTeo $teo)
    {
        $request->validate(['uraian' => 'required']);
        $recommendation = $teo->recommendations()->create(['uraian' => $request->uraian]);

        return response()->json(['success' => true, 'message' => 'Rekomendasi ditambahkan.', 'recommendation' => $recommendation]);
    }

    public function destroyRecommendation(TemplateRecommendation $recommendation)
    {
        $recommendation->delete();
        return response()->json(['success' => true, 'message' => 'Rekomendasi dihapus.']);
    }

    // --- Operasi Relasi ---
    public function syncCauseRecommendations(Request $request, TemplateCause $cause)
    {
        $cause->recommendations()->sync($request->recommendation_ids ?? []);
        return response()->json(['success' => true, 'message' => 'Kaitan Rekomendasi diperbarui.']);
    }
}
