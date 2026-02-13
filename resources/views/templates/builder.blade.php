@extends('adminlte::page')

@section('title', 'Template Builder')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1 class="text-dark font-weight-light">Builder: <b>{{ $template->nama }}</b></h1>
        <a href="{{ route('templates.index') }}" class="btn btn-default btn-sm">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="mb-3">
                <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addIndicatorModal" data-parent-id="" data-type="Aspek">
                    <i class="fas fa-plus mr-1"></i> Tambah Aspek Baru
                </button>
            </div>

            {{-- 1st Level: Aspects --}}
            @foreach($indicators as $aspect)
                <div class="card mb-3 shadow-sm border border-light">
                    <div class="card-header py-2 bg-gradient-light d-flex justify-content-between align-items-center">
                        <h3 class="card-title m-0 text-primary" style="font-size: 1.1rem; font-weight: 600;">
                            <i class="fas fa-layer-group mr-2 text-secondary"></i> {{ $aspect->uraian }} <small class="text-muted ml-1">({{ $aspect->bobot }}%)</small>
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool btn-sm" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            <form action="{{ route('templates.indicators.destroy', $aspect->id) }}" method="POST" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-tool btn-sm text-danger hover-red" onclick="return confirm('Hapus aspek ini?')" title="Hapus Aspek"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        {{-- 2nd Level: Indicators --}}
                        @if($aspect->children->count() > 0)
                            @foreach($aspect->children as $indicator)
                                <div class="border-bottom p-3 pl-4 bg-white position-relative">
                                    {{-- Indicator Header --}}
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center text-info">
                                            <i class="fas fa-chevron-right mr-2 text-xs text-muted"></i>
                                            <span class="font-weight-bold" style="font-size: 1rem;">{{ $indicator->uraian }}</span>
                                            <span class="badge badge-info ml-2">{{ $indicator->bobot }}%</span>
                                        </div>
                                        <div>
                                            <form action="{{ route('templates.indicators.destroy', $indicator->id) }}" method="POST" style="display:inline">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-xs text-muted hover-danger" onclick="return confirm('Hapus indikator ini?')" title="Hapus Indikator"><i class="fas fa-times"></i></button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- 3rd Level: Parameters --}}
                                    <div class="ml-4 pl-3" style="border-left: 2px solid #e9ecef;">
                                        @foreach($indicator->children as $parameter)
                                            <div class="mb-3 pl-2">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-dark font-weight-normal"><i class="far fa-dot-circle mr-2 text-secondary text-xs"></i> {{ $parameter->uraian }} <small class="text-muted">({{ $parameter->bobot }}%)</small></span>
                                                    <form action="{{ route('templates.indicators.destroy', $parameter->id) }}" method="POST" style="display:inline">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-xs text-light-gray hover-danger" onclick="return confirm('Hapus parameter ini?')" title="Hapus Parameter"><i class="fas fa-times"></i></button>
                                                    </form>
                                                </div>

                                                {{-- Criteria List --}}
                                                <div class="mt-1 pl-4">
                                                    <ul class="list-unstyled mb-1">
                                                        @foreach($parameter->criteria as $criteria)
                                                            <li class="text-muted text-sm d-flex align-items-center py-0">
                                                                <i class="fas fa-check mr-2 text-success" style="font-size: 0.7rem;"></i> {{ $criteria->uraian }}
                                                                <form action="{{ route('templates.criteria.destroy', $criteria->id) }}" method="POST" style="display:inline" class="ml-2">
                                                                    @csrf @method('DELETE')
                                                                    <button class="btn btn-xs text-danger p-0 opacity-50 hover-opacity-100" onclick="return confirm('Hapus kriteria?')" style="line-height:1"><i class="fas fa-times"></i></button>
                                                                </form>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                    <form action="{{ route('templates.criteria.store', $parameter->id) }}" method="POST" class="form-inline mt-1">
                                                        @csrf
                                                        <div class="input-group input-group-sm">
                                                            <input type="text" name="uraian" class="form-control form-control-sm border-0 bg-light rounded px-2" placeholder="+ Kriteria..." style="max-width: 200px;" required>
                                                            <div class="input-group-append">
                                                                <button type="submit" class="btn btn-xs btn-light text-success"><i class="fas fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach

                                        {{-- Add Parameter Btn --}}
                                        <div class="mt-2 pl-2">
                                            <button class="btn btn-xs btn-outline-secondary border-0" data-toggle="modal" data-target="#addIndicatorModal" data-parent-id="{{ $indicator->id }}" data-type="Parameter">
                                                <i class="fas fa-plus mr-1"></i> Parameter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="p-4 text-center text-muted text-sm">
                                Belum ada indikator. Silakan tambah indikator baru.
                            </div>
                        @endif

                        {{-- Add Indicator Btn --}}
                        <div class="card-footer bg-white p-2 text-center">
                            <button class="btn btn-sm btn-link text-decoration-none" data-toggle="modal" data-target="#addIndicatorModal" data-parent-id="{{ $aspect->id }}" data-type="Indikator">
                                <i class="fas fa-plus-circle mr-1"></i> Tambah Indikator
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

            @if($indicators->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-layer-group fa-3x mb-3 text-light-gray"></i>
                    <p>Belum ada Aspek yang dibuat.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="addIndicatorModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document"> {{-- Make modal smaller --}}
            <form action="{{ route('templates.indicators.store', $template->id) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-light py-2">
                        <h6 class="modal-title font-weight-bold">Tambah <span id="modalTypeName">Item</span></h6>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="parent_id" id="parentIdField">
                        <div class="form-group mb-2">
                            <label class="text-sm">Uraian / Nama</label>
                            <input type="text" name="uraian" class="form-control form-control-sm" required autofocus>
                        </div>
                        <div id="weightField" class="form-group mb-0" style="display:none">
                            <label class="text-sm">Bobot (%)</label>
                            <input type="number" name="bobot" class="form-control form-control-sm" step="0.01">
                        </div>
                        <input type="hidden" name="tipe" id="typeField" value="score_manual">
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-xs btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-xs btn-primary px-3">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $('#addIndicatorModal').appendTo("body");
    $('#addIndicatorModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var type = button.data('type');
        var parentId = button.data('parent-id');
        
        var modal = $(this);
        modal.find('#modalTypeName').text(type);
        modal.find('#parentIdField').val(parentId);
        
        if (type === 'Aspek') {
            modal.find('#weightField').show();
            modal.find('#typeField').val('header');
        } else if (type === 'Parameter') {
            modal.find('#weightField').show();
            modal.find('#typeField').val('criteria_tally');
        } else if (type === 'Indikator') {
            modal.find('#weightField').show();
            modal.find('#typeField').val('header');
        } else {
            modal.find('#weightField').hide();
            modal.find('#typeField').val('score_manual');
        }
    });
</script>
<style>
    .hover-red:hover { color: #dc3545 !important; }
    .hover-danger:hover { color: #dc3545 !important; }
    .text-light-gray { color: #ced4da; }
    .opacity-50 { opacity: 0.5; }
    .hover-opacity-100:hover { opacity: 1; }
</style>
@stop
