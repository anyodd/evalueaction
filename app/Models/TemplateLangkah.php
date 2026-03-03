<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateLangkah extends Model
{
    use HasFactory;

    protected $fillable = ['indicator_id', 'uraian', 'jenis_prosedur'];

    public function indicator()
    {
        return $this->belongsTo(TemplateIndicator::class, 'indicator_id');
    }

    public function getJenisProsedurLabelAttribute()
    {
        $map = [
            'wawancara' => 'Wawancara',
            'observasi' => 'Observasi',
            'inspeksi_dokumen' => 'Inspeksi Dokumen',
            'analisis_data' => 'Analisis Data',
            'konfirmasi' => 'Konfirmasi',
            'rekalkulasi' => 'Rekalkulasi',
            'lainnya' => 'Lainnya',
        ];

        return $map[$this->jenis_prosedur] ?? 'Lainnya';
    }
}
