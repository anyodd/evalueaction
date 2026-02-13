@extends('adminlte::page')

@section('title', 'Edit Template')

@section('content_header')
    <h1>Edit Template Kertas Kerja</h1>
@stop

@section('content')
    <div class="card">
        <form action="{{ route('templates.update', $template->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Nama Template</label>
                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" value="{{ old('nama', $template->nama) }}" required>
                    @error('nama') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Jenis Penugasan</label>
                    <select name="jenis_penugasan_id" class="form-control select2 @error('jenis_penugasan_id') is-invalid @enderror" required>
                        @foreach($jenisPenugasans as $jp)
                            <option value="{{ $jp->id }}" {{ $template->jenis_penugasan_id == $jp->id ? 'selected' : '' }}>{{ $jp->nama }}</option>
                        @endforeach
                    </select>
                    @error('jenis_penugasan_id') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <label>Tahun</label>
                    <input type="number" name="tahun" class="form-control @error('tahun') is-invalid @enderror" value="{{ old('tahun', $template->tahun) }}" required>
                    @error('tahun') <span class="invalid-feedback">{{ $message }}</span> @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="is_active" name="is_active" value="1" {{ $template->is_active ? 'checked' : '' }}>
                        <label class="custom-control-label" for="is_active">Aktif (Dapat digunakan di Surat Tugas)</label>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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
