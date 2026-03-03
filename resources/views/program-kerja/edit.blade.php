@extends('adminlte::page')

@section('title', 'Edit Program Kerja')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-edit text-warning mr-2"></i>Edit Program Kerja
            </h1>
            <a href="{{ route('program-kerja.show', $pka->id) }}" class="btn btn-default btn-sm shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        <form action="{{ route('program-kerja.update', $pka->id) }}" method="POST">
            @csrf
            @method('PUT')

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
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Surat Tugas <span class="text-danger">*</span></label>
                                <select name="st_id" class="form-control select2" required>
                                    <option value="">-- Pilih Surat Tugas --</option>
                                    @foreach($suratTugas as $st)
                                        <option value="{{ $st->id }}" {{ (old('st_id', $pka->st_id) == $st->id) ? 'selected' : '' }}>
                                            {{ $st->nomor_st }} — {{ $st->nama_objek }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Judul <span class="text-danger">*</span></label>
                                <input type="text" name="judul" class="form-control" value="{{ old('judul', $pka->judul) }}" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Status</label>
                                <select name="status" class="form-control">
                                    <option value="draft" {{ $pka->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                    <option value="active" {{ $pka->status == 'active' ? 'selected' : '' }}>Aktif</option>
                                    <option value="completed" {{ $pka->status == 'completed' ? 'selected' : '' }}>Selesai</option>
                                    <option value="archived" {{ $pka->status == 'archived' ? 'selected' : '' }}>Diarsipkan</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Tanggal Mulai</label>
                                <input type="date" name="tgl_mulai" class="form-control" value="{{ old('tgl_mulai', $pka->tgl_mulai ? $pka->tgl_mulai->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="font-weight-bold">Tanggal Selesai</label>
                                <input type="date" name="tgl_selesai" class="form-control" value="{{ old('tgl_selesai', $pka->tgl_selesai ? $pka->tgl_selesai->format('Y-m-d') : '') }}">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi', $pka->deskripsi) }}</textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Tujuan</label>
                                <textarea name="tujuan" class="form-control" rows="3">{{ old('tujuan', $pka->tujuan) }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Ruang Lingkup</label>
                                <textarea name="ruang_lingkup" class="form-control" rows="3">{{ old('ruang_lingkup', $pka->ruang_lingkup) }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="font-weight-bold">Metodologi</label>
                                <textarea name="metodologi" class="form-control" rows="3">{{ old('metodologi', $pka->metodologi) }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>



            {{-- Submit --}}
            <div class="d-flex justify-content-between mb-4">
                <form action="{{ route('program-kerja.destroy', $pka->id) }}" method="POST" id="form-delete" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    @if($pka->status === 'draft')
                        <button type="button" class="btn btn-outline-danger rounded-pill px-4" onclick="confirmDelete()">
                            <i class="fas fa-trash-alt mr-1"></i> Hapus PKA
                        </button>
                    @endif
                </form>
                <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap4' });
        });

        function confirmDelete() {
            Swal.fire({
                title: 'Hapus Program Kerja?',
                text: 'Data Program Kerja dan semua langkahnya akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-delete').submit();
                }
            });
        }
    </script>
@stop
