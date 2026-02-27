<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PkAssignment extends Model
{
    use HasFactory;

    protected $table = 'pk_assignment';

    protected $fillable = [
        'pk_langkah_id',
        'user_id',
        'assigned_by',
        'catatan',
        'status',
        'tgl_deadline',
    ];

    protected $casts = [
        'tgl_deadline' => 'date',
    ];

    public function langkah()
    {
        return $this->belongsTo(PkLangkah::class, 'pk_langkah_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedByUser()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeAttribute()
    {
        return [
            'assigned' => 'badge-warning',
            'accepted' => 'badge-info',
            'in_progress' => 'badge-primary',
            'completed' => 'badge-success',
        ][$this->status] ?? 'badge-secondary';
    }

    /**
     * Get status label for UI.
     */
    public function getStatusLabelAttribute()
    {
        return [
            'assigned' => 'Ditugaskan',
            'accepted' => 'Diterima',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
        ][$this->status] ?? $this->status;
    }
}
