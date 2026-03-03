<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateCause extends Model
{
    use HasFactory;

    protected $fillable = ['teo_id', 'uraian'];

    public function teo()
    {
        return $this->belongsTo(TemplateTeo::class, 'teo_id');
    }

    public function recommendations()
    {
        return $this->belongsToMany(TemplateRecommendation::class, 'template_cause_recommendation', 'cause_id', 'recommendation_id');
    }
}
