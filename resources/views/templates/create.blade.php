@extends('adminlte::page')

@section('title', 'Tambah Template')

@section('content_header')
    <h1>Tambah Template Kertas Kerja</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('templates.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="form-group">
                    <label>Nama Template</label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" required placeholder="Contoh: Kertas Kerja Manajemen Risiko 2026">
                    @error('nama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Jenis Penugasan</label>
                    <select name="jenis_penugasan_id" class="form-control select2 @error('jenis_penugasan_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Jenis --</option>
                        @foreach($jenisPenugasans as $jp)
                            <option value="{{ $jp->id }}">{{ $jp->nama }}</option>
                        @endforeach
                    </select>
                    @error('jenis_penugasan_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Tahun</label>
                    <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror" value="{{ date('Y') }}" required>
                    @error('tahun') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" checked>
                        <label class="custom-control-label" for="is_active">Aktif (Dapat digunakan di Surat Tugas)</label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan Header</button>
                <a href="{{ route('templates.index') }}" class="btn btn-default">Batal</a>
            </div>
        </form>
    </div>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@stop
