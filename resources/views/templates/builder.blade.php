@extends('adminlte::page')

@section('title', 'Template Builder')

@section('content_header')
    <div class="d-flex justify-content-between">
        <h1>Builder: {{ $template->nama }}</h1>
        <a href="{{ route('templates.index') }}" class="btn btn-default">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
@stop

@section('content')
    <div class="row">
        {{-- List existing aspects/indicators/parameters --}}
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- 1st Level: Aspects --}}
            @foreach($indicators as $aspect)
                <div class="card card-outline card-primary mb-4">
                    <div class="card-header">
                        <h3 class="card-title font-weight-bold">Level 1: ASPEK - {{ $aspect->label }} ({{ $aspect->weight }}%)</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            <form action="{{ route('templates.indicators.destroy', $aspect->id) }}" method="POST" style="display:inline">
                                @csrf @method('DELETE')
                                <button class="btn btn-tool text-danger" onclick="return confirm('Hapus aspek ini?')"><i class="fas fa-trash"></i></button>
                            </form>
                        </div>
                    </div>
                    <div class="card-body">
                        {{-- 2nd Level: Indicators --}}
                        @foreach($aspect->children as $indicator)
                            <div class="card card-outline card-info ml-4 mb-3">
                                <div class="card-header">
                                    <h3 class="card-title">Level 2: INDIKATOR - {{ $indicator->label }}</h3>
                                    <div class="card-tools">
                                        <form action="{{ route('templates.indicators.destroy', $indicator->id) }}" method="POST" style="display:inline">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-tool text-danger" onclick="return confirm('Hapus indikator ini?')"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </div>
                                </div>
                                <div class="card-body">
                                    {{-- 3rd Level: Parameters --}}
                                    @foreach($indicator->children as $parameter)
                                        <div class="card border ml-4 mb-2">
                                            <div class="card-header bg-light">
                                                <h3 class="card-title">Level 3: PARAMETER - {{ $parameter->label }}</h3>
                                                <div class="card-tools">
                                                    <form action="{{ route('templates.indicators.destroy', $parameter->id) }}" method="POST" style="display:inline">
                                                        @csrf @method('DELETE')
                                                        <button class="btn btn-tool text-danger" onclick="return confirm('Hapus parameter ini?')"><i class="fas fa-trash"></i></button>
                                                    </form>
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                {{-- Criteria List --}}
                                                <div class="mb-2">
                                                    <span class="badge badge-secondary mb-2">Checklist Kriteria Pemenuhan:</span>
                                                    <ul class="list-group list-group-flush ml-3">
                                                        @foreach($parameter->criteria as $criteria)
                                                            <li class="list-group-item d-flex justify-content-between align-items-center py-1 bg-transparent">
                                                                - {{ $criteria->label }}
                                                                <form action="{{ route('templates.criteria.destroy', $criteria->id) }}" method="POST" style="display:inline">
                                                                    @csrf @method('DELETE')
                                                                    <button class="btn btn-xs text-danger" onclick="return confirm('Hapus kriteria?')"><i class="fas fa-times"></i></button>
                                                                </form>
                                                            </li>
                                                        @endforeach
                                                        <li class="list-group-item py-1 bg-transparent">
                                                            <form action="{{ route('templates.criteria.store', $parameter->id) }}" method="POST" class="form-inline">
                                                                @csrf
                                                                <input type="text" name="label" class="form-control form-control-sm mr-2" style="flex:1" placeholder="Tambah kriteria..." required>
                                                                <button type="submit" class="btn btn-xs btn-success"><i class="fas fa-plus"></i></button>
                                                            </form>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    {{-- Add Parameter --}}
                                    <div class="ml-4">
                                        <button class="btn btn-xs btn-outline-info" data-toggle="modal" data-target="#addIndicatorModal" data-parent-id="{{ $indicator->id }}" data-type="Parameter">
                                            <i class="fas fa-plus"></i> Tambah Parameter
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        {{-- Add Indicator --}}
                        <div class="ml-4">
                            <button class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#addIndicatorModal" data-parent-id="{{ $aspect->id }}" data-type="Indikator">
                                <i class="fas fa-plus"></i> Tambah Indikator
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Add Aspect --}}
            <button class="btn btn-primary" data-toggle="modal" data-target="#addIndicatorModal" data-parent-id="" data-type="Aspek">
                <i class="fas fa-plus"></i> Tambah Aspek (Level 1)
            </button>
        </div>
    </div>

    {{-- Modal Add Indicator/Aspect/Parameter --}}
    <div class="modal fade" id="addIndicatorModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="{{ route('templates.indicators.store', $template->id) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Tambah <span id="modalTypeName">Indicator</span></h5>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="parent_id" id="parentIdField">
                        <div class="form-group">
                            <label>Nama/Label</label>
                            <input type="text" name="label" class="form-control" required>
                        </div>
                        <div id="weightField" class="form-group" style="display:none">
                            <label>Bobot (%)</label>
                            <input type="number" name="weight" class="form-control" step="0.01">
                        </div>
                        <input type="hidden" name="type" id="typeField" value="score">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $('#addIndicatorModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var type = button.data('type');
        var parentId = button.data('parent-id');
        
        var modal = $(this);
        modal.find('#modalTypeName').text(type);
        modal.find('#parentIdField').val(parentId);
        
        if (type === 'Aspek') {
            modal.find('#weightField').show();
            modal.find('#typeField').val('percentage');
        } else if (type === 'Parameter') {
            modal.find('#weightField').hide();
            modal.find('#typeField').val('criteria_tally');
        } else {
            modal.find('#weightField').hide();
            modal.find('#typeField').val('score');
        }
    });
</script>
@stop
