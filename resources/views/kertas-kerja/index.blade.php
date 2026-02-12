@extends('adminlte::page')

@section('title', 'Kertas Kerja')

@section('content_header')
    <h1>Daftar Kertas Kerja</h1>
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
            <h3 class="card-title">Data Kertas Kerja</h3>
            <div class="card-tools">
                <a href="{{ route('kertas-kerja.create') }}" class="btn btn-primary btn-sm"> <i class="fas fa-plus"></i> Buat Kertas Kerja</a>
            </div>
        </div>
        <div class="card-body">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 50px">No</th>
                        <th>Judul KK</th>
                        <th>Surat Tugas</th>
                        <th>Oleh</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($kertasKerja as $key => $kk)
                        <tr>
                            <td>{{ $key + 1 }}</td>
                            <td>{{ $kk->judul_kk }}</td>
                            <td>{{ $kk->suratTugas->nomor_st }}</td>
                            <td>{{ $kk->user->name }}</td>
                            <td>
                                <span class="badge badge-{{ $kk->status_approval == 'Draft' ? 'secondary' : 'primary' }}">
                                    {{ $kk->status_approval }}
                                </span>
                            </td>
                            <td>
                                <a href="#" class="btn btn-info btn-xs"><i class="fas fa-eye"></i></a>
                                <a href="#" class="btn btn-warning btn-xs"><i class="fas fa-edit"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">Data belum tersedia</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@stop
