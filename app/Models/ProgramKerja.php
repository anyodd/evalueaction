<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProgramKerja extends Model
{
    use HasFactory;

    protected $table = 'program_kerja';

    protected $fillable = [
        'st_id',
        'judul',
        'deskripsi',
        'tujuan',
        'ruang_lingkup',
        'metodologi',
        'status',
        'created_by',
        'tgl_mulai',
        'tgl_selesai',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
    ];

    public function suratTugas()
    {
        return $this->belongsTo(SuratTugas::class, 'st_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function langkah()
    {
        return $this->hasMany(PkLangkah::class, 'program_kerja_id')->orderBy('urutan');
    }

    public function langkahRoot()
    {
        return $this->hasMany(PkLangkah::class, 'program_kerja_id')
            ->whereNull('parent_id')
            ->orderBy('urutan');
    }

    /**
     * Hitung persentase progres berdasarkan langkah yang completed.
     */
    public function progressPercentage()
    {
        $total = $this->langkah()->count();
        if ($total === 0) return 0;

        $completed = $this->langkah()->where('status', 'completed')->count();
        return round(($completed / $total) * 100);
    }

    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeAttribute()
    {
        return [
            'draft' => 'badge-secondary',
            'active' => 'badge-primary',
            'completed' => 'badge-success',
            'archived' => 'badge-dark',
        ][$this->status] ?? 'badge-secondary';
    }

    /**
     * Get status label for UI.
     */
    public function getStatusLabelAttribute()
    {
        return [
            'draft' => 'Draft',
            'active' => 'Aktif',
            'completed' => 'Selesai',
            'archived' => 'Diarsipkan',
        ][$this->status] ?? $this->status;
    }
}
