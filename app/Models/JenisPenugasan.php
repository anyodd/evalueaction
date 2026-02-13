<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisPenugasan extends Model
{
    protected $table = 'jenis_penugasan';
    protected $fillable = ['nama', 'kode'];

    public function templates()
    {
        return $this->hasMany(KkTemplate::class);
    }
}
