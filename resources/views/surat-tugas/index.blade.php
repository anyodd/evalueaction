@extends('adminlte::page')

@section('title', 'Surat Tugas')

@section('content_header')
    <h1>Daftar Surat Tugas</h1>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Data Surat Tugas</h3>
            <div class="card-tools">
                <a href="{{ route('surat-tugas.create') }}" class="btn btn-primary btn-sm"> <i class="fas fa-plus"></i> Tambah Baru</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px">No</th>
                        <th>Nomor ST</th>
                        <th>Tanggal ST</th>
                        <th>Objek Evaluasi</th>
                        <th>Tahun</th>
                        <th>Perwakilan</th>
                        <th style="width: 150px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suratTugas as $key => $st)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $st->nomor_st }}</td>
                            <td>{{ \Carbon\Carbon::parse($st->tgl_st)->format('d/m/Y') }}</td>
                            <td>{{ $st->nama_objek }}</td>
                            <td>{{ $st->tahun_evaluasi }}</td>
                            <td>{{ $st->perwakilan->nama_perwakilan ?? '-' }}</td>
                            <td>
                                <a href="{{ route('surat-tugas.print', $st->id) }}" target="_blank" class="btn btn-default btn-xs" title="Cetak"><i class="fas fa-print"></i></a>
                                <a href="{{ route('surat-tugas.edit', $st->id) }}" class="btn btn-warning btn-xs" title="Edit"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('surat-tugas.destroy', $st->id) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-xs" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Data belum tersedia</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
