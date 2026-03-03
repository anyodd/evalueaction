@extends('adminlte::page')

@section('title', 'Isi Kertas Kerja')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <h1 class="m-0 text-dark font-weight-bold">Isi Kertas Kerja 
            @if(!$canEdit) 
                <span class="badge badge-secondary shadow-sm ml-2" style="font-size: 0.5em; vertical-align: middle;">
                    <i class="fas fa-eye mr-1"></i> Mode View / Review
                </span> 
            @endif
        </h1>
        <div class="d-flex justify-content-between align-items-center">
            <small class="text-muted">{{ $kertasKerja->judul_kk }}</small>
            <div class="d-flex align-items-center">
                <a href="{{ route('kertas-kerja.export-excel', $kertasKerja->id) }}" class="btn btn-outline-success btn-sm shadow-sm mr-2">
                    <i class="fas fa-file-excel mr-1"></i> Export Excel
                </a>
                @if($canEdit)
                    <button type="button" class="btn btn-outline-primary btn-sm shadow-sm mr-2" data-toggle="modal" data-target="#modal-import-excel">
                        <i class="fas fa-file-import mr-1"></i> Import Excel
                    </button>
                @endif
                <a href="{{ route('kertas-kerja.index') }}" class="btn btn-default btn-sm shadow-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
                </a>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        {{-- Real-time Score Dashboard Panel --}}
        @php
            $dashMetode = $kertasKerja->template->metode_penilaian ?? 'tally';
            $dashIsLevel = in_array($dashMetode, ['building_block', 'criteria_fulfillment']);
        @endphp
        <div class="card shadow-sm border-0 mb-3" id="score-dashboard" style="border-radius: 12px; position: sticky; top: 57px; z-index: 100;" data-metode="{{ $dashMetode }}">
            <div class="card-body py-2">
                <div class="d-flex justify-content-between align-items-center flex-wrap">
                    <div class="d-flex align-items-center flex-wrap">
                        <small class="text-muted mr-2 font-weight-bold"><i class="fas fa-chart-bar mr-1"></i>Skor:</small>
                        @foreach($indicators as $header)
                            @php
                                $aspectAnswer = $kertasKerja->answers->where('indikator_id', $header->id)->first();
                                $aspectScore = $aspectAnswer ? $aspectAnswer->nilai : 0;
                            @endphp
                            <span class="badge badge-light border mr-2 p-2 mb-1" id="score-aspect-{{ $header->id }}">
                                {{ \Illuminate\Support\Str::limit($header->uraian, 18) }}:
                                <strong>{{ $dashIsLevel ? number_format((float)$aspectScore, 2) : number_format((float)$aspectScore, 1) . '%' }}</strong>
                            </span>
                        @endforeach
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="text-muted mr-2 font-weight-bold">Skor Akhir:</span>
                        @php
                            $finalScore = $kertasKerja->nilai_akhir ?? 0;
                            if ($dashIsLevel) {
                                $finalBadgeClass = $finalScore >= 4 ? 'badge-success' : ($finalScore >= 3 ? 'badge-primary' : ($finalScore >= 2 ? 'badge-warning' : 'badge-danger'));
                            } else {
                                $finalBadgeClass = $finalScore >= 80 ? 'badge-success' : ($finalScore >= 60 ? 'badge-primary' : ($finalScore >= 40 ? 'badge-warning' : 'badge-danger'));
                            }
                        @endphp
                        <span class="badge {{ $finalBadgeClass }} p-2 px-3" id="score-final" style="font-size: 1.1em">
                            {{ number_format((float)$finalScore, 2) }}{{ $dashIsLevel ? '' : '%' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <form action="{{ route('kertas-kerja.update', $kertasKerja->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Loop through Headers (Dimensions) --}}
                    @foreach($indicators as $header)
                        <div class="card mb-4 border-left-primary shadow-sm">
                            <div class="card-header bg-light">
                                <h5 class="card-title font-weight-bold text-primary m-0">{{ $header->uraian }}</h5>
                            </div>
                            <div class="card-body">
                                {{-- Loop through Children (Indicators) --}}
                                @foreach($header->children as $child)
                                    @if($child->children->count() > 0)
                                        {{-- Level 2 is a Grouping Header (e.g. for Risk Management) --}}
                                        <div class="mb-4 pl-3 border-left">
                                            <h6 class="font-weight-bold text-secondary border-bottom pb-2 mb-3">{{ $child->uraian }}</h6>
                                            {{-- Loop through Grandchildren (Parameters) --}}
                                            @foreach($child->children as $grandChild)
                                                 @include('kertas-kerja.partials.input', ['question' => $grandChild])
                                            @endforeach
                                        </div>
                                    @else
                                        {{-- Level 2 is a Question directly --}}
                                        @include('kertas-kerja.partials.input', ['question' => $child])
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                </div>
                <div class="card-footer bg-white border-0 text-right pb-4">
                    @if($canEdit)
                        <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                            <i class="fas fa-save mr-2"></i> Simpan Kertas Kerja
                        </button>
                    @elseif(isset($isQaMode) && $isQaMode)
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <div>
                                @if(isset($isQaFinal) && $isQaFinal)
                                    <div class="alert alert-success d-inline-block rounded-pill px-4 shadow-sm mb-0">
                                        <i class="fas fa-check-circle mr-2"></i> QA Selesai (Final)
                                    </div>
                                @else
                                    <div class="alert alert-warning d-inline-block rounded-pill px-4 shadow-sm mb-0">
                                        <i class="fas fa-pen mr-2"></i> Mode QA (Draft)
                                    </div>
                                @endif
                            </div>
                            
                            @if(isset($canEditQa) && $canEditQa && !$isQaFinal)
                                <button type="button" class="btn btn-success rounded-pill px-5 shadow-sm btn-finalize-qa">
                                    <i class="fas fa-check-double mr-2"></i> Finalkan QA
                                </button>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-info d-inline-block rounded-pill px-4 shadow-sm mb-0">
                            <i class="fas fa-info-circle mr-2"></i> Anda dalam mode **Read-Only**. Hubungi Ketua Tim atau Dalnis jika ada data yang perlu diubah.
                        </div>
                    @endif
                </div>
            </div>
        </form>

        {{-- Jejak Rekam (Audit Trail) --}}
        <div class="card shadow-sm border-0 mt-4" style="border-radius: 15px;">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="card-title font-weight-bold text-dark m-0">
                    <i class="fas fa-history text-muted mr-2"></i> Jejak Rekam (Audit Trail)
                </h5>
            </div>
            <div class="card-body">
                @if($kertasKerja->audits && $kertasKerja->audits->count() > 0)
                    <div class="timeline mt-3">
                        @foreach($kertasKerja->audits->sortByDesc('created_at') as $audit)
                            <div>
                                @php
                                    $iconClass = 'fas fa-info-circle bg-secondary';
                                    if(strtolower($audit->action) == 'simpan') $iconClass = 'fas fa-save bg-primary';
                                    if(strtolower($audit->action) == 'kirim') $iconClass = 'fas fa-paper-plane bg-info';
                                    if(strtolower($audit->action) == 'setuju') $iconClass = 'fas fa-check bg-success';
                                    if(strtolower($audit->action) == 'tolak') $iconClass = 'fas fa-times bg-danger';
                                @endphp
                                <i class="{{ $iconClass }}"></i>
                                <div class="timeline-item shadow-sm" style="border-radius: 8px;">
                                    <span class="time"><i class="fas fa-clock mr-1"></i> {{ $audit->created_at->format('d M Y H:i') }}</span>
                                    <h3 class="timeline-header font-weight-bold" style="font-size: 0.95rem;">
                                        {{ $audit->user ? $audit->user->name : 'Sistem' }} 
                                        <span class="text-muted font-weight-normal">mengubah status / skor</span>
                                        <span class="badge badge-light border ml-1">{{ $audit->action }}</span>
                                    </h3>
                                    @if($audit->description)
                                        <div class="timeline-body py-2 text-muted" style="font-size: 0.9rem;">
                                            {{ $audit->description }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                        <div>
                            <i class="fas fa-clock bg-gray"></i>
                        </div>
                    </div>
                @else
                    <div class="alert alert-light border text-center text-muted col-12 py-4">
                        <i class="fas fa-inbox fa-2x mb-2 text-light-gray"></i><br>
                        Belum ada jejak rekam (Audit Trail) untuk dokumen ini.
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Hidden Form for Finalize QA --}}
    <form id="form-finalize-qa" action="{{ route('kertas-kerja.finalize-qa', $kertasKerja->id) }}" method="POST" style="display: none;">
        @csrf
    </form>

    {{-- Modal Import Excel --}}
    @if($canEdit)
        <div class="modal fade" id="modal-import-excel" tabindex="-1" role="dialog" aria-labelledby="modalImportLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <form action="{{ route('kertas-kerja.import-excel', $kertasKerja->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-content" style="border-radius: 15px;">
                        <div class="modal-header border-0">
                            <h5 class="modal-title font-weight-bold" id="modalImportLabel">Import Kertas Kerja dari Excel</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info py-2" style="font-size: 0.9rem;">
                                <i class="fas fa-info-circle mr-2"></i> 
                                Gunakan file hasil <strong>Export Excel</strong> untuk memastikan format dan ID indikator sudah sesuai.
                            </div>
                            <div class="form-group mt-3">
                                <label for="file_excel">Pilih File Excel (.xlsx, .xls)</label>
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="file_excel" name="file_excel" accept=".xlsx, .xls" required>
                                    <label class="custom-file-label" for="file_excel">Pilih file...</label>
                                </div>
                            </div>
                            <p class="text-xs text-muted mt-2">
                                <span class="text-danger">*</span> Sistem akan memperbarui nilai, catatan, dan link bukti berdasarkan ID unik di kolom pertama Excel.
                            </p>
                        </div>
                        <div class="modal-footer border-0">
                            <button type="button" class="btn btn-light rounded-pill px-4" data-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-primary rounded-pill px-4">
                                <i class="fas fa-upload mr-2"></i> Mulai Import
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.btn-finalize-qa').click(function(e) {
                e.preventDefault();
                Swal.fire({
                    title: 'Finalisasi QA?',
                    text: "Apakah Anda yakin ingin memfinalisasi QA? Data tidak dapat diubah lagi setelah ini.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Ya, Finalkan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('form-finalize-qa').submit();
                    }
                });
            });

            // Criteria Radio Logic
            $(document).on('change', '.criteria-radio', function() {
                let val = $(this).val();
                let targetId = $(this).data('target'); // #file-123
                let fileInput = $(targetId);
                let linkInput = fileInput.next('input[type="text"]');

                if (val === 'none') {
                    fileInput.prop('disabled', true);
                    linkInput.prop('disabled', true);
                } else {
                    fileInput.prop('disabled', false);
                    linkInput.prop('disabled', false);
                }
            });

            // Reference Fetching Logic
            $('.fetch-ref').click(function() {
                let btn = $(this);
                let indicatorId = btn.data('id');
                let refJenisId = btn.data('ref-jenis');
                let tahun = btn.data('tahun');
                let input = $('#ref-val-' + indicatorId);

                // Disable button
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                // AJAX Call (Mocking for now as we haven't built the API)
                // In real implementation, call /api/ref-score or a controller method
                
                // For demonstration, let's alert implementation detail
                // alert('Fitur Ambil Nilai akan mencari ST dengan jenis ID: ' + refJenisId + ' di tahun ' + tahun);
                
                 $.ajax({
                    url: '/kertas-kerja/fetch-reference', // We need to create this route
                    type: 'GET',
                    data: {
                        ref_jenis_id: refJenisId,
                        tahun: tahun
                    },
                    success: function(response) {
                        if(response.success) {
                            input.val(response.nilai);
                            toastr.success('Nilai berhasil diambil: ' + response.nilai);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function() {
                        toastr.error('Gagal mengambil data referensi.');
                    },
                    complete: function() {
                        btn.prop('disabled', false).html('<i class="fas fa-sync-alt mr-1"></i> Ambil');
                    }
                });
            });
            // AJAX Save QA
            $(document).on('click', '.btn-save-qa', function() {
                let btn = $(this);
                let indicatorId = btn.data('indicator');
                let criteriaId = btn.data('criteria');
                let kkId = btn.data('kk');
                
                // Collect Data
                let radioName = `qa[${criteriaId}][qa_value]`;
                let qaValue = $(`input[name="${radioName}"]:checked`).val();
                
                let noteQaName = `qa[${criteriaId}][catatan_qa]`;
                let noteQa = $(`textarea[name="${noteQaName}"]`).val();

                if (!qaValue && !noteQa) {
                    toastr.warning('Pilih nilai QA atau isi catatan.');
                    return;
                }

                // Prepare FormData
                let formData = new FormData();
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                
                if (!csrfToken) {
                    toastr.error('CSRF Token missing. Silakan refresh halaman.');
                    return;
                }

                formData.append('_token', csrfToken);
                formData.append('kk_id', kkId);
                formData.append('criteria_id', criteriaId);
                if (qaValue) formData.append('qa_value', qaValue);
                formData.append('catatan_qa', noteQa);

                // UI Loading
                let originalIcon = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                // AJAX
                $.ajax({
                    url: '/kertas-kerja/update-qa-single', 
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if(response.success) {
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message || 'Gagal menyimpan QA.');
                        }
                    },
                    error: function(xhr, status, error) {
                        toastr.error('Gagal menyimpan QA. ' + error);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalIcon);
                    }
                });
            });

            // AJAX Save Criteria
            $(document).on('click', '.btn-save-criteria', function() {
                let btn = $(this);
                let indicatorId = btn.data('indicator');
                let criteriaId = btn.data('criteria');
                let kkId = btn.data('kk');
                
                // Collect Data
                // 1. Radio Value
                let radioName = `answers[${indicatorId}][criteria][${criteriaId}][value]`;
                let value = $(`input[name="${radioName}"]:checked`).val();
                
                if (!value) {
                    toastr.warning('Pilih nilai (Ya/Sebagian/Tidak) terlebih dahulu.');
                    return;
                }

                // 2. File
                let fileInputId = `#file-${criteriaId}`;
                let fileInput = $(fileInputId)[0];
                let file = fileInput.files.length > 0 ? fileInput.files[0] : null;

                // 3. Notes & Link
                let noteName = `answers[${indicatorId}][criteria][${criteriaId}][catatan]`;
                let note = $(`textarea[name="${noteName}"]`).val();
                
                let linkName = `answers[${indicatorId}][criteria][${criteriaId}][link]`;
                let link = $(`input[name="${linkName}"]`).val();

                // Prepare FormData
                let formData = new FormData();
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                
                if (!csrfToken) {
                    toastr.error('CSRF Token missing. Silakan refresh halaman.');
                    return;
                }

                formData.append('_token', csrfToken);
                formData.append('kk_id', kkId);
                formData.append('indicator_id', indicatorId);
                formData.append('criteria_id', criteriaId);
                formData.append('value', value);
                formData.append('catatan', note);
                if (link) formData.append('link', link);
                if (file) formData.append('evidence', file);

                // UI Loading
                let originalIcon = btn.html();
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                // AJAX
                $.ajax({
                    url: '/kertas-kerja/update-single', // Explicit URL to avoid route cache issues
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if(response.success) {
                            let metode = response.metode || 'tally';
                            let isLevel = (metode === 'building_block' || metode === 'criteria_fulfillment');
                            let suffix = isLevel ? '' : '%';

                            toastr.success('Skor: ' + response.param_score + suffix);

                            // Update Parameter Score Badge
                            let badge = $('#score-param-' + indicatorId);
                            if (badge.length) {
                                if (isLevel) {
                                    let lvl = Math.floor(parseFloat(response.param_score));
                                    badge.html('Skor: <strong>' + response.param_score + '</strong> (Level ' + lvl + ')');
                                } else {
                                    badge.html('Skor Parameter: ' + response.param_score + '%');
                                }
                                badge.addClass('animate__animated animate__pulse');
                                setTimeout(() => badge.removeClass('animate__animated animate__pulse'), 1000);
                            }

                            // Update Rollup Scores (Indicator/Aspect levels)
                            if (response.rollup_scores) {
                                $.each(response.rollup_scores, function(indId, score) {
                                    let el = $('#score-aspect-' + indId);
                                    if (el.length) {
                                        if (isLevel) {
                                            el.find('strong').text(parseFloat(score).toFixed(2));
                                        } else {
                                            el.find('strong').text(parseFloat(score).toFixed(1) + '%');
                                        }
                                        el.addClass('animate__animated animate__pulse');
                                        setTimeout(() => el.removeClass('animate__animated animate__pulse'), 1000);
                                    }
                                });
                            }

                            // Update Final Score
                            if (response.final_score) {
                                let finalBadge = $('#score-final');
                                finalBadge.text(response.final_score + suffix);
                                finalBadge.addClass('animate__animated animate__heartBeat');
                                setTimeout(() => finalBadge.removeClass('animate__animated animate__heartBeat'), 1500);

                                // Color-code based on score value and method
                                let score = parseFloat(response.final_score);
                                finalBadge.removeClass('badge-primary badge-success badge-warning badge-danger');
                                if (isLevel) {
                                    if (score >= 4) finalBadge.addClass('badge-success');
                                    else if (score >= 3) finalBadge.addClass('badge-primary');
                                    else if (score >= 2) finalBadge.addClass('badge-warning');
                                    else finalBadge.addClass('badge-danger');
                                } else {
                                    if (score >= 80) finalBadge.addClass('badge-success');
                                    else if (score >= 60) finalBadge.addClass('badge-primary');
                                    else if (score >= 40) finalBadge.addClass('badge-warning');
                                    else finalBadge.addClass('badge-danger');
                                }
                            }
                        } else {
                            toastr.error(response.message || 'Gagal menyimpan.');
                        }
                    },
                    error: function(xhr, status, error) {
                        let msg = 'Gagal menyimpan. ';
                        if(xhr.responseJSON && xhr.responseJSON.message) {
                            msg += xhr.responseJSON.message;
                            // Check for validation errors
                            if (xhr.responseJSON.errors) {
                                let errors = Object.values(xhr.responseJSON.errors).flat();
                                msg += ' ' + errors.join(', ');
                            }
                        } else {
                            msg += error + ' (' + xhr.status + ')';
                        }
                        console.error('Save Error:', xhr);
                        toastr.error(msg);
                    },
                    complete: function() {
                        btn.prop('disabled', false).html(originalIcon);
                    }
                });
            });
            // Save Tanggapan QA
            $(document).on('click', '.btn-save-tanggapan', function() {
                let btn = $(this);
                let criteriaId = btn.data('criteria');
                let kkId = btn.data('kk');
                
                let tanggapanName = `qa[${criteriaId}][tanggapan_qa]`;
                let tanggapan = $(`textarea[name="${tanggapanName}"]`).val();

                if (!tanggapan) {
                    toastr.warning('Isi tanggapan terlebih dahulu.');
                    return;
                }

                // Prepare FormData
                let formData = new FormData();
                let csrfToken = $('meta[name="csrf-token"]').attr('content');
                
                if (!csrfToken) {
                    toastr.error('CSRF Token missing. Silakan refresh halaman.');
                    return;
                }

                formData.append('_token', csrfToken);
                formData.append('kk_id', kkId);
                formData.append('criteria_id', criteriaId);
                formData.append('tanggapan_qa', tanggapan);

                // UI Loading
                let originalText = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

                $.ajax({
                    url: "{{ route('kertas-kerja.update-tanggapan-qa') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if(response.success) {
                            toastr.success(response.message);
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        toastr.error('Gagal menyimpan tanggapan.');
                        console.error(xhr);
                    },
                    complete: function() {
                        btn.html(originalText).prop('disabled', false);
                    }
                });
            });

            // Custom file input label
            $('.custom-file-input').on('change', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

        });
    </script>
@stop
