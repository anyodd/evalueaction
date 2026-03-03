@extends('adminlte::page')

@section('title', 'Edit Jenis Penugasan')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <h1 class="m-0 text-dark font-weight-bold">Edit Jenis Penugasan</h1>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title font-weight-bold text-navy">Update Data</h3>
                    </div>
                    <form action="{{ route('jenis-penugasan.update', $jenisPenugasan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="form-group">
                                <label for="kode">Kode Penugasan <span class="text-danger">*</span></label>
                                <input type="text" name="kode" id="kode" class="form-control @error('kode') is-invalid @enderror" value="{{ old('kode', $jenisPenugasan->kode) }}" required>
                                @error('kode')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label for="nama">Nama Penugasan <span class="text-danger">*</span></label>
                                <input type="text" name="nama" id="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $jenisPenugasan->nama) }}" required>
                                @error('nama')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pb-4">
                            <button type="submit" class="btn btn-warning text-white rounded-pill px-4 shadow-sm">Simpan Perubahan</button>
                            <a href="{{ route('jenis-penugasan.index') }}" class="btn btn-default rounded-pill px-4 float-right border-0 text-muted">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
