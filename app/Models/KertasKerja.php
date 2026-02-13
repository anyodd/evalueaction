<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KertasKerja extends Model
{
    use HasFactory;

    protected $table = 'kertas_kerja';

    protected $fillable = [
        'st_id',
        'template_id',
        'user_id',
        'judul_kk',
        'isi_kk',
        'status_approval',
        'nilai_akhir',
        'nilai_akhir_qa',
        'file_pendukung',
    ];

    public function template()
    {
        return $this->belongsTo(KkTemplate::class, 'template_id');
    }

    public function answers()
    {
        return $this->hasMany(KkAnswer::class, 'kertas_kerja_id');
    }

    public function suratTugas()
    {
        return $this->belongsTo(SuratTugas::class, 'st_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviewNotes()
    {
        return $this->hasMany(ReviewNote::class, 'kk_id');
    }
}
