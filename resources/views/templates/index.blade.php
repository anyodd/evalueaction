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
    <div class="card">
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nama Template</th>
                        <th>Jenis Penugasan</th>
                        <th>Tahun</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($templates as $template)
                        <tr>
                            <td>{{ $template->nama }}</td>
                            <td>{{ $template->jenisPenugasan->nama ?? '-' }}</td>
                            <td>{{ $template->tahun }}</td>
                            <td>
                                @if($template->is_active)
                                    <span class="badge badge-success">Aktif</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group">
                                    <a href="{{ route('templates.builder', $template->id) }}" class="btn btn-sm btn-info" title="Manage Indicators & Questions">
                                        <i class="fas fa-tools"></i> Builder
                                    </a>
                                    <a href="{{ route('templates.edit', $template->id) }}" class="btn btn-sm btn-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('templates.destroy', $template->id) }}" method="POST" style="display:inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Hapus template ini?')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada template.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
