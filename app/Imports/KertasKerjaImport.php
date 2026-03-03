<?php

namespace App\Imports;

use App\Models\KkAnswer;
use App\Models\TemplateIndicator;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class KertasKerjaImport implements ToCollection, WithHeadingRow
{
    protected $kk_id;

    public function __construct($kk_id)
    {
        $this->kk_id = $kk_id;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $id = $row['id'];
            $nilai = $row['nilaiskor'];
            $catatan = $row['catatan'];
            $link = $row['link_bukti'];

            if (!$id) continue;

            // Check if this ID is a parameter (level 3)
            $indicator = TemplateIndicator::find($id);
            if ($indicator && $indicator->children->count() == 0 && $indicator->parent_id != null) {
                // Update or create answer
                KkAnswer::updateOrCreate(
                    ['kertas_kerja_id' => $this->kk_id, 'indikator_id' => $id],
                    [
                        'nilai' => (float)$nilai,
                        'catatan' => $catatan,
                        'evidence_link' => $link
                    ]
                );
            }
        }
    }
}
