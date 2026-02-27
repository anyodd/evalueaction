@extends('adminlte::page')

@section('title', 'Edit Template PKA')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Edit Template Program Kerja</h1>
        <a href="{{ route('template-pka.show', $template->id) }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali ke Builder</a>
    </div>
@stop

@section('content')
    <div class="card shadow-sm border-0" style="border-radius: 10px;">
        <form action="{{ route('template-pka.update', $template->id) }}" method="POST">
            @csrf @method('PUT')
            <div class="card-body">
                <div class="form-group">
                    <label>Judul Template <span class="text-danger">*</span></label>
                    <input type="text" name="judul" class="form-control" value="{{ old('judul', $template->judul) }}" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" rows="2">{{ old('deskripsi', $template->deskripsi) }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Tujuan</label>
                            <textarea name="tujuan" class="form-control" rows="3">{{ old('tujuan', $template->tujuan) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Ruang Lingkup</label>
                            <textarea name="ruang_lingkup" class="form-control" rows="3">{{ old('ruang_lingkup', $template->ruang_lingkup) }}</textarea>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Metodologi</label>
                            <textarea name="metodologi" class="form-control" rows="3">{{ old('metodologi', $template->metodologi) }}</textarea>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" class="form-control" style="max-width: 200px;">
                        <option value="draft" {{ $template->status === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="published" {{ $template->status === 'published' ? 'selected' : '' }}>Published</option>
                    </select>
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary"><i class="fas fa-save mr-1"></i> Simpan</button>
            </div>
        </form>
    </div>
@stop
