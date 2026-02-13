@extends('adminlte::page')

@section('title', 'Isi Kertas Kerja')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <h1 class="m-0 text-dark font-weight-bold">Isi Kertas Kerja</h1>
        <small class="text-muted">{{ $kertasKerja->judul_kk }}</small>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        <form action="{{ route('kertas-kerja.update', $kertasKerja->id) }}" method="POST">
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
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-sm">
                        <i class="fas fa-save mr-2"></i> Simpan Kertas Kerja
                    </button>
                </div>
            </div>
        </form>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
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
        });
    </script>
@stop
