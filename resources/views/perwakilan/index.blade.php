@extends('adminlte::page')

@section('title', 'Data Perwakilan')

@section('content_header')
    <h1>Daftar Perwakilan</h1>
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

        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="card-title font-weight-bold text-navy"><i class="fas fa-building mr-2"></i> Master Data Perwakilan</h3>
                <div class="card-tools">
                    <a href="{{ route('perwakilan.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"> <i class="fas fa-plus mr-1"></i> Tambah Data</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-4">Kode Wilayah</th>
                                <th>Nama Perwakilan</th>
                                <th class="text-right pr-4" style="width: 150px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($perwakilan as $p)
                                <tr>
                                    <td class="pl-4 font-weight-bold text-navy">{{ $p->kode_wilayah }}</td>
                                    <td>{{ $p->nama_perwakilan }}</td>
                                    <td class="text-right pr-4">
                                        <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                            <a href="{{ route('perwakilan.edit', $p->id) }}" class="btn btn-warning btn-xs shadow-none" title="Edit"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('perwakilan.destroy', $p->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs shadow-none" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus perwakilan ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-building fa-3x mb-3 opacity-2"></i><br>
                                            Data perwakilan belum tersedia.
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
