{{-- Recursive langkah row partial --}}
<tr class="langkah-row {{ $langkah->from_template ? 'bg-light-blue' : '' }}" data-langkah-id="{{ $langkah->id }}">
    <td class="text-center align-middle">{{ $langkah->urutan }}</td>
    <td class="align-middle">
        <div style="padding-left: {{ $level * 20 }}px;">
            @if($level > 0)<i class="fas fa-level-up-alt fa-rotate-90 text-muted mr-1"></i>@endif
            @if($langkah->from_template)
                <span class="badge badge-outline-secondary mr-1" style="font-size: 0.65rem;" title="Langkah dari template (read-only)"><i class="fas fa-lock mr-1"></i>Template</span>
            @endif
            <strong>{{ $langkah->judul }}</strong>
            @if($langkah->deskripsi)
                <br><small class="text-muted">{{ Str::limit($langkah->deskripsi, 80) }}</small>
            @endif
            @if($langkah->catatan_hasil)
                <br><small class="text-success"><i class="fas fa-check-circle mr-1"></i>{{ Str::limit($langkah->catatan_hasil, 60) }}</small>
            @endif
        </div>
    </td>
    <td class="align-middle">
        @if($langkah->jenis_prosedur)
            <span class="badge badge-light border p-1">{{ $langkah->jenis_prosedur_label }}</span>
        @else
            <span class="text-muted">-</span>
        @endif
    </td>
    <td class="align-middle">
        @forelse($langkah->assignments as $assignment)
            <div class="d-flex align-items-center mb-1">
                <img src="{{ $assignment->user->adminlte_image() }}" class="rounded-circle mr-1" width="20" height="20">
                <small>{{ $assignment->user->name ?? '-' }}</small>
                @if($canManage)
                    <button type="button" class="btn btn-xs btn-outline-danger ml-1 btn-remove-assignment" 
                            data-assignment-id="{{ $assignment->id }}" title="Hapus">
                        <i class="fas fa-times" style="font-size: 8px;"></i>
                    </button>
                @endif
            </div>
        @empty
            <small class="text-muted">Belum ditugaskan</small>
        @endforelse
    </td>
    <td class="align-middle">
        @if($langkah->kertasKerja)
            <a href="{{ route('kertas-kerja.edit', $langkah->kertas_kerja_id) }}" class="badge badge-primary p-1" target="_blank">
                <i class="fas fa-external-link-alt mr-1"></i>{{ Str::limit($langkah->kertasKerja->judul_kk, 20) }}
            </a>
        @else
            <small class="text-muted">-</small>
        @endif
    </td>
    <td class="align-middle">
        <span class="badge {{ $langkah->status_badge }} p-2">{{ $langkah->status_label }}</span>
    </td>
    <td class="align-middle">
        @if($langkah->target_hari)
            <small>{{ $langkah->target_hari }} hari</small>
        @else
            <small class="text-muted">-</small>
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
            @if($canManage)
                <button type="button" class="btn btn-outline-secondary btn-link-kk" title="Link Kertas Kerja"
                        data-langkah-id="{{ $langkah->id }}"
                        data-langkah-judul="{{ $langkah->judul }}"
                        data-current-kk="{{ $langkah->kertas_kerja_id }}">
                    <i class="fas fa-link"></i>
                </button>
            @endif
        </div>
    </td>
</tr>

{{-- Recursive children --}}
@if($langkah->children && $langkah->children->count() > 0)
    @foreach($langkah->children as $child)
        @include('program-kerja.partials.langkah-row', ['langkah' => $child, 'level' => $level + 1])
    @endforeach
@endif
