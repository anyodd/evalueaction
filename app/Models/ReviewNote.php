<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewNote extends Model
{
    use HasFactory;

    protected $table = 'review_notes';

    protected $fillable = [
        'kk_id',
        'reviewer_id',
        'catatan',
        'status',
    ];

    public function kertasKerja()
    {
        return $this->belongsTo(KertasKerja::class, 'kk_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
