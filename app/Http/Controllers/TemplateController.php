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
        $templates = KkTemplate::with('jenisPenugasan')
            ->withCount('indicators')
            ->latest()
            ->get();

        // Count criteria and KK usage for each template
        foreach ($templates as $t) {
            $t->criteria_count = TemplateCriteria::whereHas('indicator', function ($q) use ($t) {
                $q->where('template_id', $t->id);
            })->count();
            $t->kk_count = \App\Models\KertasKerja::where('template_id', $t->id)->count();
        }

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
                $q->orderBy('id')->with(['criteria' => function($qc) {
                    $qc->orderBy('level')->orderBy('id');
                }, 'children' => function($q2) {
                    $q2->orderBy('id')->with(['criteria' => function($qc2) {
                        $qc2->orderBy('level')->orderBy('id');
                    }, 'langkahs' => function($ql) {
                        $ql->orderBy('id');
                    }]);
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
        // Prevent deletion if template is used by any KK
        $kkCount = \App\Models\KertasKerja::where('template_id', $template->id)->count();
        if ($kkCount > 0) {
            return back()->with('error', "Template tidak bisa dihapus karena sedang digunakan oleh {$kkCount} Kertas Kerja.");
        }

        $template->delete();
        return redirect()->route('templates.index')->with('success', 'Template deleted successfully.');
    }

    // ─── Indicator Management ────────────────────────────────

    public function storeIndicator(Request $request, KkTemplate $template)
    {
        $request->validate([
            'uraian' => 'required',
            'bobot' => 'nullable|numeric',
        ]);

        try {
            $indicator = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => !empty($request->parent_id) ? $request->parent_id : null,
                'uraian' => $request->uraian,
                'bobot' => is_numeric($request->bobot) ? $request->bobot : 0,
                'tipe' => !empty($request->tipe) ? $request->tipe : 'score_manual',
            ]);

            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Indicator added.',
                    'indicator' => $indicator,
                ]);
            }

            return back()->with('success', 'Indicator/Parameter added.');
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->withErrors(['message' => 'Error saving data: ' . $e->getMessage()])->withInput();
        }
    }

    public function updateIndicator(Request $request, TemplateIndicator $indicator)
    {
        $request->validate([
            'uraian' => 'nullable|string',
            'bobot' => 'nullable|numeric',
        ]);

        $data = [];
        if ($request->has('uraian')) $data['uraian'] = $request->uraian;
        if ($request->has('bobot')) $data['bobot'] = $request->bobot;
        
        $indicator->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Updated.', 'indicator' => $indicator]);
        }
        return back()->with('success', 'Indicator updated.');
    }

    public function destroyIndicator(Request $request, TemplateIndicator $indicator)
    {
        $indicator->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Indicator removed.']);
        }
        return back()->with('success', 'Indicator removed.');
    }

    // ─── Criteria Management ─────────────────────────────────

    public function storeCriteria(Request $request, TemplateIndicator $indicator)
    {
        $request->validate([
            'uraian' => 'required',
            'level' => 'nullable|integer|min:1|max:5',
        ]);

        $criteria = TemplateCriteria::create([
            'indicator_id' => $indicator->id,
            'uraian' => $request->uraian,
            'level' => $request->level ?? 1,
        ]);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Criteria added.', 'criteria' => $criteria]);
        }
        return back()->with('success', 'Criteria added.');
    }

    public function updateCriteria(Request $request, TemplateCriteria $criteria)
    {
        $request->validate([
            'uraian' => 'nullable|string',
            'level' => 'nullable|integer|min:1|max:5',
        ]);

        $data = [];
        if ($request->has('uraian')) $data['uraian'] = $request->uraian;
        if ($request->has('level')) $data['level'] = $request->level;

        $criteria->update($data);

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Updated.', 'criteria' => $criteria]);
        }
        return back()->with('success', 'Criteria updated.');
    }

    public function destroyCriteria(Request $request, TemplateCriteria $criteria)
    {
        $criteria->delete();

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Criteria removed.']);
        }
        return back()->with('success', 'Criteria removed.');
    }

    // ─── Clone Template ──────────────────────────────────────

    public function cloneTemplate(KkTemplate $template)
    {
        $newTemplate = $template->replicate();
        $newTemplate->nama = $template->nama . ' (Copy)';
        $newTemplate->is_active = false;
        $newTemplate->save();

        // Clone root indicators → children → grandchildren → criteria & langkahs
        $roots = TemplateIndicator::where('template_id', $template->id)
            ->whereNull('parent_id')
            ->with(['children.children.criteria', 'children.children.langkahs', 'children.criteria', 'children.langkahs', 'criteria', 'langkahs'])
            ->get();

        foreach ($roots as $root) {
            $this->cloneIndicatorTree($root, $newTemplate->id, null);
        }

        return redirect()->route('templates.builder', $newTemplate)
            ->with('success', 'Template "' . $template->nama . '" berhasil di-clone!');
    }

    private function cloneIndicatorTree(TemplateIndicator $source, $newTemplateId, $newParentId)
    {
        $newInd = $source->replicate();
        $newInd->template_id = $newTemplateId;
        $newInd->parent_id = $newParentId;
        $newInd->save();

        // Clone criteria
        foreach ($source->criteria as $criteria) {
            $newC = $criteria->replicate();
            $newC->indicator_id = $newInd->id;
            $newC->save();
        }

        // Clone langkahs
        foreach ($source->langkahs as $langkah) {
            $newL = $langkah->replicate();
            $newL->indicator_id = $newInd->id;
            $newL->save();
        }

        // Clone children recursively
        foreach ($source->children as $child) {
            $this->cloneIndicatorTree($child, $newTemplateId, $newInd->id);
        }
    }

    // ─── Preview Template ────────────────────────────────────

    public function preview(KkTemplate $template)
    {
        $indicators = TemplateIndicator::where('template_id', $template->id)
            ->whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('id')->with(['criteria' => function($qc) {
                    $qc->orderBy('level')->orderBy('id');
                }, 'children' => function($q2) {
                    $q2->orderBy('id')->with(['criteria' => function($qc2) {
                        $qc2->orderBy('level')->orderBy('id');
                    }, 'langkahs' => function($ql) {
                        $ql->orderBy('id');
                    }]);
                }]);
            }])
            ->orderBy('id')
            ->get();

        return view('templates.preview', compact('template', 'indicators'));
    }

    // ─── Bobot Summary (AJAX) ────────────────────────────────

    public function bobotSummary(KkTemplate $template)
    {
        $aspects = TemplateIndicator::where('template_id', $template->id)
            ->whereNull('parent_id')
            ->with('children.children')
            ->get();

        $summary = [];
        $totalAspectBobot = 0;

        foreach ($aspects as $aspect) {
            $indTotal = $aspect->children->sum('bobot');
            $params = [];
            foreach ($aspect->children as $ind) {
                $paramTotal = $ind->children->sum('bobot');
                $params[] = [
                    'id' => $ind->id,
                    'uraian' => $ind->uraian,
                    'bobot' => $ind->bobot,
                    'children_bobot_sum' => $paramTotal,
                ];
            }

            $summary[] = [
                'id' => $aspect->id,
                'uraian' => $aspect->uraian,
                'bobot' => $aspect->bobot,
                'children_bobot_sum' => $indTotal,
                'children' => $params,
            ];
            $totalAspectBobot += $aspect->bobot;
        }

        return response()->json([
            'success' => true,
            'total_bobot' => $totalAspectBobot,
            'aspects' => $summary,
        ]);
    }
}
