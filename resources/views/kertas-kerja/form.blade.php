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
            <a href="{{ route('kertas-kerja.index') }}" class="btn btn-default btn-sm shadow-sm">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke Daftar
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
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
                    @else
                        <div class="alert alert-info d-inline-block rounded-pill px-4 shadow-sm mb-0">
                            <i class="fas fa-info-circle mr-2"></i> Anda dalam mode **Read-Only**. Hubungi Ketua Tim atau Dalnis jika ada data yang perlu diubah.
                        </div>
                    @endif
                </div>
            </div>
        </form>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
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
                            toastr.success('Data tersimpan! Skor: ' + response.param_score + '%');
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
        });
    </script>
@stop
