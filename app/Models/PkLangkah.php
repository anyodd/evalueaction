<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PkLangkah extends Model
{
    use HasFactory;

    protected $table = 'pk_langkah';

    protected $fillable = [
        'program_kerja_id',
        'parent_id',
        'urutan',
        'judul',
        'deskripsi',
        'jenis_prosedur',
        'target_hari',
        'tgl_mulai',
        'tgl_selesai',
        'status',
        'kertas_kerja_id',
        'catatan_hasil',
        'ref_dokumen',
        'from_template',
    ];

    protected $casts = [
        'tgl_mulai' => 'date',
        'tgl_selesai' => 'date',
    ];

    public function programKerja()
    {
        return $this->belongsTo(ProgramKerja::class, 'program_kerja_id');
    }

    public function parent()
    {
        return $this->belongsTo(PkLangkah::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(PkLangkah::class, 'parent_id')->orderBy('urutan');
    }

    public function kertasKerja()
    {
        return $this->belongsTo(KertasKerja::class, 'kertas_kerja_id');
    }

    public function assignments()
    {
        return $this->hasMany(PkAssignment::class, 'pk_langkah_id');
    }

    /**
     * Get assigned user names as comma-separated string.
     */
    public function getAssigneeNamesAttribute()
    {
        return $this->assignments->map(fn($a) => $a->user->name ?? '-')->implode(', ');
    }

    /**
     * Get status badge class for UI.
     */
    public function getStatusBadgeAttribute()
    {
        return [
            'pending' => 'badge-secondary',
            'in_progress' => 'badge-info',
            'completed' => 'badge-success',
            'skipped' => 'badge-dark',
        ][$this->status] ?? 'badge-secondary';
    }

    /**
     * Get status label for UI.
     */
    public function getStatusLabelAttribute()
    {
        return [
            'pending' => 'Pending',
            'in_progress' => 'Sedang Dikerjakan',
            'completed' => 'Selesai',
            'skipped' => 'Dilewati',
        ][$this->status] ?? $this->status;
    }

    /**
     * Get jenis prosedur label for UI.
     */
    public function getJenisProsedurLabelAttribute()
    {
        return [
            'wawancara' => 'Wawancara',
            'observasi' => 'Observasi',
            'inspeksi_dokumen' => 'Inspeksi Dokumen',
            'analisis_data' => 'Analisis Data',
            'konfirmasi' => 'Konfirmasi',
            'rekalkulasi' => 'Rekalkulasi',
            'lainnya' => 'Lainnya',
        ][$this->jenis_prosedur] ?? '-';
    }
}
