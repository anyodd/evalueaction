<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateRecommendation extends Model
{
    use HasFactory;

    protected $fillable = ['teo_id', 'uraian'];

    public function teo()
    {
        return $this->belongsTo(TemplateTeo::class, 'teo_id');
    }

    public function causes()
    {
        return $this->belongsToMany(TemplateCause::class, 'template_cause_recommendation', 'recommendation_id', 'cause_id');
    }
}
