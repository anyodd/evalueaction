@extends('adminlte::page')

@section('title', 'Buat Kertas Kerja')

@section('content_header')
    <h1>Buat Kertas Kerja Baru</h1>
@stop

@section('content')
    <div class="card card-info">
        <div class="card-header">
            <h3 class="card-title">Form Kertas Kerja</h3>
        </div>
        <!-- /.card-header -->
        <!-- form start -->
        <form action="{{ route('kertas-kerja.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label for="judul_kk">Judul Kertas Kerja</label>
                    <input type="text" class="form-control" id="judul_kk" name="judul_kk" placeholder="Masukkan Judul Kertas Kerja" required>
                </div>
                <div class="form-group">
                    <label for="st_id">Pilih Surat Tugas</label>
                    <select class="form-control select2" name="st_id" style="width: 100%;" required>
                        <option selected="selected" disabled value="">-- Pilih Surat Tugas --</option>
                        @foreach($suratTugas as $st)
                            <option value="{{ $st->id }}">{{ $st->nomor_st }} - {{ $st->nama_objek }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="isi_kk">Uraian / Isi Kertas Kerja</label>
                    <textarea class="form-control" rows="5" id="isi_kk" name="isi_kk" placeholder="Uraian hasil evaluasi..."></textarea>
                </div>
                <!-- File upload placeholder -->
                <div class="form-group">
                    <label for="exampleInputFile">File Pendukung</label>
                    <div class="input-group">
                      <div class="custom-file">
                        <input type="file" class="custom-file-input" id="exampleInputFile">
                        <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                      </div>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->

            <div class="card-footer">
                <button type="submit" class="btn btn-info">Simpan Draft</button>
                <a href="{{ route('kertas-kerja.index') }}" class="btn btn-default float-right">Batal</a>
            </div>
        </form>
    </div>
@stop
