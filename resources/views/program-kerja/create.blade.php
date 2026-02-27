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

            {{-- Template PKA Selector --}}
            @if(isset($pkaTemplates) && $pkaTemplates->count() > 0)
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title font-weight-bold text-info"><i class="fas fa-clipboard-list mr-2"></i>Template Program Kerja (Opsional)</h5>
                </div>
                <div class="card-body pt-0">
                    <div class="callout callout-info py-2">
                        <small><i class="fas fa-info-circle mr-1"></i> Jika dipilih, langkah-langkah dari template akan otomatis ter-load. Langkah template bersifat <strong>read-only</strong> — tidak dapat diedit atau dihapus oleh Perwakilan.</small>
                    </div>
                    <select name="template_id" class="form-control" style="max-width: 500px;">
                        <option value="">— Tanpa Template (isi manual) —</option>
                        @foreach($pkaTemplates as $tpl)
                            <option value="{{ $tpl->id }}" {{ old('template_id') == $tpl->id ? 'selected' : '' }}>
                                {{ $tpl->judul }} ({{ $tpl->langkah_count }} langkah)
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            {{-- Langkah-langkah --}}
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h5 class="card-title font-weight-bold text-primary mb-0"><i class="fas fa-list-ol mr-2"></i>Langkah-langkah Tambahan</h5>
                    <button type="button" class="btn btn-success btn-sm rounded-pill shadow-sm" id="btn-add-langkah">
                        <i class="fas fa-plus mr-1"></i> Tambah Langkah
                    </button>
                </div>
                <div class="card-body">
                    <div id="langkah-container">
                        {{-- Langkah rows will be added here --}}
                    </div>
                    <div id="empty-langkah" class="text-center text-muted py-4">
                        <i class="fas fa-arrow-up fa-2x mb-2 d-block"></i>
                        Klik <strong>"Tambah Langkah"</strong> untuk menambah tahapan tambahan.
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="text-right mb-4">
                <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                    <i class="fas fa-save mr-2"></i> Simpan Program Kerja
                </button>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('.select2').select2({ theme: 'bootstrap4', placeholder: '-- Pilih Surat Tugas --' });

            let langkahIndex = 0;

            function addLangkah(data = {}) {
                langkahIndex++;
                let html = `
                    <div class="langkah-row card border mb-3 animate__animated animate__fadeIn" data-index="${langkahIndex}" style="border-radius: 10px;">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge badge-primary p-2">Langkah ${langkahIndex}</span>
                                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-langkah" title="Hapus">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="form-group mb-2">
                                        <label class="small font-weight-bold">Judul Langkah <span class="text-danger">*</span></label>
                                        <input type="text" name="langkah[${langkahIndex}][judul]" class="form-control form-control-sm" 
                                               value="${data.judul || ''}" placeholder="Contoh: Pemeriksaan Dokumen Anggaran" required>
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
                                               value="${data.target_hari || ''}" min="1" placeholder="0">
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex align-items-end pb-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-move-up mr-1" title="Pindah Atas"><i class="fas fa-arrow-up"></i></button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary btn-move-down" title="Pindah Bawah"><i class="fas fa-arrow-down"></i></button>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold">Deskripsi / Prosedur Detail</label>
                                <textarea name="langkah[${langkahIndex}][deskripsi]" class="form-control form-control-sm" rows="2" 
                                          placeholder="Detail prosedur yang harus dilakukan...">${data.deskripsi || ''}</textarea>
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

            $('#btn-add-langkah').click(function() {
                addLangkah();
            });

            $(document).on('click', '.btn-remove-langkah', function() {
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
                if (prev.length) {
                    row.insertBefore(prev);
                    renumberLangkah();
                }
            });

            $(document).on('click', '.btn-move-down', function() {
                let row = $(this).closest('.langkah-row');
                let next = row.next('.langkah-row');
                if (next.length) {
                    row.insertAfter(next);
                    renumberLangkah();
                }
            });

            // Add one langkah by default
            addLangkah();
        });
    </script>
@stop
