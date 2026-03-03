{{-- Recursive langkah item for Template PKA builder --}}
@php
    $indent = $depth * 24;
    $procedureIcons = [
        'wawancara' => 'fa-comments',
        'observasi' => 'fa-binoculars',
        'inspeksi_dokumen' => 'fa-file-alt',
        'analisis_data' => 'fa-chart-line',
        'konfirmasi' => 'fa-handshake',
        'rekalkulasi' => 'fa-calculator',
        'lainnya' => 'fa-ellipsis-h',
    ];
@endphp

<div class="langkah-item list-group-item list-group-item-action py-2 border-0 border-bottom" style="padding-left: {{ 16 + $indent }}px;" id="langkah-{{ $langkah->id }}">
    <div class="d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center flex-grow-1">
            <span class="text-muted font-weight-bold mr-2" style="min-width: 25px;">{{ $langkah->urutan }}.</span>
            <div class="flex-grow-1">
                <span class="font-weight-bold langkah-editable" data-id="{{ $langkah->id }}" data-field="judul">{{ $langkah->judul }}</span>
                @if($langkah->jenis_prosedur)
                    <span class="badge badge-light border ml-1" style="font-size: 0.7rem;">
                        <i class="fas {{ $procedureIcons[$langkah->jenis_prosedur] ?? 'fa-ellipsis-h' }} mr-1"></i>{{ $langkah->jenis_prosedur_label }}
                    </span>
                @endif
                @if($langkah->target_hari)
                    <span class="badge badge-light border ml-1" style="font-size: 0.7rem;">
                        <i class="fas fa-clock mr-1"></i>{{ $langkah->target_hari }} hari
                    </span>
                @endif
                @if($langkah->kk_template_id && $langkah->kkTemplate)
                    <span class="badge badge-info border ml-1" style="font-size: 0.7rem;" title="Terkoneksi ke Template Kertas Kerja">
                        <i class="fas fa-link mr-1"></i>{{ $langkah->kkTemplate->nama }}
                    </span>
                @endif
                @if($langkah->deskripsi)
                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($langkah->deskripsi, 120) }}</small>
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center ml-2">
            <button class="btn btn-xs btn-outline-primary border-0 mr-1" data-toggle="modal" data-target="#addLangkahModal" data-parent="{{ $langkah->id }}" title="Tambah Sub-langkah">
                <i class="fas fa-plus"></i>
            </button>
            <button class="btn btn-xs text-danger opacity-50 hover-opacity-100 btn-delete-langkah" data-id="{{ $langkah->id }}" title="Hapus">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</div>

@if($langkah->children->count())
    @foreach($langkah->children as $child)
        @include('template-pka.partials.langkah-item', ['langkah' => $child, 'depth' => $depth + 1])
    @endforeach
@endif
