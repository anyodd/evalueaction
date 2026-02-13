<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\JenisPenugasan;
use App\Models\KkTemplate;
use App\Models\TemplateIndicator;

class RiskManagementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::transaction(function () {
            // 1. Get or Create Jenis Penugasan "Manajemen Risiko"
            $jenisMr = JenisPenugasan::firstOrCreate(
                ['kode' => 'MR'],
                ['nama_penugasan' => 'Penilaian Maturitas Manajemen Risiko']
            );

            // 2. Create Template Header for current year
            $template = KkTemplate::create([
                'jenis_penugasan_id' => $jenisMr->id,
                'nama_template' => 'Kertas Kerja Maturitas MR 2026',
                'tahun' => 2026,
                'is_active' => true,
            ]);

            // 3. Level 1: ASPEK
            
            // 3.1 Aspek Perencanaan (40%)
            $aspekPerencanaan = TemplateIndicator::create([
                'template_id' => $template->id,
                'uraian' => 'PERENCANAAN',
                'tipe' => 'header',
                'bobot' => 40.00,
            ]);

            // 3.1.1 Indikator: Kualitas Perencanaan (Child of Aspek Perencanaan)
            // Note: In user's description, "Kualitas Perencanaan" is an Indicator under Aspect.
            // And parameters are under this Indicator.
            $indKualitas = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => $aspekPerencanaan->id,
                'uraian' => 'Kualitas Perencanaan',
                'tipe' => 'header', // Grouping for parameters
                'bobot' => 40.00, // Bobot Indikator thd Aspek (example from user image column 3 isn't clear if it's Aspect or Indicator weight, assuming Indicator)
            ]);

            // 3.1.1.1 Parameter 1: Adanya keterkaitan...
            $param1 = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => $indKualitas->id,
                'uraian' => 'Adanya keterkaitan antara Sasaran BLU/BLUD dengan Sasaran Strategis K/L/Pemda',
                'tipe' => 'criteria_tally', // New type for checklist based scoring
                'bobot' => 30.00, // Bobot Parameter thd Indikator
            ]);

            // Criteria for Parameter 1
            $this->addCriteria($param1->id, [
                'Keselarasan sasaran BLU/BLUD dengan arah kebijakan pembangunan nasional...',
                'Keselarasan sasaran BLU/BLUD dengan arah kebijakan pembangunan nasional... (Point 2)',
                'Keselarasan sasaran BLU/BLUD dengan arah kebijakan pembangunan nasional... (Point 3)',
                'Keselarasan sasaran BLU/BLUD dengan arah kebijakan pembangunan nasional... (Point 4)',
                'Keselarasan sasaran BLU/BLUD dengan arah kebijakan pembangunan nasional... (Point 5)',
            ]);

             // 3.1.1.2 Parameter 2: Penetapan Sasaran Strategis...
             $param2 = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => $indKualitas->id,
                'uraian' => 'Penetapan Sasaran Strategis sudah tepat',
                'tipe' => 'criteria_tally',
                'bobot' => 30.00,
            ]);
            $this->addCriteria($param2->id, ['Kriteria 1', 'Kriteria 2', 'Kriteria 3', 'Kriteria 4', 'Kriteria 5']);

             // 3.1.1.3 Parameter 3: Penetapan Indikator Kinerja...
             $param3 = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => $indKualitas->id,
                'uraian' => 'Penetapan Indikator Kinerja sudah tepat dan...',
                'tipe' => 'criteria_tally',
                'bobot' => 20.00,
            ]);
            $this->addCriteria($param3->id, ['Kriteria 1', 'Kriteria 2', 'Kriteria 3', 'Kriteria 4', 'Kriteria 5']);

             // 3.1.1.4 Parameter 4: Penetapan Target Kinerja...
             $param4 = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => $indKualitas->id,
                'uraian' => 'Penetapan Target Kinerja sudah baik',
                'tipe' => 'criteria_tally',
                'bobot' => 20.00,
            ]);
            $this->addCriteria($param4->id, ['Kriteria 1', 'Kriteria 2', 'Kriteria 3', 'Kriteria 4', 'Kriteria 5']);


            // 3.2 Aspek Kapabilitas (30%)
            TemplateIndicator::create([
                'template_id' => $template->id,
                'uraian' => 'KAPABILITAS',
                'tipe' => 'header',
                'bobot' => 30.00,
            ]);

            // 3.3 Aspek Hasil (30%)
            TemplateIndicator::create([
                'template_id' => $template->id,
                'uraian' => 'HASIL',
                'tipe' => 'header',
                'bobot' => 30.00,
            ]);

        });
    }

    private function addCriteria($indicatorId, $criteriaList)
    {
        foreach ($criteriaList as $desc) {
            DB::table('template_criteria')->insert([
                'indicator_id' => $indicatorId,
                'uraian' => $desc,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
