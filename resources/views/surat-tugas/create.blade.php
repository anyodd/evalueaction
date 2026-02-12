@extends('adminlte::page')

@section('title', 'Tambah Surat Tugas')

@section('content_header')
    <h1>Buat Surat Tugas Baru</h1>
@stop

@section('content')
    <div class="card card-primary">
        <div class="card-header">
            <h3 class="card-title">Form Surat Tugas</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('surat-tugas.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="nomor_st">Nomor ST</label>
                    <input type="text" class="form-control" id="nomor_st" name="nomor_st" placeholder="Masukkan Nomor ST" required>
                </div>
                <div class="form-group">
                    <label for="tgl_st">Tanggal ST</label>
                    <input type="date" class="form-control" id="tgl_st" name="tgl_st" required>
                </div>
                <div class="form-group">
                    <label for="nama_objek">Nama Objek Evaluasi</label>
                    <input type="text" class="form-control" id="nama_objek" name="nama_objek" placeholder="Contoh: Pemda Kabupaten X" required>
                </div>
                <div class="form-group">
                    <label for="tahun_evaluasi">Tahun Evaluasi</label>
                    <input type="number" class="form-control" id="tahun_evaluasi" name="tahun_evaluasi" value="{{ date('Y') }}" required>
                </div>
            </div>
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('surat-tugas.index') }}" class="btn btn-default float-right">Batal</a>
            </div>
        </form>
    </div>
@stop
