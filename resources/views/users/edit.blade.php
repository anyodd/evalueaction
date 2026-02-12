@extends('adminlte::page')

@section('title', 'Edit Pengguna')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <h1 class="m-0 text-dark font-weight-bold">Update Pengguna</h1>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title font-weight-bold text-navy">Edit Data: {{ $user->name }}</h3>
                    </div>
                    <form action="{{ route('users.update', $user->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Nama Lengkap</label>
                                        <input type="text" name="name" id="name" class="form-control rounded-pill @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="nip">NIP</label>
                                        <input type="text" name="nip" id="nip" class="form-control rounded-pill @error('nip') is-invalid @enderror" value="{{ old('nip', $user->nip) }}">
                                        @error('nip') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email</label>
                                        <input type="email" name="email" id="email" class="form-control rounded-pill @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                                        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6 border-left">
                                    <div class="form-group">
                                        <label for="role_id">Role Jabatan</label>
                                        <select name="role_id" id="role_id" class="form-control rounded-pill @error('role_id') is-invalid @enderror" required>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ (old('role_id', $user->role_id) == $role->id) ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="perwakilan_id">Kantor Perwakilan</label>
                                        <select name="perwakilan_id" id="perwakilan_id" class="form-control rounded-pill">
                                            <option value="">Pusat</option>
                                            @foreach($perwakilan as $p)
                                                <option value="{{ $p->id }}" {{ (old('perwakilan_id', $user->perwakilan_id) == $p->id) ? 'selected' : '' }}>{{ $p->nama_perwakilan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-light p-3 mt-4" style="border-radius: 10px;">
                                <h6 class="font-weight-bold text-navy mb-3"><i class="fas fa-key mr-1"></i> Keamanan (Opsional)</h6>
                                <p class="text-muted small">Kosongkan password jika tidak ingin mengubahnya.</p>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password">Password Baru</label>
                                            <input type="password" name="password" id="password" class="form-control rounded-pill @error('password') is-invalid @enderror">
                                            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="password_confirmation">Konfirmasi Password</label>
                                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control rounded-pill">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pb-4 text-center">
                            <button type="submit" class="btn btn-warning text-white rounded-pill px-5 shadow-sm">Simpan Perubahan</button>
                            <a href="{{ route('users.index') }}" class="btn btn-link text-muted">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop
