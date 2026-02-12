@extends('adminlte::page')

@section('title', 'Tambah Role')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <h1 class="m-0 text-dark font-weight-bold">Tambah Role</h1>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title font-weight-bold text-navy">Form Role Baru</h3>
                    </div>
                    <form action="{{ route('roles.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="form-group">
                                <label for="name">Nama Role</label>
                                <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Admin Perwakilan, Korwas, dsb" required>
                                @error('name')
                                    <span class="invalid-feedback">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pb-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm">Simpan Role</button>
                            <a href="{{ route('roles.index') }}" class="btn btn-default rounded-pill px-4 float-right border-0 text-muted">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
