<?php

namespace App\Exports;

use App\Models\KertasKerja;
use App\Models\TemplateIndicator;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class KertasKerjaExport implements FromView, WithTitle, ShouldAutoSize
{
    protected $kk_id;

    public function __construct($kk_id)
    {
        $this->kk_id = $kk_id;
    }

    public function view(): View
    {
        $kertasKerja = KertasKerja::with(['template', 'answers.details', 'suratTugas.jenisPenugasan'])->findOrFail($this->kk_id);
        
        // Organize indicators hierarchy
        $indicators = TemplateIndicator::where('template_id', $kertasKerja->template_id)
            ->whereNull('parent_id')
            ->with(['children' => function($q) {
                $q->orderBy('id')->with(['children' => function($q2) {
                    $q2->orderBy('id')->with(['criteria']);
                }]);
            }])
            ->orderBy('id')
            ->get();

        return view('kertas-kerja.export-excel', [
            'kertasKerja' => $kertasKerja,
            'indicators' => $indicators
        ]);
    }

    public function title(): string
    {
        return 'Kertas Kerja';
    }
}
