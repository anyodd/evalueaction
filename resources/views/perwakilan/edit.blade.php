@extends('adminlte::page')

@section('title', 'Edit Perwakilan')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <h1 class="m-0 text-dark font-weight-bold">Edit Perwakilan</h1>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title font-weight-bold text-navy">Update Data Perwakilan</h3>
                    </div>
                    <form action="{{ route('perwakilan.update', $perwakilan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label for="kode_wilayah">Kode Wilayah</label>
                                <input type="text" name="kode_wilayah" id="kode_wilayah" class="form-control @error('kode_wilayah') is-invalid @enderror" value="{{ old('kode_wilayah', $perwakilan->kode_wilayah) }}" required>
                                @error('kode_wilayah')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="nama_perwakilan">Nama Perwakilan</label>
                                <input type="text" name="nama_perwakilan" id="nama_perwakilan" class="form-control @error('nama_perwakilan') is-invalid @enderror" value="{{ old('nama_perwakilan', $perwakilan->nama_perwakilan) }}" required>
                                @error('nama_perwakilan')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pb-4">
                            <button type="submit" class="btn btn-warning text-white rounded-pill px-4 shadow-sm">Simpan Perubahan</button>
                            <a href="{{ route('perwakilan.index') }}" class="btn btn-default rounded-pill px-4 float-right border-0 text-muted">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
