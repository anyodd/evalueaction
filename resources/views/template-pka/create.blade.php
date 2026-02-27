@extends('adminlte::page')

@section('title', 'Buat Template PKA')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Buat Template Program Kerja</h1>
        <a href="{{ route('template-pka.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
    </div>
@stop

@section('content')
    <div class="card shadow-sm border-0" style="border-radius: 10px;">
        <form action="{{ route('template-pka.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Judul Template <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror"
                           placeholder="Contoh: Program Kerja Evaluasi Manajemen Risiko" required>
                    @error('judul') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2" placeholder="Deskripsi umum template..."></textarea>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tujuan</label>
                            <textarea name="tujuan" class="form-control" rows="3" placeholder="Tujuan evaluasi/audit..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ruang Lingkup</label>
                            <textarea name="ruang_lingkup" class="form-control" rows="3" placeholder="Ruang lingkup pemeriksaan..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Metodologi</label>
                            <textarea name="metodologi" class="form-control" rows="3" placeholder="Metodologi yang digunakan..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan & Lanjut ke Builder</button>
            </div>
        </form>
    </div>
@stop
