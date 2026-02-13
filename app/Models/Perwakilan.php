<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Perwakilan extends Model
{
    use HasFactory;
    
    protected $table = 'perwakilan';
    protected $fillable = [
        'nama_perwakilan', 
        'kode_wilayah',
        'alamat',
        'telepon',
        'email',
        'website'
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
