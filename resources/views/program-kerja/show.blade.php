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

        {{-- Langkah-langkah Table --}}
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header bg-white border-0">
                <h5 class="card-title font-weight-bold text-primary mb-0">
                    <i class="fas fa-list-ol mr-2"></i>Langkah-langkah Audit
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="5%" class="text-center">#</th>
                                <th width="25%">Langkah</th>
                                <th width="12%">Prosedur</th>
                                <th width="15%">Ditugaskan ke</th>
                                <th width="13%">Kertas Kerja</th>
                                <th width="10%">Status</th>
                                <th width="10%">Target</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="langkah-tbody">
                            @forelse($pka->langkahRoot as $langkah)
                                @include('program-kerja.partials.langkah-row', ['langkah' => $langkah, 'level' => 0])
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        Belum ada langkah. <a href="{{ route('program-kerja.edit', $pka->id) }}">Tambah langkah</a>.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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

    {{-- Modal: Assign Langkah --}}
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

    {{-- Modal: Link Kertas Kerja --}}
    <div class="modal fade" id="linkKkModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content" style="border-radius: 15px;">
                <div class="modal-header border-0">
                    <h5 class="modal-title font-weight-bold"><i class="fas fa-link mr-2"></i>Hubungkan Kertas Kerja</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="link-langkah-id">
                    <div class="form-group">
                        <label class="font-weight-bold">Langkah</label>
                        <p id="link-langkah-judul" class="text-muted"></p>
                    </div>
                    <div class="form-group">
                        <label class="font-weight-bold">Kertas Kerja</label>
                        <select id="link-kk-id" class="form-control">
                            <option value="">-- Tanpa Link (Hapus) --</option>
                            @foreach($kertasKerjaList as $kk)
                                <option value="{{ $kk->id }}">{{ $kk->judul_kk }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4" id="btn-submit-link">
                        <i class="fas fa-link mr-1"></i> Hubungkan
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

        // ======== LINK KERTAS KERJA ========
        $(document).on('click', '.btn-link-kk', function() {
            let langkahId = $(this).data('langkah-id');
            let judul = $(this).data('langkah-judul');
            let currentKk = $(this).data('current-kk');
            $('#link-langkah-id').val(langkahId);
            $('#link-langkah-judul').text(judul);
            $('#link-kk-id').val(currentKk || '');
            $('#linkKkModal').modal('show');
        });

        $(document).on('click', '#btn-submit-link', function() {
            let btn = $(this);
            let langkahId = $('#link-langkah-id').val();

            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menghubungkan...');

            $.ajax({
                url: '/program-kerja/langkah/' + langkahId + '/link-kk',
                type: 'POST',
                data: {
                    _token: csrfToken,
                    kertas_kerja_id: $('#link-kk-id').val() || null
                },
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        $('#linkKkModal').modal('hide');
                        location.reload();
                    }
                },
                error: function(xhr) {
                    toastr.error(xhr.responseJSON?.message || 'Gagal menghubungkan.');
                },
                complete: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-link mr-1"></i> Hubungkan');
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
