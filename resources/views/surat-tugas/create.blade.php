@extends('adminlte::page')

@section('title', 'Buat Surat Tugas')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <h1 class="m-0 text-dark font-weight-bold">Buat Surat Tugas</h1>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        <form action="{{ route('surat-tugas.store') }}" method="POST">
            @csrf
            <div class="row">
                {{-- Data Utama ST --}}
                <div class="col-md-5">
                    <div class="card shadow-sm border-0" style="border-radius: 15px;">
                        <div class="card-header bg-white border-0 py-3">
                            <h3 class="card-title font-weight-bold text-navy"><i class="fas fa-info-circle mr-2"></i> Detail Surat Tugas</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label for="nomor_st">Nomor Surat Tugas</label>
                                <input type="text" name="nomor_st" id="nomor_st" class="form-control rounded-pill @error('nomor_st') is-invalid @enderror" value="{{ old('nomor_st') }}" placeholder="Contoh: ST-123/PW01/3/2024" required>
                                @error('nomor_st') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="tgl_st">Tanggal ST</label>
                                <input type="date" name="tgl_st" id="tgl_st" class="form-control rounded-pill @error('tgl_st') is-invalid @enderror" value="{{ old('tgl_st', date('Y-m-d')) }}" required>
                                @error('tgl_st') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="tgl_mulai">Tanggal Mulai</label>
                                    <input type="date" name="tgl_mulai" id="tgl_mulai" class="form-control rounded-pill @error('tgl_mulai') is-invalid @enderror" value="{{ old('tgl_mulai') }}">
                                    @error('tgl_mulai') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="tgl_selesai">Tanggal Selesai</label>
                                    <input type="date" name="tgl_selesai" id="tgl_selesai" class="form-control rounded-pill @error('tgl_selesai') is-invalid @enderror" value="{{ old('tgl_selesai') }}">
                                    @error('tgl_selesai') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nama_objek">Judul Penugasan</label>
                                <textarea name="nama_objek" id="nama_objek" class="form-control @error('nama_objek') is-invalid @enderror" rows="3" style="border-radius: 15px;" placeholder="Masukkan judul penugasan..." required>{{ old('nama_objek') }}</textarea>
                                @error('nama_objek') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="jenis_penugasan_id">Jenis Penugasan</label>
                                <select name="jenis_penugasan_id" id="jenis_penugasan_id" class="form-control rounded-pill @error('jenis_penugasan_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Jenis Penugasan --</option>
                                    @foreach($jenisPenugasan as $jp)
                                        <option value="{{ $jp->id }}" {{ old('jenis_penugasan_id') == $jp->id ? 'selected' : '' }}>{{ $jp->nama }}</option>
                                    @endforeach
                                </select>
                                @error('jenis_penugasan_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>
                            <div class="form-group">
                                <label for="tahun_evaluasi">Tahun Evaluasi</label>
                                <select name="tahun_evaluasi" id="tahun_evaluasi" class="form-control rounded-pill @error('tahun_evaluasi') is-invalid @enderror">
                                    @for($i = date('Y'); $i >= date('Y')-5; $i--)
                                        <option value="{{ $i }}" {{ old('tahun_evaluasi') == $i ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Personel Terlibat --}}
                <div class="col-md-7">
                    <div class="card shadow-sm border-0" style="border-radius: 15px;">
                        <div class="card-header bg-white border-0 py-3">
                            <h3 class="card-title font-weight-bold text-navy"><i class="fas fa-users mr-2"></i> Susunan Tim Pengawasan</h3>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-4">Tentukan personel dan peran mereka dalam penugasan ini.</p>
                            
                            <div class="table-responsive">
                                <table class="table table-borderless" id="personel-table">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th>Personel</th>
                                            <th>Peran Dalam Tim</th>
                                            <th style="width: 50px"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="personel-container">
                                        {{-- Row Template --}}
                                        <tr class="personel-row animate__animated animate__fadeIn">
                                            <td>
                                                <select name="personel[]" class="form-control select2 rounded-pill personel-select" required>
                                                    <option value="">-- Pilih Pegawai --</option>
                                                    @foreach($users as $user)
                                                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->nip ?? 'NIP -' }})</option>
                                                    @endforeach
                                                </select>
                                            </td>
                                            <td>
                                                <select name="peran[]" class="form-control rounded-pill" required>
                                                    <option value="Korwas">Korwas</option>
                                                    <option value="Dalnis">Dalnis</option>
                                                    <option value="Ketua Tim">Ketua Tim</option>
                                                    <option value="Anggota">Anggota</option>
                                                </select>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-outline-danger btn-sm border-0 remove-row"><i class="fas fa-times"></i></button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" id="add-personel" class="btn btn-outline-primary btn-sm rounded-pill mt-2">
                                <i class="fas fa-plus mr-1"></i> Tambah Personel
                            </button>
                        </div>
                        <div class="card-footer bg-white border-0 pb-4 text-center">
                            <hr>
                            <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">Simpan Surat Tugas</button>
                            <a href="{{ route('surat-tugas.index') }}" class="btn btn-link text-muted">Batal</a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Function to add new row
        $('#add-personel').click(function() {
            let row = $('.personel-row').first().clone();
            row.find('select').val('');
            row.find('.select2-container').remove(); // Remove select2 duplicate
            row.find('select').removeClass('select2-hidden-accessible');
            row.find('select').removeAttr('data-select2-id');
            row.find('option').removeAttr('data-select2-id');
            
            $('#personel-container').append(row);
            
            // Init select2 for new row
            row.find('.personel-select').select2({ theme: 'bootstrap4' });
        });

        // Function to remove row
        $(document).on('click', '.remove-row', function() {
            if ($('.personel-row').length > 1) {
                $(this).closest('tr').remove();
            } else {
                alert('Minimal harus ada 1 personel dalam tim.');
            }
        });

        // Initial setup for Select2 if not already initialized
        // $('.personel-select').select2({ theme: 'bootstrap4' });
    });
</script>
@stop
