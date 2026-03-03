@extends('adminlte::page')

@section('title', 'Buat Program Kerja')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-plus-circle text-primary mr-2"></i>Buat Program Kerja
            </h1>
            <a href="{{ route('program-kerja.index') }}" class="btn btn-default btn-sm shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        <form action="{{ route('program-kerja.store') }}" method="POST">
            @csrf

            @if ($errors->any())
                <div class="alert alert-danger" style="border-radius: 10px;">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Header Info --}}
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title font-weight-bold text-primary"><i class="fas fa-info-circle mr-2"></i>Informasi Umum</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Surat Tugas <span class="text-danger">*</span></label>
                                <select name="st_id" class="form-control select2" required>
                                    <option value="">-- Pilih Surat Tugas --</option>
                                    @foreach($suratTugas as $st)
                                        <option value="{{ $st->id }}" {{ (old('st_id', $selectedStId) == $st->id) ? 'selected' : '' }}>
                                            {{ $st->nomor_st }} — {{ $st->nama_objek }} ({{ $st->jenisPenugasan->nama ?? '-' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Judul Program Kerja <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control" value="{{ old('judul') }}" placeholder="Contoh: PKA Evaluasi BLUD RSUD XYZ Tahun 2025" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Tanggal Mulai</label>
                                <input type="date" name="tgl_mulai" class="form-control" value="{{ old('tgl_mulai') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Tanggal Selesai</label>
                                <input type="date" name="tgl_selesai" class="form-control" value="{{ old('tgl_selesai') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Deskripsi / Latar Belakang</label>
                        <textarea name="deskripsi" class="form-control" rows="3" placeholder="Deskripsi singkat program kerja...">{{ old('deskripsi') }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Tujuan</label>
                                <textarea name="tujuan" class="form-control" rows="3" placeholder="Tujuan audit/evaluasi...">{{ old('tujuan') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Ruang Lingkup</label>
                                <textarea name="ruang_lingkup" class="form-control" rows="3" placeholder="Ruang lingkup pemeriksaan...">{{ old('ruang_lingkup') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Metodologi</label>
                                <textarea name="metodologi" class="form-control" rows="3" placeholder="Metodologi yang digunakan...">{{ old('metodologi') }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="text-right mb-4">
                <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                    <i class="fas fa-save mr-2"></i> Simpan & Lanjutkan ke Detail
                </button>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap4', placeholder: '-- Pilih Surat Tugas --' });
        });
    </script>
@stop
