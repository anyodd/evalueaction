@extends('adminlte::page')

@section('title', 'Manajemen Template Kertas Kerja')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Manajemen Template Kertas Kerja</h1>
        <a href="{{ route('templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Template
        </a>
    </div>
@stop

@section('content')
    <div class="card shadow-sm border-0" style="border-radius: 10px;">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            <table class="table table-hover" id="templateTable">
                <thead class="bg-light">
                    <tr>
                        <th>Nama Template</th>
                        <th>Jenis Penugasan</th>
                        <th>Metode</th>
                        <th>Tahun</th>
                        <th class="text-center">Statistik</th>
                        <th class="text-center">Dipakai</th>
                        <th>Status</th>
                        <th style="min-width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                        <tr>
                            <td class="font-weight-bold">{{ $template->nama }}</td>
                            <td>{{ $template->jenisPenugasan->nama ?? '-' }}</td>
                            <td>
                                @php
                                    $metodeMap = [
                                        'tally' => ['label' => 'Tally', 'class' => 'secondary', 'icon' => 'fa-percentage'],
                                        'building_block' => ['label' => 'Building Block', 'class' => 'primary', 'icon' => 'fa-cubes'],
                                        'criteria_fulfillment' => ['label' => 'Pemenuhan', 'class' => 'info', 'icon' => 'fa-tasks'],
                                    ];
                                    $m = $metodeMap[$template->metode_penilaian] ?? $metodeMap['tally'];
                                @endphp
                                <span class="badge badge-{{ $m['class'] }}">
                                    <i class="fas {{ $m['icon'] }} mr-1"></i>{{ $m['label'] }}
                                </span>
                            </td>
                            <td>{{ $template->tahun }}</td>
                            <td class="text-center">
                                <small class="text-muted">
                                    <i class="fas fa-sitemap mr-1"></i>{{ $template->indicators_count }} indikator
                                    · <i class="fas fa-check-circle mr-1"></i>{{ $template->criteria_count }} kriteria
                                </small>
                            </td>
                            <td class="text-center">
                                @if($template->kk_count > 0)
                                    <span class="badge badge-success">{{ $template->kk_count }} KK</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($template->is_active)
                                    <span class="badge badge-success"><i class="fas fa-check mr-1"></i>Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('templates.builder', $template->id) }}" class="btn btn-info" title="Builder">
                                        <i class="fas fa-tools"></i> Builder
                                    </a>
                                    <a href="{{ route('templates.preview', $template->id) }}" class="btn btn-outline-info" title="Preview" target="_blank">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('templates.edit', $template->id) }}" class="btn btn-warning" title="Edit Header">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('templates.clone', $template->id) }}" method="POST" style="display:inline-block">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-primary" onclick="return confirm('Clone template ini?')" title="Clone">
                                            <i class="fas fa-copy"></i>
                                        </button>
                                    </form>
                                    @if($template->kk_count == 0)
                                    <form action="{{ route('templates.destroy', $template->id) }}" method="POST" style="display:inline-block">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus template ini?')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fas fa-folder-open fa-2x mb-2 d-block"></i>
                                Belum ada template. Klik <strong>Tambah Template</strong> untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
