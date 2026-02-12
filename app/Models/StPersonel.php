<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StPersonel extends Model
{
    use HasFactory;
    
    protected $table = 'st_personel';

    protected $fillable = [
        'st_id',
        'user_id',
        'role_dalam_tim',
    ];

    public function suratTugas()
    {
        return $this->belongsTo(SuratTugas::class, 'st_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
