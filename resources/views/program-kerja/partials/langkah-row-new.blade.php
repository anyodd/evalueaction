{{-- Langkah row for Parameter view --}}
<tr data-langkah-id="{{ $langkah->id }}">
    <td class="text-center align-middle">{{ $nomor }}</td>
    <td class="align-middle">
        <strong>{{ $langkah->judul }}</strong>
        @if($langkah->deskripsi)
            <br><small class="text-muted">{{ Str::limit($langkah->deskripsi, 100) }}</small>
        @endif
        @if($langkah->jenis_prosedur)
            <div class="mt-1"><span class="badge badge-light border">{{ $langkah->jenis_prosedur_label }}</span></div>
        @endif
        @if($langkah->kertasKerja)
            <div class="mt-1">
                <a href="{{ route('kertas-kerja.edit', $langkah->kertas_kerja_id) }}" class="badge badge-primary p-1" target="_blank" title="Buka Kertas Kerja">
                    <i class="fas fa-external-link-alt mr-1"></i>{{ Str::limit($langkah->kertasKerja->judul_kk, 25) }}
                </a>
            </div>
        @endif
    </td>
    <td class="align-middle">
        @forelse($langkah->assignments as $assignment)
            <div class="d-flex align-items-center mb-1">
                <img src="{{ $assignment->user->adminlte_image() }}" class="rounded-circle mr-1" width="20" height="20">
                <small>{{ $assignment->user->name ?? '-' }}</small>
                @if($canManage)
                    <button type="button" class="btn btn-xs btn-outline-danger ml-1 btn-remove-assignment" 
                            data-assignment-id="{{ $assignment->id }}" title="Hapus Tugas">
                        <i class="fas fa-times" style="font-size: 8px;"></i>
                    </button>
                @endif
            </div>
        @empty
            <small class="text-muted"><i>Belum ditugaskan</i></small>
        @endforelse
    </td>
    <td class="align-middle">
        <div><span class="badge {{ $langkah->status_badge }} p-2">{{ $langkah->status_label }}</span></div>
        @if($langkah->catatan_hasil)
            <div class="mt-1"><small class="text-success"><i class="fas fa-check-circle mr-1"></i>{{ Str::limit($langkah->catatan_hasil, 30) }}</small></div>
        @endif
    </td>
    <td class="align-middle">
        <div class="btn-group btn-group-sm">
            @if($canManage)
                <button type="button" class="btn btn-outline-primary btn-assign" title="Tugaskan"
                        data-langkah-id="{{ $langkah->id }}"
                        data-langkah-judul="{{ $langkah->judul }}">
                    <i class="fas fa-user-plus"></i>
                </button>
            @endif
            <button type="button" class="btn btn-outline-info btn-status" title="Update Status"
                    data-langkah-id="{{ $langkah->id }}"
                    data-langkah-judul="{{ $langkah->judul }}"
                    data-langkah-status="{{ $langkah->status }}">
                <i class="fas fa-sync-alt"></i>
            </button>
            @if($canManage && !$langkah->is_mandatory)
                <form action="{{ route('program-kerja.langkah.destroy', $langkah->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus langkah ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger" title="Hapus Prosedur">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </form>
            @endif
        </div>
    </td>
</tr>
