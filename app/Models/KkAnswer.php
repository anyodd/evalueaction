<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkAnswer extends Model
{
    protected $table = 'kk_answers';
    protected $fillable = ['kertas_kerja_id', 'indikator_id', 'nilai', 'catatan', 'ref_st_id'];

    public function kertasKerja()
    {
        return $this->belongsTo(KertasKerja::class);
    }

    public function indicator()
    {
        return $this->belongsTo(TemplateIndicator::class, 'indikator_id');
    }

    public function details()
    {
        return $this->hasMany(KkAnswerDetail::class, 'kk_answer_id');
    }
}
