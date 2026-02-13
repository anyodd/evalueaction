<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JenisPenugasan;
use App\Models\KkTemplate;
use App\Models\TemplateIndicator;

class JenisPenugasanSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Jenis Penugasan
        $mr = JenisPenugasan::create([
            'nama' => 'Evaluasi Manajemen Risiko',
            'kode' => 'MR'
        ]);

        $akuntabilitas = JenisPenugasan::create([
            'nama' => 'Evaluasi Akuntabilitas Kinerja',
            'kode' => 'AKUNTABILITAS'
        ]);

        $kinerja = JenisPenugasan::create([
            'nama' => 'Evaluasi Kinerja BLU/BLUD',
            'kode' => 'KINERJA'
        ]);

        // 2. Create Template MR 2026
        $mrTemplate = KkTemplate::create([
            'jenis_penugasan_id' => $mr->id,
            'nama' => 'Template Evaluasi MR 2026',
            'tahun' => 2026,
            'is_active' => true
        ]);

        // Indicators for MR
        $dimensiLingkungan = TemplateIndicator::create([
            'template_id' => $mrTemplate->id,
            'uraian' => 'Lingkungan Pengendalian',
            'tipe' => 'header',
            'bobot' => 0
        ]);

        TemplateIndicator::create([
            'template_id' => $mrTemplate->id,
            'parent_id' => $dimensiLingkungan->id,
            'uraian' => 'Apakah pimpinan telah membangun budaya sadar risiko?',
            'tipe' => 'score_manual',
            'bobot' => 20
        ]);

        TemplateIndicator::create([
            'template_id' => $mrTemplate->id,
            'parent_id' => $dimensiLingkungan->id,
            'uraian' => 'Apakah struktur organisasi mendukung pengelolaan risiko?',
            'tipe' => 'score_manual',
            'bobot' => 10
        ]);
        
        // 3. Create Template Akuntabilitas 2026 (With Reference)
        $akuntabilitasTemplate = KkTemplate::create([
            'jenis_penugasan_id' => $akuntabilitas->id,
            'nama' => 'Template Evaluasi Akuntabilitas 2026',
            'tahun' => 2026,
            'is_active' => true
        ]);

        // Dimension A: Perencanaan
        $dimA = TemplateIndicator::create([
            'template_id' => $akuntabilitasTemplate->id,
            'uraian' => 'Komponen Perencanaan Kinerja',
            'tipe' => 'header',
            'bobot' => 0
        ]);

        TemplateIndicator::create([
            'template_id' => $akuntabilitasTemplate->id,
            'parent_id' => $dimA->id,
            'uraian' => 'Kualitas Dokumen Perencanaan',
            'tipe' => 'score_manual',
            'bobot' => 30
        ]);

        // Dimension B: MR (Reference)
        $dimB = TemplateIndicator::create([
            'template_id' => $akuntabilitasTemplate->id,
            'uraian' => 'Komponen Manajemen Risiko',
            'tipe' => 'header',
            'bobot' => 0
        ]);

        TemplateIndicator::create([
            'template_id' => $akuntabilitasTemplate->id,
            'parent_id' => $dimB->id,
            'uraian' => 'Nilai Kualitas Penerapan Manajemen Risiko',
            'tipe' => 'score_reference', // This pulls from MR
            'bobot' => 15,
            'ref_jenis_id' => $mr->id // References "Evaluasi MR"
        ]);
    }
}
