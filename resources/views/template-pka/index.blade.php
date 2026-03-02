@extends('adminlte::page')

@section('title', 'Template Program Kerja')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1><i class="fas fa-clipboard-list mr-2 text-primary"></i>Template Program Kerja</h1>
        <a href="{{ route('template-pka.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-1"></i> Buat Template Baru
        </a>
    </div>
@stop

@section('css')
<style>
    .btn-action-group {
        display: flex;
        gap: 8px;
        align-items: center;
        justify-content: center;
    }
    .btn-action {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        box-shadow: 0 2px 5px rgba(0,0,0,0.06);
        position: relative;
    }
    .btn-action:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    .btn-action-builder {
        width: auto;
        padding: 0 16px;
        border-radius: 12px !important;
        font-weight: 600;
        background: linear-gradient(135deg, #00c6ff, #0072ff);
        color: white;
    }
    .btn-action-warning { background: linear-gradient(135deg, #f6d365, #fda085); color: white; }
    .btn-action-danger { background: linear-gradient(135deg, #ff0844, #ffb199); color: white; }
    
    .table td { vertical-align: middle !important; }
</style>
@stop

@section('content')
    <div class="card shadow-sm border-0" style="border-radius: 10px;">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>{{ session('error') }}</div>
            @endif

            <table class="table table-hover">
                <thead class="bg-light">
                    <tr>
                        <th>Judul Template</th>
                        <th class="text-center">Langkah</th>
                        <th>Dibuat Oleh</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $tpl)
                        <tr>
                            <td class="font-weight-bold">{{ $tpl->judul }}</td>
                            <td class="text-center">
                                <span class="badge badge-light border p-2">
                                    <i class="fas fa-list-ol mr-1"></i>{{ $tpl->langkah_count }} langkah
                                </span>
                            </td>
                            <td>{{ $tpl->creator->name ?? '-' }}</td>
                            <td>
                                @if($tpl->status === 'published')
                                    <span class="badge badge-success"><i class="fas fa-globe mr-1"></i>Published</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-action-group">
                                    <a href="{{ route('template-pka.show', $tpl->id) }}" class="btn btn-action btn-action-builder" title="Builder Langkah">
                                        <i class="fas fa-tools mr-1"></i> Builder
                                    </a>
                                    <a href="{{ route('template-pka.edit', $tpl->id) }}" class="btn btn-action btn-action-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    @if($tpl->status !== 'published')
                                    <form action="{{ route('template-pka.destroy', $tpl->id) }}" method="POST" style="display:inline-block">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-action btn-action-danger" onclick="return confirm('Hapus template ini?')" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-clipboard fa-2x mb-2 d-block"></i>
                                Belum ada template. Klik <strong>Buat Template Baru</strong> untuk memulai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
