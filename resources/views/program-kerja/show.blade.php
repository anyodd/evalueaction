@extends('adminlte::page')

@section('title', 'Detail Program Kerja')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1 class="m-0 text-dark font-weight-bold">
                    <i class="fas fa-tasks text-primary mr-2"></i>{{ $pka->judul }}
                </h1>
                <small class="text-muted">
                    <i class="fas fa-file-alt mr-1"></i>ST: {{ $pka->suratTugas->nomor_st ?? '-' }}
                    · Objek: {{ $pka->suratTugas->nama_objek ?? '-' }}
                    · <span class="badge {{ $pka->status_badge }}">{{ $pka->status_label }}</span>
                </small>
            </div>
            <div>
                <a href="{{ route('program-kerja.print', $pka->id) }}" class="btn btn-outline-secondary btn-sm shadow-sm mr-1" target="_blank">
                    <i class="fas fa-print mr-1"></i> Cetak
                </a>
                @if($canManage)
                    <a href="{{ route('program-kerja.edit', $pka->id) }}" class="btn btn-outline-warning btn-sm shadow-sm mr-1">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                @endif
                <a href="{{ route('program-kerja.index') }}" class="btn btn-default btn-sm shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">

        {{-- Progress Overview --}}
        <div class="card shadow-sm border-0 mb-3" style="border-radius: 15px;">
            <div class="card-body py-3">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        @php $progress = $pka->progressPercentage(); @endphp
                        <div class="text-center">
                            <h2 class="font-weight-bold text-primary mb-0" id="progress-value">{{ $progress }}%</h2>
                            <small class="text-muted">Progres Keseluruhan</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="progress mb-2" style="height: 25px; border-radius: 12px;">
                            <div class="progress-bar {{ $progress >= 100 ? 'bg-success' : ($progress >= 50 ? 'bg-primary' : 'bg-warning') }}" 
                                 id="progress-bar"
                                 role="progressbar" 
                                 style="width: {{ $progress }}%; border-radius: 12px; transition: width 0.5s ease;"
                                 aria-valuenow="{{ $progress }}">
                                <span id="progress-text">{{ $pka->langkah->where('status', 'completed')->count() }}/{{ $pka->langkah->count() }} langkah selesai</span>
                            </div>
                        </div>
                        <div class="d-flex justify-content-between text-sm">
                            <span><i class="fas fa-circle text-secondary mr-1"></i>Pending: <strong>{{ $pka->langkah->where('status', 'pending')->count() }}</strong></span>
                            <span><i class="fas fa-circle text-info mr-1"></i>Sedang: <strong>{{ $pka->langkah->where('status', 'in_progress')->count() }}</strong></span>
                            <span><i class="fas fa-circle text-success mr-1"></i>Selesai: <strong>{{ $pka->langkah->where('status', 'completed')->count() }}</strong></span>
                            <span><i class="fas fa-circle text-dark mr-1"></i>Dilewati: <strong>{{ $pka->langkah->where('status', 'skipped')->count() }}</strong></span>
                        </div>
                    </div>
                    <div class="col-md-3 text-center">
                        <small class="text-muted d-block">Periode</small>
                        <strong>{{ $pka->tgl_mulai ? $pka->tgl_mulai->format('d/m/Y') : '-' }}</strong>
                        <span class="text-muted">s.d.</span>
                        <strong>{{ $pka->tgl_selesai ? $pka->tgl_selesai->format('d/m/Y') : '-' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Info Cards --}}
        @if($pka->tujuan || $pka->ruang_lingkup || $pka->metodologi || $pka->deskripsi)
        <div class="row mb-3">
            @if($pka->tujuan)
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                    <div class="card-body">
                        <h6 class="font-weight-bold text-primary"><i class="fas fa-bullseye mr-2"></i>Tujuan</h6>
                        <p class="text-sm mb-0">{{ $pka->tujuan }}</p>
                    </div>
                </div>
            </div>
            @endif
            @if($pka->ruang_lingkup)
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                    <div class="card-body">
                        <h6 class="font-weight-bold text-primary"><i class="fas fa-search mr-2"></i>Ruang Lingkup</h6>
                        <p class="text-sm mb-0">{{ $pka->ruang_lingkup }}</p>
                    </div>
                </div>
            </div>
            @endif
            @if($pka->metodologi)
            <div class="col-md-4">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 12px;">
                    <div class="card-body">
                        <h6 class="font-weight-bold text-primary"><i class="fas fa-cogs mr-2"></i>Metodologi</h6>
                        <p class="text-sm mb-0">{{ $pka->metodologi }}</p>
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- Hierarki Kertas Kerja & Langkah Kerja --}}
        <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title font-weight-bold text-primary mb-0">
                    <i class="fas fa-list-ol mr-2"></i>Langkah-langkah Audit Berdasarkan Kertas Kerja
                </h5>
                @if($canManage)
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill shadow-sm btn-add-langkah-modal" data-param-id="" data-param-uraian="">
                        <i class="fas fa-plus mr-1"></i> Tambah Prosedur (Diluar Template)
                    </button>
                @endif
            </div>
            
            <div class="card-body p-0 border-top bg-light">
                @if($templateIndicators->count() > 0)
                    <div class="accordion" id="accordionKertasKerja">
                        @foreach($templateIndicators as $index1 => $aspek)
                            <div class="card border-0 mb-1">
                                <div class="card-header bg-white shadow-sm" id="headingAspek{{ $aspek->id }}">
                                    <h2 class="mb-0">
                                        <button class="btn btn-link btn-block text-left font-weight-bold text-dark text-decoration-none" type="button" data-toggle="collapse" data-target="#collapseAspek{{ $aspek->id }}">
                                            <i class="fas fa-folder-open mr-2 text-warning"></i>Aspek: {{ $aspek->uraian }}
                                            <i class="fas fa-chevron-down float-right text-muted mt-1" style="font-size: 0.8rem;"></i>
                                        </button>
                                    </h2>
                                </div>
                                <div id="collapseAspek{{ $aspek->id }}" class="collapse show" data-parent="#accordionKertasKerja">
                                    <div class="card-body p-0">
                                        @foreach($aspek->children as $index2 => $indikator)
                                            <div class="bg-white border-bottom p-3 pl-4">
                                                <h6 class="font-weight-bold mb-3"><i class="fas fa-layer-group text-info mr-2"></i>Indikator: {{ $indikator->uraian }}</h6>
                                                
                                                @foreach($indikator->children as $index3 => $parameter)
                                                    <div class="ml-4 mt-2 p-3 border border-primary rounded bg-light shadow-sm">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <div>
                                                                <h6 class="font-weight-bold text-primary mb-1"><i class="fas fa-check-square mr-2"></i>Parameter: {{ $parameter->uraian }}</h6>
                                                                @if($parameter->criteria && $parameter->criteria->count() > 0)
                                                                    <div class="ml-4 text-sm text-muted mt-2">
                                                                        <a class="text-info text-decoration-none collapsed" data-toggle="collapse" href="#kriteria-{{ $parameter->id }}" role="button" aria-expanded="false" aria-controls="kriteria-{{ $parameter->id }}">
                                                                            <i class="fas fa-info-circle mr-1"></i> Tampilkan Kriteria Pemenuhan
                                                                        </a>
                                                                        <div class="collapse mt-2" id="kriteria-{{ $parameter->id }}">
                                                                            <div class="p-3 bg-white border rounded shadow-sm">
                                                                                <strong class="text-dark d-block mb-2">Pedoman Kriteria Pemenuhan:</strong>
                                                                                <ul class="mb-0 pl-3 text-dark">
                                                                                    @foreach($parameter->criteria as $kriteria)
                                                                                        <li class="mb-1 pb-1 border-bottom" style="border-bottom-color: #f0f0f0 !important;">{{ $kriteria->uraian }}</li>
                                                                                    @endforeach
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                            @if($canManage)
                                                                <div class="d-flex align-items-center">
                                                                    <select class="form-control form-control-sm mr-2 bulk-assign-select shadow-sm" data-param-id="{{ $parameter->id }}" title="Tugaskan semua prosedur di parameter ini sekaligus" style="width: auto; min-width: 180px;">
                                                                        <option value="">-- Tugaskan Semua Ke --</option>
                                                                        @foreach($teamMembers as $member)
                                                                            <option value="{{ $member->user_id }}">{{ $member->user->name }} ({{ $member->role_dalam_tim }})</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <button type="button" class="btn btn-sm btn-success shadow-sm btn-add-langkah-modal text-nowrap" data-param-id="{{ $parameter->id }}" data-param-uraian="{{ $parameter->uraian }}">
                                                                        <i class="fas fa-plus mr-1"></i> Tambah Prosedur
                                                                    </button>
                                                                </div>
                                                            @endif
                                                        </div>

                                                        {{-- Tabel Langkah Kerja untuk Parameter ini --}}
                                                        @php
                                                            $langkahForParam = $langkahByIndicator->get($parameter->id, collect());
                                                        @endphp
                                                        @if($langkahForParam->count() > 0)
                                                            <div class="table-responsive mt-3 bg-white rounded shadow-sm border">
                                                                <table class="table table-sm table-hover mb-0 text-sm">
                                                                    <thead class="bg-primary text-white">
                                                                        <tr>
                                                                            <th width="5%" class="text-center">#</th>
                                                                            <th width="40%">Prosedur / Langkah Kerja</th>
                                                                            <th width="20%">Ditugaskan Ke</th>
                                                                            <th width="20%">Status</th>
                                                                            <th width="15%">Aksi</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($langkahForParam as $idx => $langkah)
                                                                            @include('program-kerja.partials.langkah-row-new', ['langkah' => $langkah, 'nomor' => $idx + 1])
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        @else
                                                            <div class="alert alert-secondary text-center py-2 mt-3 mb-0" style="font-size: 0.85rem;">
                                                                <i class="fas fa-info-circle mr-1"></i> Belum ada prosedur langkah kerja untuk memenuhi parameter ini.
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 bg-white">
                        <i class="fas fa-file-alt fa-3x mb-3 text-light"></i>
                        <h5>Belum Ada Hierarki Target Kertas Kerja</h5>
                        <p class="text-muted">Surat Tugas ini tidak dikaitkan dengan Template Kertas Kerja atau belum ada strukturnya.</p>
                    </div>
                @endif
                
                {{-- Langkah Non-Mapping (Bebas) --}}
                @php
                    $langkahBebas = $langkahByIndicator->get('', collect());
                @endphp
                @if($langkahBebas->count() > 0)
                    <div class="p-4 bg-white border-top">
                        <h6 class="font-weight-bold text-danger"><i class="fas fa-paperclip mr-2"></i>Langkah Kerja Tambahan (Tidak Terikat Parameter)</h6>
                        <div class="table-responsive mt-3 bg-white rounded shadow-sm border border-danger">
                            <table class="table table-sm table-hover mb-0 text-sm">
                                <thead class="bg-danger text-white">
                                    <tr>
                                        <th width="5%" class="text-center">#</th>
                                        <th width="40%">Prosedur / Langkah Kerja</th>
                                        <th width="20%">Ditugaskan Ke</th>
                                        <th width="20%">Status</th>
                                        <th width="15%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($langkahBebas as $idx => $langkah)
                                        @include('program-kerja.partials.langkah-row-new', ['langkah' => $langkah, 'nomor' => $idx + 1])
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Tim --}}
        <div class="card shadow-sm border-0 mt-3" style="border-radius: 15px;">
            <div class="card-header bg-white border-0">
                <h5 class="card-title font-weight-bold text-primary mb-0">
                    <i class="fas fa-users mr-2"></i>Susunan Tim
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($teamMembers as $member)
                        <div class="col-md-3 mb-2">
                            <div class="card border shadow-sm" style="border-radius: 10px;">
                                <div class="card-body py-2 px-3 d-flex align-items-center">
                                    <img src="{{ $member->user->adminlte_image() }}" alt="" class="rounded-circle mr-2" width="32" height="32">
                                    <div>
                                        <strong class="d-block text-sm">{{ $member->user->name ?? '-' }}</strong>
                                        <small class="text-muted">{{ $member->role_dalam_tim }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@stop

    {{-- Modal: Tambah Langkah --}}
    <div class="modal fade" id="addLangkahModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('program-kerja.langkah.store', $pka->id) }}" method="POST">
                @csrf
                <div class="modal-content" style="border-radius: 15px;">
                    <div class="modal-header border-0 bg-light">
                        <h5 class="modal-title font-weight-bold"><i class="fas fa-plus-circle mr-2 text-primary"></i>Tambah Langkah Kerja Baru</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="template_indicator_id" id="add-langkah-param-id">
                        
                        <div id="param-context-box" class="alert alert-info" style="display: none;">
                            <i class="fas fa-info-circle mr-1"></i> <strong>Mengisi Parameter:</strong> <span id="add-langkah-param-uraian"></span>
                        </div>

                        <div class="form-group">
                            <label class="font-weight-bold">Judul Langkah / Prosedur Utama <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required placeholder="Contoh: Lakukan Pengujian ...">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Jenis Prosedur</label>
                                    <select name="jenis_prosedur" class="form-control">
                                        <option value="">-- Pilih --</option>
                                        <option value="wawancara">Wawancara</option>
                                        <option value="observasi">Observasi</option>
                                        <option value="inspeksi_dokumen">Inspeksi Dokumen</option>
                                        <option value="analisis_data">Analisis Data</option>
                                        <option value="konfirmasi">Konfirmasi</option>
                                        <option value="rekalkulasi">Rekalkulasi</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="font-weight-bold">Target Waktu (Hari)</label>
                                    <input type="number" name="target_hari" class="form-control" placeholder="Opsional" min="1">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold">Deskripsi Tambahan <small class="text-muted">(Opsional)</small></label>
                            <textarea name="deskripsi" class="form-control" rows="3" placeholder="Detail langkah jika diperlukan..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4">
                            <i class="fas fa-save mr-1"></i> Simpan Langkah
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="modal fade" id="assignModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-user-plus mr-2"></i>Tugaskan Langkah</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="assign-langkah-id">
                    <div class="form-group">
                        <label class="font-weight-bold">Langkah</label>
                        <p id="assign-langkah-judul" class="text-muted"></p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Anggota Tim <span class="text-danger">*</span></label>
                        <select id="assign-user-id" class="form-control">
                            <option value="">-- Pilih Anggota --</option>
                            @foreach($teamMembers as $member)
                                <option value="{{ $member->user_id }}">{{ $member->user->name }} ({{ $member->role_dalam_tim }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Deadline</label>
                        <input type="date" id="assign-deadline" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Catatan / Instruksi</label>
                        <textarea id="assign-catatan" class="form-control" rows="2" placeholder="Instruksi tambahan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="btn-submit-assign">
                        <i class="fas fa-check mr-1"></i> Tugaskan
                    </button>
                </div>
            </div>
        </div>
    </div>



    {{-- Modal: Update Status --}}
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-sync-alt mr-2"></i>Update Status Langkah</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="status-langkah-id">
                    <div class="form-group">
                        <label class="font-weight-bold">Langkah</label>
                        <p id="status-langkah-judul" class="text-muted"></p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Status Baru <span class="text-danger">*</span></label>
                        <select id="status-value" class="form-control">
                            <option value="pending">Pending</option>
                            <option value="in_progress">Sedang Dikerjakan</option>
                            <option value="completed">Selesai</option>
                            <option value="skipped">Dilewati</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Catatan Hasil</label>
                        <textarea id="status-catatan" class="form-control" rows="3" placeholder="Catatan hasil pelaksanaan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="btn-submit-status">
                        <i class="fas fa-check mr-1"></i> Update
                    </button>
                </div>
            </div>
        </div>
    </div>
@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        /* Fix Toastr z-index to show above Bootstrap Modals */
        #toast-container {
            z-index: 1060 !important; 
        }
        
        /* Fix Bootstrap Modal z-index and backdrop for AdminLTE */
        .modal {
            z-index: 1050 !important;
        }
        .modal-backdrop {
            z-index: 1040 !important;
        }
    </style>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        let csrfToken = $('meta[name="csrf-token"]').attr('content');

        // ======== BULK ASSIGN ========
        $(document).on('change', '.bulk-assign-select', function() {
            let userId = $(this).val();
            let paramId = $(this).data('param-id');
            let selectElem = $(this);
            
            if(!userId) return;
            
            Swal.fire({
                title: 'Tugaskan Massal?',
                text: "Anda akan menugaskan seluruh langkah kerja pada parameter ini kepada anggota tersebut.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Tugaskan!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    selectElem.prop('disabled', true);
                    
                    $.ajax({
                        url: '{{ route("program-kerja.bulk-assign") }}',
                        method: 'POST',
                        data: {
                            _token: csrfToken,
                            program_kerja_id: '{{ $pka->id }}',
                            parameter_id: paramId,
                            user_id: userId
                        },
                        success: function(res) {
                            toastr.success(res.message);
                            setTimeout(() => location.reload(), 1000);
                        },
                        error: function(err) {
                            selectElem.prop('disabled', false);
                            selectElem.val(''); // reset
                            let msg = err.responseJSON && err.responseJSON.message ? err.responseJSON.message : 'Gagal melakukan penugasan massal.';
                            toastr.error(msg);
                        }
                    });
                } else {
                    selectElem.val(''); // Reset
                }
            });
        });

        // ======== TAMBAH LANGKAH MODAL ========
        $(document).on('click', '.btn-add-langkah-modal', function() {
            let paramId = $(this).data('param-id');
            let paramUraian = $(this).data('param-uraian');
            
            $('#add-langkah-param-id').val(paramId);
            
            if (paramId) {
                $('#add-langkah-param-uraian').text(paramUraian);
                $('#param-context-box').show();
            } else {
                $('#param-context-box').hide();
            }
            
            $('#addLangkahModal').modal('show');
        });

        // ======== ASSIGN LANGKAH ========
        $(document).on('click', '.btn-assign', function() {
            let langkahId = $(this).data('langkah-id');
            let judul = $(this).data('langkah-judul');
            $('#assign-langkah-id').val(langkahId);
            $('#assign-langkah-judul').text(judul);
            $('#assign-user-id').val('');
            $('#assign-deadline').val('');
            $('#assign-catatan').val('');
            $('#assignModal').modal('show');
        });

        $(document).on('click', '#btn-submit-assign', function() {
            let btn = $(this);
            let langkahId = $('#assign-langkah-id').val();
            let userId = $('#assign-user-id').val();

            if (!userId) {
                toastr.warning('Pilih anggota tim terlebih dahulu.');
                return;
            }

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

            $.ajax({
                url: '{{ route("program-kerja.assign") }}',
                type: 'POST',
                data: {
                    _token: csrfToken,
                    pk_langkah_id: langkahId,
                    user_id: userId,
                    catatan: $('#assign-catatan').val(),
                    tgl_deadline: $('#assign-deadline').val()
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#assignModal').modal('hide');
                        location.reload();
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Gagal menugaskan.');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Tugaskan');
                }
            });
        });

        // ======== UPDATE STATUS ========
        $(document).on('click', '.btn-status', function() {
            let langkahId = $(this).data('langkah-id');
            let judul = $(this).data('langkah-judul');
            let currentStatus = $(this).data('langkah-status');
            $('#status-langkah-id').val(langkahId);
            $('#status-langkah-judul').text(judul);
            $('#status-value').val(currentStatus);
            $('#status-catatan').val('');
            $('#statusModal').modal('show');
        });

        $(document).on('click', '#btn-submit-status', function() {
            let btn = $(this);
            let langkahId = $('#status-langkah-id').val();
            let status = $('#status-value').val();

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Updating...');

            $.ajax({
                url: '/program-kerja/langkah/' + langkahId + '/status',
                type: 'POST',
                data: {
                    _token: csrfToken,
                    status: status,
                    catatan_hasil: $('#status-catatan').val()
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#statusModal').modal('hide');

                        // Update progress bar
                        let pct = response.pka_progress;
                        $('#progress-value').text(pct + '%');
                        $('#progress-bar').css('width', pct + '%').attr('aria-valuenow', pct);
                        
                        location.reload();
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Gagal update status.');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-check mr-1"></i> Update');
                }
            });
        });



        // ======== REMOVE ASSIGNMENT ========
        $(document).on('click', '.btn-remove-assignment', function() {
            let assignmentId = $(this).data('assignment-id');
            let btn = $(this);

            Swal.fire({
                title: 'Hapus Penugasan?',
                text: 'Penugasan ini akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route("program-kerja.remove-assignment") }}',
                        type: 'POST',
                        data: { _token: csrfToken, assignment_id: assignmentId },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                location.reload();
                            }
                        },
                        error: function() {
                            toastr.error('Gagal menghapus penugasan.');
                        }
                    });
                }
            });
        });
    </script>
@stop
