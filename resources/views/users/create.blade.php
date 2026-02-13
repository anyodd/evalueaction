@extends('adminlte::page')

@section('title', 'Tambah User Baru')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <h1 class="m-0 text-dark font-weight-bold">User Baru</h1>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-white border-0 py-3">
                        <h3 class="card-title font-weight-bold text-navy">Form Registrasi Pengguna</h3>
                    </div>
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 text-center py-4">
                                     <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3 shadow-sm" style="width: 120px; height: 120px;">
                                        <i class="fas fa-user-plus fa-4x text-navy opacity-3"></i>
                                    </div>
                                    <p class="text-muted small px-3">Pastikan data NIP dan Email sesuai dengan identitas resmi pegawai BPKP.</p>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="name">Nama Lengkap</label>
                                        <input type="text" name="name" id="name" class="form-control rounded-pill @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                        @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="nip">NIP</label>
                                        <input type="text" name="nip" id="nip" class="form-control rounded-pill @error('nip') is-invalid @enderror" value="{{ old('nip') }}">
                                        @error('nip') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="email">Email <small class="text-info font-italic">(Wajib @bpkp.go.id)</small></label>
                                        <input type="email" name="email" id="email" class="form-control rounded-pill @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="username@bpkp.go.id" required>
                                        @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="role_id">Role Jabatan</label>
                                        <select name="role_id" id="role_id" class="form-control rounded-pill @error('role_id') is-invalid @enderror" required>
                                            <option value="">-- Pilih Role --</option>
                                            @foreach($roles as $role)
                                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('role_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="perwakilan_id">Kantor Perwakilan</label>
                                        <select name="perwakilan_id" id="perwakilan_id" class="form-control rounded-pill @error('perwakilan_id') is-invalid @enderror">
                                            <option value="">Pusat</option>
                                            @foreach($perwakilan as $p)
                                                <option value="{{ $p->id }}" {{ old('perwakilan_id') == $p->id ? 'selected' : '' }}>{{ $p->nama_perwakilan }}</option>
                                            @endforeach
                                        </select>
                                        @error('perwakilan_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="password">Password</label>
                                        <input type="password" name="password" id="password" class="form-control rounded-pill @error('password') is-invalid @enderror" required>
                                        @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="form-group">
                                        <label for="password_confirmation">Konfirmasi Password</label>
                                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control rounded-pill" required>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 pb-4 text-center">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">Simpan User Baru</button>
                            <a href="{{ route('users.index') }}" class="btn btn-link text-muted">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        const roleSelect = $('#role_id');
        const nipInput = $('#nip');
        const perwakilanSelect = $('#perwakilan_id');

        // NIP Mask: ######## ###### # ###
        nipInput.inputmask("99999999 999999 9 999", {
            placeholder: "_",
            showMaskOnHover: false,
            showMaskOnFocus: true
        });

        function toggleNip() {
            const selectedRole = roleSelect.find('option:selected').text().trim();
            console.log("Selected Role:", selectedRole); // For debugging
            const exemptRoles = ['Superadmin', 'Admin Perwakilan', 'Rendal'];
            
            if (exemptRoles.includes(selectedRole)) {
                nipInput.prop('disabled', true).val('').attr('placeholder', 'NIP tidak diperlukan untuk role ini');
            } else {
                nipInput.prop('disabled', false).attr('placeholder', '######## ###### # ###');
            }
        }

        roleSelect.on('change', toggleNip);
        toggleNip(); // Run on load

        // If only one perwakilan option (Admin Perwakilan scope), auto-select it
        if (perwakilanSelect.find('option[value!=""]').length === 1) {
             perwakilanSelect.find('option[value!=""]').prop('selected', true);
        }
    });
</script>
@stop
