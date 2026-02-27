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

            {{-- Langkah-langkah --}}
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-primary mb-0"><i class="fas fa-list-ol mr-2"></i>Langkah-langkah</h5>
                    <button type="button" class="btn btn-success btn-sm rounded-pill shadow-sm" id="btn-add-langkah">
                        <i class="fas fa-plus mr-1"></i> Tambah Langkah
                    </button>
                </div>
                <div class="card-body">
                    <div id="langkah-container">
                        {{-- Pre-fill existing langkah --}}
                    </div>
                    <div id="empty-langkah" class="text-center text-muted py-4" style="display: none;">
                        <i class="fas fa-arrow-up fa-2x mb-2 d-block"></i>
                        Klik <strong>"Tambah Langkah"</strong> untuk menambah tahapan.
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

            let langkahIndex = 0;

            function addLangkah(data = {}) {
                langkahIndex++;
                let idField = data.id ? `<input type="hidden" name="langkah[${langkahIndex}][id]" value="${data.id}">` : '';
                let hasAssignments = data.has_assignments || false;
                let hasKk = data.has_kk || false;
                let deleteDisabled = (hasAssignments || hasKk) ? 'disabled title="Tidak dapat dihapus: memiliki penugasan/link KK"' : '';

                let html = `
                    <div class="langkah-row card border mb-3" data-index="${langkahIndex}" style="border-radius: 10px;">
                        <div class="card-body py-3">
                            ${idField}
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge badge-primary p-2">Langkah ${langkahIndex}</span>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-langkah" ${deleteDisabled}>
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Judul Langkah <span class="text-danger">*</span></label>
                                        <input type="text" name="langkah[${langkahIndex}][judul]" class="form-control form-control-sm" 
                                               value="${data.judul || ''}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Jenis Prosedur</label>
                                        <select name="langkah[${langkahIndex}][jenis_prosedur]" class="form-control form-control-sm">
                                            <option value="">-- Pilih --</option>
                                            <option value="wawancara" ${data.jenis_prosedur === 'wawancara' ? 'selected' : ''}>Wawancara</option>
                                            <option value="observasi" ${data.jenis_prosedur === 'observasi' ? 'selected' : ''}>Observasi</option>
                                            <option value="inspeksi_dokumen" ${data.jenis_prosedur === 'inspeksi_dokumen' ? 'selected' : ''}>Inspeksi Dokumen</option>
                                            <option value="analisis_data" ${data.jenis_prosedur === 'analisis_data' ? 'selected' : ''}>Analisis Data</option>
                                            <option value="konfirmasi" ${data.jenis_prosedur === 'konfirmasi' ? 'selected' : ''}>Konfirmasi</option>
                                            <option value="rekalkulasi" ${data.jenis_prosedur === 'rekalkulasi' ? 'selected' : ''}>Rekalkulasi</option>
                                            <option value="lainnya" ${data.jenis_prosedur === 'lainnya' ? 'selected' : ''}>Lainnya</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Target (hari)</label>
                                        <input type="number" name="langkah[${langkahIndex}][target_hari]" class="form-control form-control-sm" 
                                               value="${data.target_hari || ''}" min="1">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end pb-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-move-up mr-1" title="Pindah Atas"><i class="fas fa-arrow-up"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-move-down" title="Pindah Bawah"><i class="fas fa-arrow-down"></i></button>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">Deskripsi</label>
                                <textarea name="langkah[${langkahIndex}][deskripsi]" class="form-control form-control-sm" rows="2">${data.deskripsi || ''}</textarea>
                            </div>
                        </div>
                    </div>
                `;
                $('#langkah-container').append(html);
                $('#empty-langkah').hide();
                renumberLangkah();
            }

            function renumberLangkah() {
                $('#langkah-container .langkah-row').each(function(idx) {
                    $(this).find('.badge-primary').first().text('Langkah ' + (idx + 1));
                });
            }

            $('#btn-add-langkah').click(function() { addLangkah(); });

            $(document).on('click', '.btn-remove-langkah', function() {
                if ($(this).prop('disabled')) return;
                $(this).closest('.langkah-row').fadeOut(300, function() {
                    $(this).remove();
                    renumberLangkah();
                    if ($('#langkah-container .langkah-row').length === 0) {
                        $('#empty-langkah').show();
                    }
                });
            });

            $(document).on('click', '.btn-move-up', function() {
                let row = $(this).closest('.langkah-row');
                let prev = row.prev('.langkah-row');
                if (prev.length) { row.insertBefore(prev); renumberLangkah(); }
            });

            $(document).on('click', '.btn-move-down', function() {
                let row = $(this).closest('.langkah-row');
                let next = row.next('.langkah-row');
                if (next.length) { row.insertAfter(next); renumberLangkah(); }
            });

            // Pre-fill existing langkah
            @foreach($pka->langkahRoot as $langkah)
                addLangkah({
                    id: {{ $langkah->id }},
                    judul: @json($langkah->judul),
                    deskripsi: @json($langkah->deskripsi),
                    jenis_prosedur: @json($langkah->jenis_prosedur),
                    target_hari: @json($langkah->target_hari),
                    has_assignments: {{ $langkah->assignments()->count() > 0 ? 'true' : 'false' }},
                    has_kk: {{ $langkah->kertas_kerja_id ? 'true' : 'false' }}
                });
            @endforeach

            if ($('#langkah-container .langkah-row').length === 0) {
                $('#empty-langkah').show();
            }
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
