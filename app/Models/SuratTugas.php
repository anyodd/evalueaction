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
        'admin_id',
        'perwakilan_id',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
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
