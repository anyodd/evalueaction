@extends('adminlte::page')

@section('title', 'Edit Surat Tugas')

@section('content_header')
    <h1>Edit Surat Tugas</h1>
@stop

@section('content')
    <div class="card card-warning">
        <div class="card-header">
            <h3 class="card-title">Form Update Surat Tugas</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('surat-tugas.update', $surat_tuga->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label for="nomor_st">Nomor ST</label>
                    <input type="text" class="form-control" id="nomor_st" name="nomor_st" value="{{ $surat_tuga->nomor_st }}" required>
                </div>
                <div class="form-group">
                    <label for="tgl_st">Tanggal ST</label>
                    <input type="date" class="form-control" id="tgl_st" name="tgl_st" value="{{ $surat_tuga->tgl_st }}" required>
                </div>
                <div class="form-group">
                    <label for="nama_objek">Nama Objek Evaluasi</label>
                    <input type="text" class="form-control" id="nama_objek" name="nama_objek" value="{{ $surat_tuga->nama_objek }}" required>
                </div>
                <div class="form-group">
                    <label for="tahun_evaluasi">Tahun Evaluasi</label>
                    <input type="number" class="form-control" id="tahun_evaluasi" name="tahun_evaluasi" value="{{ $surat_tuga->tahun_evaluasi }}" required>
                </div>
            </div>
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-warning">Update Data</button>
                <a href="{{ route('surat-tugas.index') }}" class="btn btn-default float-right">Batal</a>
            </div>
        </form>
    </div>
@stop
