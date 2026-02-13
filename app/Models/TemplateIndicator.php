<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateIndicator extends Model
{
    protected $table = 'template_indicators';
    protected $fillable = ['template_id', 'parent_id', 'uraian', 'tipe', 'bobot', 'ref_jenis_id'];

    public function template()
    {
        return $this->belongsTo(KkTemplate::class);
    }

    public function parent()
    {
        return $this->belongsTo(TemplateIndicator::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(TemplateIndicator::class, 'parent_id');
    }

    public function criteria()
    {
        return $this->hasMany(TemplateCriteria::class, 'indicator_id');
    }
}
