<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkTeo extends Model
{
    use HasFactory;

    protected $fillable = ['kk_answer_id', 'teo', 'template_teo_id'];

    public function answer()
    {
        return $this->belongsTo(KkAnswer::class, 'kk_answer_id');
    }

    public function findings()
    {
        return $this->hasMany(KkFinding::class, 'kk_teo_id');
    }
}
