@extends('adminlte::page')

@section('title', 'Template Builder')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="text-dark font-weight-light">
            Builder: <b>{{ $template->nama }}</b>
            @php
                $metodeLabels = ['tally' => 'Tally (%)', 'building_block' => 'Building Block (Lv 1-5)', 'criteria_fulfillment' => 'Pemenuhan Kriteria (Lv 1-5)'];
                $isLevel = in_array($template->metode_penilaian, ['building_block', 'criteria_fulfillment']);
            @endphp
            <span class="badge badge-{{ $template->metode_penilaian === 'tally' ? 'secondary' : 'primary' }} ml-2" style="font-size: 0.6em;">
                <i class="fas fa-calculator mr-1"></i>{{ $metodeLabels[$template->metode_penilaian] ?? 'Tally (%)' }}
            </span>
        </h1>
        <div>
            <a href="{{ route('templates.preview', $template->id) }}" class="btn btn-outline-info btn-sm mr-1" target="_blank">
                <i class="fas fa-eye mr-1"></i> Preview
            </a>
            <a href="{{ route('templates.index') }}" class="btn btn-default btn-sm">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0 pl-3">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- Bobot Summary Bar --}}
            @php
                $totalAspectBobot = $indicators->sum('bobot');
                $bobotOk = abs($totalAspectBobot - 100) < 0.01;
            @endphp
            <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                        <div class="d-flex align-items-center flex-wrap">
                            <small class="text-muted font-weight-bold mr-3"><i class="fas fa-balance-scale mr-1"></i>Bobot Aspek:</small>
                            @foreach($indicators as $asp)
                                <span class="badge badge-light border mr-2 mb-1 p-2">
                                    {{ \Illuminate\Support\Str::limit($asp->uraian, 20) }}: <strong>{{ $asp->bobot }}%</strong>
                                </span>
                            @endforeach
                        </div>
                        <div>
                            <span class="badge {{ $bobotOk ? 'badge-success' : 'badge-danger' }} p-2 px-3" style="font-size: 0.9em;" id="totalBobotBadge">
                                Total: {{ number_format($totalAspectBobot, 2) }}%
                                @if($bobotOk) <i class="fas fa-check ml-1"></i> @else <i class="fas fa-exclamation-triangle ml-1"></i> @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-3">
                <button class="btn btn-primary btn-sm shadow-sm" data-toggle="modal" data-target="#addIndicatorModal" data-parent-id="" data-type="Aspek">
                    <i class="fas fa-plus mr-1"></i> Tambah Aspek Baru
                </button>
                <button class="btn btn-outline-secondary btn-sm ml-2" id="btnCollapseAll">
                    <i class="fas fa-compress-alt mr-1"></i> Collapse All
                </button>
                <button class="btn btn-outline-secondary btn-sm" id="btnExpandAll">
                    <i class="fas fa-expand-alt mr-1"></i> Expand All
                </button>
            </div>

            {{-- 1st Level: Aspects --}}
            @foreach($indicators as $aspect)
                <div class="card mb-3 shadow-sm border border-light" id="aspect-{{ $aspect->id }}">
                    <div class="card-header py-2 bg-gradient-light d-flex justify-content-between align-items-center">
                        <h3 class="card-title m-0 text-primary" style="font-size: 1.1rem; font-weight: 600;">
                            <i class="fas fa-layer-group mr-2 text-secondary"></i>
                            <span class="editable-text" data-type="indicator" data-id="{{ $aspect->id }}" data-field="uraian">{{ $aspect->uraian }}</span>
                            <span class="editable-text badge badge-outline-primary ml-1" data-type="indicator" data-id="{{ $aspect->id }}" data-field="bobot" style="cursor:pointer;">{{ $aspect->bobot }}%</span>
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool btn-sm" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            <button type="button" class="btn btn-tool btn-sm text-danger btn-delete-indicator" data-id="{{ $aspect->id }}" title="Hapus Aspek">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        {{-- 2nd Level: Indicators --}}
                        @if($aspect->children->count() > 0)
                            @foreach($aspect->children as $indicator)
                                <div class="border-bottom p-3 pl-4 bg-white position-relative" id="indicator-{{ $indicator->id }}">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center text-info">
                                            <i class="fas fa-chevron-right mr-2 text-xs text-muted"></i>
                                            <span class="font-weight-bold editable-text" style="font-size: 1rem;" data-type="indicator" data-id="{{ $indicator->id }}" data-field="uraian">{{ $indicator->uraian }}</span>
                                            <span class="badge badge-info ml-2 editable-text" data-type="indicator" data-id="{{ $indicator->id }}" data-field="bobot" style="cursor:pointer;">{{ $indicator->bobot }}%</span>
                                        </div>
                                        <button type="button" class="btn btn-xs text-muted hover-danger btn-delete-indicator" data-id="{{ $indicator->id }}" title="Hapus Indikator">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    {{-- 3rd Level: Parameters --}}
                                    <div class="ml-4 pl-3" style="border-left: 2px solid #e9ecef;">
                                        @foreach($indicator->children as $parameter)
                                            <div class="mb-3 pl-2" id="parameter-{{ $parameter->id }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-dark font-weight-normal">
                                                        <i class="far fa-dot-circle mr-2 text-secondary text-xs"></i>
                                                        <span class="editable-text" data-type="indicator" data-id="{{ $parameter->id }}" data-field="uraian">{{ $parameter->uraian }}</span>
                                                        <span class="editable-text badge badge-light border ml-1" data-type="indicator" data-id="{{ $parameter->id }}" data-field="bobot" style="cursor:pointer;">{{ $parameter->bobot }}%</span>
                                                    </span>
                                                    <button type="button" class="btn btn-xs text-light-gray hover-danger btn-delete-indicator" data-id="{{ $parameter->id }}" title="Hapus Parameter">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </div>

                                                {{-- Criteria List --}}
                                                <div class="mt-1 pl-4" id="criteria-list-{{ $parameter->id }}">
                                                    <ul class="list-unstyled mb-1">
                                                        @foreach($parameter->criteria->sortBy('level') as $criteria)
                                                            <li class="text-muted text-sm d-flex align-items-center py-1 criteria-item" id="criteria-{{ $criteria->id }}">
                                                                <i class="fas fa-check mr-2 text-success" style="font-size: 0.7rem;"></i>
                                                                @if($isLevel && $criteria->level > 0)
                                                                    <span class="badge badge-light border mr-1" style="font-size: 0.65rem;">Lv.{{ $criteria->level }}</span>
                                                                @endif
                                                                <span class="editable-text flex-grow-1" data-type="criteria" data-id="{{ $criteria->id }}" data-field="uraian">{{ $criteria->uraian }}</span>
                                                                @if($isLevel)
                                                                    <select class="form-control form-control-sm ml-1 criteria-level-select" data-id="{{ $criteria->id }}" style="max-width: 65px; height: 24px; font-size: 0.75rem; padding: 0 4px;">
                                                                        @for($lv = 1; $lv <= 5; $lv++)
                                                                            <option value="{{ $lv }}" {{ $criteria->level == $lv ? 'selected' : '' }}>Lv.{{ $lv }}</option>
                                                                        @endfor
                                                                    </select>
                                                                @endif
                                                                <button type="button" class="btn btn-xs text-danger p-0 opacity-50 hover-opacity-100 btn-delete-criteria ml-2" data-id="{{ $criteria->id }}" style="line-height:1">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                    {{-- Add Criteria Form --}}
                                                    <div class="d-flex align-items-center mt-1">
                                                        <input type="text" class="form-control form-control-sm border-0 bg-light rounded px-2 criteria-input" placeholder="+ Kriteria..." style="max-width: 350px;" data-indicator="{{ $parameter->id }}">
                                                        @if($isLevel)
                                                        <select class="form-control form-control-sm ml-1 criteria-level-input" style="max-width: 70px;" data-indicator="{{ $parameter->id }}">
                                                            @for($lv = 1; $lv <= 5; $lv++)
                                                                <option value="{{ $lv }}">Lv.{{ $lv }}</option>
                                                            @endfor
                                                        </select>
                                                        @endif
                                                        <button type="button" class="btn btn-xs btn-light text-success ml-1 btn-add-criteria" data-indicator="{{ $parameter->id }}"><i class="fas fa-plus"></i></button>
                                                    </div>
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

    {{-- Modal Add Indicator --}}
    <div class="modal fade" id="addIndicatorModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
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
$(document).ready(function() {
    // ─── Modal Setup ──────────────────────────────────────
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

    // ─── Inline Edit (Double-click) ──────────────────────
    $(document).on('dblclick', '.editable-text', function() {
        var el = $(this);
        if (el.find('input, select').length) return; // Already editing

        var type = el.data('type');
        var id = el.data('id');
        var field = el.data('field');
        var currentVal = el.text().trim().replace('%', '');
        var isBobot = field === 'bobot';

        var input;
        if (isBobot) {
            input = $('<input type="number" step="0.01" class="form-control form-control-sm d-inline" style="width:80px;">').val(currentVal);
        } else {
            input = $('<input type="text" class="form-control form-control-sm d-inline" style="width:300px;">').val(currentVal);
        }

        el.data('original', el.html());
        el.html('').append(input);
        input.focus().select();

        input.on('blur keydown', function(e) {
            if (e.type === 'keydown' && e.key !== 'Enter') return;
            e.preventDefault();

            var newVal = $(this).val();
            if (!newVal || newVal === currentVal) {
                el.html(el.data('original'));
                return;
            }

            var url = type === 'indicator' ? '/indicators/' + id : '/criteria/' + id;
            var data = { _method: 'PUT' };
            data[field] = newVal;
            data['_token'] = '{{ csrf_token() }}';

            $.ajax({
                url: url,
                type: 'POST',
                data: data,
                success: function(resp) {
                    if (resp.success) {
                        if (isBobot) {
                            el.text(newVal + '%');
                        } else {
                            el.text(newVal);
                        }
                        toastr.success('Tersimpan');
                        if (isBobot) location.reload(); // Refresh bobot summary
                    }
                },
                error: function() {
                    el.html(el.data('original'));
                    toastr.error('Gagal menyimpan');
                }
            });
        });
    });

    // ─── AJAX Delete Indicator ────────────────────────────
    $(document).on('click', '.btn-delete-indicator', function() {
        var id = $(this).data('id');
        var el = $(this);

        Swal.fire({
            title: 'Hapus item ini?',
            text: 'Item beserta semua sub-item dan kriteria akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/indicators/' + id,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function(resp) {
                        if (resp.success) {
                            // Remove the indicator card/row from DOM
                            var card = el.closest('[id^="aspect-"], [id^="indicator-"], [id^="parameter-"]');
                            card.fadeOut(300, function() { $(this).remove(); });
                            toastr.success('Dihapus');
                        }
                    },
                    error: function() { toastr.error('Gagal menghapus'); }
                });
            }
        });
    });

    // ─── AJAX Add Criteria ────────────────────────────────
    $(document).on('click', '.btn-add-criteria', function() {
        var indicatorId = $(this).data('indicator');
        var inputEl = $(this).siblings('.criteria-input');
        var levelEl = $(this).siblings('.criteria-level-input');
        var uraian = inputEl.val();
        var level = levelEl.length ? levelEl.val() : 1;

        if (!uraian) { inputEl.focus(); return; }

        $.ajax({
            url: '/indicators/' + indicatorId + '/criteria',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', uraian: uraian, level: level },
            success: function(resp) {
                if (resp.success) {
                    inputEl.val('');
                    toastr.success('Kriteria ditambahkan');
                    // Reload to show new criteria properly
                    location.reload();
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Gagal menambah kriteria');
            }
        });
    });

    // Enter key on criteria input
    $(document).on('keydown', '.criteria-input', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            $(this).siblings('.btn-add-criteria').click();
        }
    });

    // ─── AJAX Delete Criteria ─────────────────────────────
    $(document).on('click', '.btn-delete-criteria', function() {
        var id = $(this).data('id');
        var li = $(this).closest('.criteria-item');

        Swal.fire({
            title: 'Hapus kriteria?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/criteria/' + id,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function(resp) {
                        if (resp.success) {
                            li.fadeOut(200, function() { $(this).remove(); });
                            toastr.success('Kriteria dihapus');
                        }
                    },
                    error: function() { toastr.error('Gagal menghapus'); }
                });
            }
        });
    });

    // ─── AJAX Update Criteria Level ───────────────────────
    $(document).on('change', '.criteria-level-select', function() {
        var id = $(this).data('id');
        var level = $(this).val();

        $.ajax({
            url: '/criteria/' + id,
            type: 'POST',
            data: { _method: 'PUT', _token: '{{ csrf_token() }}', level: level },
            success: function(resp) {
                if (resp.success) toastr.success('Level diupdate');
            },
            error: function() { toastr.error('Gagal update level'); }
        });
    });

    // ─── Collapse / Expand All ────────────────────────────
    $('#btnCollapseAll').on('click', function() {
        $('.card-body').slideUp(200);
    });
    $('#btnExpandAll').on('click', function() {
        $('.card-body').slideDown(200);
    });
});
</script>
<style>
    .hover-red:hover { color: #dc3545 !important; }
    .hover-danger:hover { color: #dc3545 !important; }
    .text-light-gray { color: #ced4da; }
    .opacity-50 { opacity: 0.5; }
    .hover-opacity-100:hover { opacity: 1; }
    .editable-text { cursor: default; border-bottom: 1px dashed transparent; transition: all 0.2s; }
    .editable-text:hover { border-bottom-color: #007bff; cursor: text; }
    .criteria-item:hover { background-color: #f8f9fa; border-radius: 4px; }
</style>
@stop
