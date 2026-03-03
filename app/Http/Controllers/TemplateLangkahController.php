<?php

namespace App\Http\Controllers;

use App\Models\TemplateLangkah;
use App\Models\TemplateIndicator;
use Illuminate\Http\Request;

class TemplateLangkahController extends Controller
{
    public function store(Request $request, TemplateIndicator $indicator)
    {
        $request->validate([
            'uraian' => 'required|string',
            'jenis_prosedur' => 'nullable|string',
        ]);

        $langkah = TemplateLangkah::create([
            'indicator_id' => $indicator->id,
            'uraian' => $request->uraian,
            'jenis_prosedur' => $request->jenis_prosedur,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Langkah kerja ditambahkan.',
                'langkah' => $langkah
            ]);
        }
        
        return back()->with('success', 'Langkah kerja ditambahkan.');
    }

    public function update(Request $request, TemplateLangkah $langkah)
    {
        $request->validate([
            'uraian' => 'nullable|string',
            'jenis_prosedur' => 'nullable|string',
        ]);

        $data = [];
        if ($request->has('uraian')) $data['uraian'] = $request->uraian;
        if ($request->has('jenis_prosedur')) $data['jenis_prosedur'] = $request->jenis_prosedur;

        $langkah->update($data);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Langkah kerja diupdate.',
                'langkah' => $langkah
            ]);
        }
        
        return back()->with('success', 'Langkah kerja diupdate.');
    }

    public function destroy(Request $request, TemplateLangkah $langkah)
    {
        $langkah->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Langkah kerja dihapus.']);
        }
        
        return back()->with('success', 'Langkah kerja dihapus.');
    }
}
