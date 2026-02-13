<?php

namespace App\Http\Controllers;

use App\Models\KkTemplate;
use App\Models\JenisPenugasan;
use App\Models\TemplateIndicator;
use App\Models\TemplateCriteria;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = KkTemplate::with('jenisPenugasan')->latest()->get();
        return view('templates.index', compact('templates'));
    }

    public function create()
    {
        $jenisPenugasans = JenisPenugasan::all();
        return view('templates.create', compact('jenisPenugasans'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'jenis_penugasan_id' => 'required',
            'tahun' => 'required|numeric',
        ]);

        KkTemplate::create($request->all());

        return redirect()->route('templates.index')
            ->with('success', 'Template header created successfully. Now build the indicators!');
    }

    public function builder(KkTemplate $template)
    {
        $indicators = TemplateIndicator::where('template_id', $template->id)
            ->whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('id')->with(['criteria', 'children' => function($q2) {
                    $q2->orderBy('id')->with('criteria');
                }]);
            }])
            ->orderBy('id')
            ->get();

        return view('templates.builder', compact('template', 'indicators'));
    }

    public function edit(KkTemplate $template)
    {
        $jenisPenugasans = JenisPenugasan::all();
        return view('templates.edit', compact('template', 'jenisPenugasans'));
    }

    public function update(Request $request, KkTemplate $template)
    {
        $request->validate([
            'nama' => 'required',
            'jenis_penugasan_id' => 'required',
            'tahun' => 'required|numeric',
        ]);

        $template->update($request->all());

        return redirect()->route('templates.index')->with('success', 'Template updated successfully.');
    }

    public function destroy(KkTemplate $template)
    {
        $template->delete();
        return redirect()->route('templates.index')->with('success', 'Template deleted successfully.');
    }

    // Indicator Management
    public function storeIndicator(Request $request, KkTemplate $template)
    {
        $request->validate([
            'uraian' => 'required',
            'bobot' => 'nullable|numeric',
        ]);

        try {
            TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => !empty($request->parent_id) ? $request->parent_id : null,
                'uraian' => $request->uraian,
                'bobot' => is_numeric($request->bobot) ? $request->bobot : 0,
                'tipe' => !empty($request->tipe) ? $request->tipe : 'score_manual',
            ]);

            return back()->with('success', 'Indicator/Parameter added.');
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error saving data: ' . $e->getMessage()])->withInput();
        }
    }

    public function destroyIndicator(TemplateIndicator $indicator)
    {
        $indicator->delete();
        return back()->with('success', 'Indicator removed.');
    }

    // Criteria Management
    public function storeCriteria(Request $request, TemplateIndicator $indicator)
    {
        $request->validate([
            'uraian' => 'required',
        ]);

        TemplateCriteria::create([
            'indicator_id' => $indicator->id,
            'uraian' => $request->uraian,
        ]);

        return back()->with('success', 'Criteria added.');
    }

    public function destroyCriteria(TemplateCriteria $criteria)
    {
        $criteria->delete();
        return back()->with('success', 'Criteria removed.');
    }
}
