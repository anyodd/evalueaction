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

            // 2. Create Template Header
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

            // 3.1.1 Indikator: Kualitas Perencanaan
            $indKualitas = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => $aspekPerencanaan->id,
                'uraian' => 'Kualitas Perencanaan',
                'tipe' => 'header', // Grouping
                'bobot' => 40.00, // Bobot Indikator terhadap Aspek
            ]);

            // 3.1.1.1 Parameter: Keselarasan Sasaran
            $param1 = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => $indKualitas->id,
                'uraian' => 'Adanya keterkaitan antara Sasaran BLU/BLUD dengan Sasaran Strategis K/L/Pemda',
                'tipe' => 'criteria_tally', // Scoring Item
                'bobot' => 25.00, // Bobot Parameter terhadap Indikator
            ]);

            // Criteria for Parameter 1
            $this->addCriteria($param1->id, [
                'Terdapat dokumen rencana strategis (Renstra) yang sah (Skor: 1.0)',
                'Terdapat keselarasan visi misi dengan K/L/Pemda (Skor: 1.0)',
                'Terdapat indikator kinerja utama yang terukur (Skor: 1.0)',
            ]);

             // 3.1.1.2 Parameter: Penetapan Sasaran
             $param2 = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => $indKualitas->id,
                'uraian' => 'Penetapan Sasaran Strategis sudah tepat',
                'tipe' => 'criteria_tally',
                'bobot' => 25.00,
            ]);
            $this->addCriteria($param2->id, ['Sasaran ditetapkan dengan metode SMART', 'Dokumentasi penetapan sasaran lengkap']);

             // 3.1.1.3 Parameter: Indikator Kinerja
             $param3 = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => $indKualitas->id,
                'uraian' => 'Penetapan Indikator Kinerja sudah tepat',
                'tipe' => 'criteria_tally',
                'bobot' => 25.00,
            ]);
            $this->addCriteria($param3->id, ['Indikator relevan dengan tujuan', 'Indikator dapat dicapai']);

             // 3.1.1.4 Parameter: Target Kinerja
             $param4 = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => $indKualitas->id,
                'uraian' => 'Penetapan Target Kinerja menantang namun realistis',
                'tipe' => 'criteria_tally',
                'bobot' => 25.00,
            ]);
            $this->addCriteria($param4->id, ['Target didasarkan pada data historis', 'Target disepakati pimpinan']);


            // 3.2 Aspek Kapabilitas (30%)
            $aspekKapabilitas = TemplateIndicator::create([
                'template_id' => $template->id,
                'uraian' => 'KAPABILITAS',
                'tipe' => 'header',
                'bobot' => 30.00,
            ]);
            
            // Indikator Kapabilitas
            $indKepemimpinan = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => $aspekKapabilitas->id,
                'uraian' => 'Kepemimpinan dan Budaya Risiko',
                'tipe' => 'header',
                'bobot' => 100.00,
            ]);
            
            $paramKap1 = TemplateIndicator::create([
                'template_id' => $template->id,
                'parent_id' => $indKepemimpinan->id,
                'uraian' => 'Komitmen Pimpinan terhadap Manajemen Risiko',
                'tipe' => 'criteria_tally',
                'bobot' => 100.00,
            ]);
            $this->addCriteria($paramKap1->id, ['Terdapat kebijakan MR', 'Pimpinan menjadi role model']);


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
