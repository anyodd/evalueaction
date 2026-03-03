<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KertasKerjaAudit extends Model
{
    use HasFactory;

    protected $table = 'kertas_kerja_audits';

    protected $fillable = [
        'kertas_kerja_id',
        'user_id',
        'action',
        'description',
    ];

    public function kertasKerja()
    {
        return $this->belongsTo(KertasKerja::class, 'kertas_kerja_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
