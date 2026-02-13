<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratTugas extends Model
{
    use HasFactory;

    protected $table = 'surat_tugas';
    
    protected $fillable = [
        'nomor_st',
        'tgl_st',
        'nama_objek',
        'tahun_evaluasi',
        'jenis_penugasan_id',
        'template_id',
        'admin_id',
        'perwakilan_id',
        'tgl_mulai',
        'tgl_selesai',
    ];

    protected $casts = [
        'tgl_st' => 'date',
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function jenisPenugasan()
    {
        return $this->belongsTo(JenisPenugasan::class, 'jenis_penugasan_id');
    }

    public function template()
    {
        return $this->belongsTo(KkTemplate::class, 'template_id');
    }

    public function perwakilan()
    {
        return $this->belongsTo(Perwakilan::class);
    }

    public function personel()
    {
        return $this->hasMany(StPersonel::class, 'st_id');
    }

    public function kertasKerja()
    {
        return $this->hasMany(KertasKerja::class, 'st_id');
    }
}
