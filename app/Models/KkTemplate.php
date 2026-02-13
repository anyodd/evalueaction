<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkTemplate extends Model
{
    protected $table = 'kk_templates';
    protected $fillable = ['jenis_penugasan_id', 'nama', 'tahun', 'is_active'];

    public function jenisPenugasan()
    {
        return $this->belongsTo(JenisPenugasan::class);
    }
    
    public function indicators()
    {
        return $this->hasMany(TemplateIndicator::class, 'template_id');
    }
}
