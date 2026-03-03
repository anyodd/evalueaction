<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TemplateIndicator;
use App\Models\TemplateLangkah;

class AIGeneratedLangkahSeeder extends Seeder
{
    public function run()
    {
        $parameters = TemplateIndicator::whereNotNull('parent_id')
            ->where('tipe', '!=', 'header') // Just to be safe if 'header' exists, though parent_id usually indicates parameters
            ->get();

        $count = 0;
        foreach ($parameters as $param) {
            // Check if it already has criteria/langkah or is a leaf node
            $hasChildren = TemplateIndicator::where('parent_id', $param->id)->exists();
            if ($hasChildren) continue; // Only process parameters, not indicators

            // Delete old langkahs to avoid duplicates when running multiple times
            TemplateLangkah::where('indicator_id', $param->id)->delete();

            $uraian = strtolower($param->uraian);
            $langkahList = $this->generateStepsForParameter($param->uraian);

            foreach ($langkahList as $step) {
                TemplateLangkah::create([
                    'indicator_id' => $param->id,
                    'uraian' => $step['uraian'],
                    'jenis_prosedur' => $step['jenis']
                ]);
            }
            $count++;
        }

        echo "Seeded langkah kerja for $count parameters.\n";
    }

    private function generateStepsForParameter($text)
    {
        $lower = strtolower($text);
        
        // General Steps for almost any audit parameter
        $steps = [
            ['uraian' => "Dapatkan dan pelajari dokumen kebijakan/SOP/pedoman terkait {$text}.", 'jenis' => 'inspeksi_dokumen'],
            ['uraian' => "Lakukan wawancara dengan auditee/pic terkait untuk memahami proses pelaksanaan {$text}, kendala yang dihadapi, serta tindak lanjutnya.", 'jenis' => 'wawancara'],
        ];

        if (str_contains($lower, 'risiko') || str_contains($lower, 'risk')) {
            $steps = array_merge($steps, [
                ['uraian' => "Mintakan register risiko dan profil risiko dari unit manajemen risiko (MR).", 'jenis' => 'inspeksi_dokumen'],
                ['uraian' => "Lakukan evaluasi atas identifikasi, analisis, dan mitigasi risiko yang telah disusun.", 'jenis' => 'analisis_data'],
                ['uraian' => "Bandingkan rencana mitigasi dengan realisasi tindakan pada periode berjalan.", 'jenis' => 'analisis_data'],
                ['uraian' => "Simpulkan apakah pengelolaan risiko atas {$text} telah memadai dan berjalan efektif.", 'jenis' => 'analisis_data']
            ]);
        } elseif (str_contains($lower, 'keuangan') || str_contains($lower, 'pendapatan') || str_contains($lower, 'laporan')) {
            $steps = array_merge($steps, [
                ['uraian' => "Dapatkan laporan keuangan/rekapitulasi terkait komponen {$text}.", 'jenis' => 'inspeksi_dokumen'],
                ['uraian' => "Lakukan rekalkulasi (perhitungan ulang) terhadap nilai yang disajikan pada laporan untuk memastikan akurasi matematis.", 'jenis' => 'rekalkulasi'],
                ['uraian' => "Lakukan uji petik (sampling) dokumen sumber untuk memastikan validitas transaksi pencatatan.", 'jenis' => 'inspeksi_dokumen'],
                ['uraian' => "Bandingkan saldo pada mutasi rekening bank dengan pembukuan (rekonsiliasi).", 'jenis' => 'analisis_data'],
                ['uraian' => "Buat kesimpulan kesesuaian nilai {$text} dengan standar akuntansi/aturan yang berlaku.", 'jenis' => 'analisis_data']
            ]);
        } elseif (str_contains($lower, 'pengaduan') || str_contains($lower, 'whistleblowing')) {
            $steps = array_merge($steps, [
                ['uraian' => "Pastikan terdapat saluran pengaduan (kanal komunikasi) yang dapat diakses oleh stakeholders secara aktif.", 'jenis' => 'observasi'],
                ['uraian' => "Dapatkan rekap data jumlah aduan yang masuk, diproses, dan yang diselesaikan pada tahun berjalan.", 'jenis' => 'inspeksi_dokumen'],
                ['uraian' => "Lakukan wawancara dengan pengelola saluran pengaduan mengenai proses penanganan laporan dari awal hingga penutupan kasus.", 'jenis' => 'wawancara'],
                ['uraian' => "Uji petik beberapa kasus pengaduan untuk memastikan SLA penyelesaian telah terpenuhi dan kerahasiaan pelapor terjaga.", 'jenis' => 'analisis_data'],
                ['uraian' => "Simpulkan efektivitas sistem pengaduan yang ada.", 'jenis' => 'analisis_data']
            ]);
        } elseif (str_contains($lower, 'dewan pengawas') || str_contains($lower, 'organisasi') || str_contains($lower, 'kemitraan')) {
            $steps = array_merge($steps, [
                ['uraian' => "Dapatkan struktur organisasi, SK pembentukan, atau nota kesepahaman (MoU) terkait.", 'jenis' => 'inspeksi_dokumen'],
                ['uraian' => "Dapatkan notulensi rapat berkala untuk menguji keterlibatan aktif pihak terkait (Dewan Pengawas/Mitra).", 'jenis' => 'inspeksi_dokumen'],
                ['uraian' => "Evaluasi pemenuhan hak dan kewajiban masing-masing pihak berdasarkan ketentuan perundangan atau perjanjian.", 'jenis' => 'analisis_data'],
                ['uraian' => "Wawancarai pihak terkait untuk mengonfirmasi manfaat dan efektivitas hubungan yang terjalin.", 'jenis' => 'konfirmasi'],
                ['uraian' => "Simpulkan peran strategis serta ketaatan operasional {$text}.", 'jenis' => 'analisis_data']
            ]);
        } else {
            // Generic 5 steps for anything else
            $steps = array_merge($steps, [
                ['uraian' => "Dapatkan bukti/dokumen pelaksanaan tahunan yang relevan.", 'jenis' => 'inspeksi_dokumen'],
                ['uraian' => "Lakukan observasi dan konfirmasi untuk memvalidasi kelengkapan serta keandalan bukti dukung yang disampaikan responden.", 'jenis' => 'observasi'],
                ['uraian' => "Bandingkan praktik di lapangan dengan standar teknis, prosedur yang ditetapkan, atau target renstra/RBA.", 'jenis' => 'analisis_data'],
                ['uraian' => "Evaluasi permasalahan atau gap (kesenjangan) yang mengakibatkan target tidak/belum tercapai sepenuhnya.", 'jenis' => 'analisis_data'],
                ['uraian' => "Formulasikan kesimpulan, akibat, sebab, serta saran manajerial yang dapat diusulkan atas {$text}.", 'jenis' => 'analisis_data']
            ]);
        }

        return $steps;
    }
}
