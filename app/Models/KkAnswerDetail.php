<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkAnswerDetail extends Model
{
    use HasFactory;

    protected $table = 'kk_answer_details';
    protected $fillable = ['kk_answer_id', 'criteria_id', 'is_checked'];

    public function answer()
    {
        return $this->belongsTo(KkAnswer::class, 'kk_answer_id');
    }

    public function criteria()
    {
        return $this->belongsTo(TemplateCriteria::class, 'criteria_id');
    }
}
