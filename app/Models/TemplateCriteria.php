<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateCriteria extends Model
{
    use HasFactory;

    protected $table = 'template_criteria';
    protected $fillable = ['indicator_id', 'uraian', 'level'];

    public function indicator()
    {
        return $this->belongsTo(TemplateIndicator::class, 'indicator_id');
    }
}
