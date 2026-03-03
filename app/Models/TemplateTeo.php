<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateTeo extends Model
{
    use HasFactory;

    protected $fillable = ['indicator_id', 'teo'];

    public function indicator()
    {
        return $this->belongsTo(TemplateIndicator::class, 'indicator_id');
    }

    public function causes()
    {
        return $this->hasMany(TemplateCause::class, 'teo_id');
    }

    public function recommendations()
    {
        return $this->hasMany(TemplateRecommendation::class, 'teo_id');
    }
}
