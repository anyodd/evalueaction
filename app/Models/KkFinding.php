<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KkFinding extends Model
{
    use HasFactory;

    protected $fillable = [
        'kk_teo_id',
        'cause',
        'recommendation',
        'user_id'
    ];

    public function teo()
    {
        return $this->belongsTo(KkTeo::class, 'kk_teo_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
