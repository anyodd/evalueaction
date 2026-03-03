@extends('adminlte::page')

@section('title', 'Data Jenis Penugasan')

@section('content_header')
    <h1>Daftar Jenis Penugasan</h1>
@stop

@section('content')
    <div class="animate__animated animate__fadeIn">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius: 10px;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius: 10px;">
                <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="card-title font-weight-bold text-navy"><i class="fas fa-tags mr-2"></i> Master Data Jenis Penugasan</h3>
                <div class="card-tools">
                    <a href="{{ route('jenis-penugasan.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"> <i class="fas fa-plus mr-1"></i> Tambah</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0 table-stack">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-4">ID</th>
                                <th>Kode</th>
                                <th>Nama Penugasan</th>
                                <th class="text-right pr-4" style="width: 150px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($jenisPenugasan as $item)
                                <tr>
                                    <td data-label="ID" class="pl-4 font-weight-bold text-navy">#{{ $item->id }}</td>
                                    <td data-label="Kode"><span class="badge badge-outline-secondary px-3 py-2 rounded-pill shadow-sm">{{ $item->kode }}</span></td>
                                    <td data-label="Nama Penugasan"><strong>{{ $item->nama }}</strong></td>
                                    <td data-label="Aksi" class="text-right pr-4">
                                        <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                            <a href="{{ route('jenis-penugasan.edit', $item->id) }}" class="btn btn-warning btn-xs shadow-none" title="Edit"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('jenis-penugasan.destroy', $item->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-danger btn-xs shadow-none btn-confirm" 
                                                    title="Hapus" 
                                                    data-title="Hapus Data?"
                                                    data-text="Apakah Anda yakin ingin menghapus data ini?"
                                                    data-icon="warning">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-tags fa-3x mb-3 opacity-2"></i><br>
                                            Data Jenis Penugasan belum tersedia.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
