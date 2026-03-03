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
                            <button type="button" class="btn btn-tool btn-sm text-info btn-edit-indicator" data-id="{{ $aspect->id }}" data-uraian="{{ $aspect->uraian }}" data-bobot="{{ $aspect->bobot }}" title="Edit Aspek">
                                <i class="fas fa-edit"></i>
                            </button>
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
                                        <div>
                                            <button type="button" class="btn btn-xs text-info hover-info btn-edit-indicator mr-1" data-id="{{ $indicator->id }}" data-uraian="{{ $indicator->uraian }}" data-bobot="{{ $indicator->bobot }}" title="Edit Indikator">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs text-muted hover-danger btn-delete-indicator" data-id="{{ $indicator->id }}" title="Hapus Indikator">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
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
                                                    <div>
                                                        <button type="button" class="btn btn-xs text-info hover-info btn-edit-indicator mr-1" data-id="{{ $parameter->id }}" data-uraian="{{ $parameter->uraian }}" data-bobot="{{ $parameter->bobot }}" title="Edit Parameter">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-xs text-light-gray hover-danger btn-delete-indicator" data-id="{{ $parameter->id }}" title="Hapus Parameter">
                                                            <i class="fas fa-times"></i>
                                                        </button>
                                                    </div>
                                                </div>

                                                <div class="row mt-2 pl-3">
                                                    {{-- Criteria Column --}}
                                                    <div class="col-lg-6 mb-3 pr-lg-3 border-right">
                                                        <h6 class="text-xs font-weight-bold text-secondary mb-2 text-uppercase"><i class="fas fa-list-ul mr-1"></i> Kriteria Penilaian</h6>
                                                        <div id="criteria-list-{{ $parameter->id }}">
                                                            <ul class="list-unstyled mb-1">
                                                                @foreach($parameter->criteria->sortBy('level') as $criteria)
                                                                    <li class="text-muted text-sm d-flex align-items-start py-1 criteria-item" id="criteria-{{ $criteria->id }}">
                                                                        <i class="fas fa-check mr-2 text-success" style="font-size: 0.7rem; margin-top: 0.35rem;"></i>
                                                                        @if($isLevel && $criteria->level > 0)
                                                                            <span class="badge badge-light border mr-2 mt-1" style="font-size: 0.65rem;">Lv.{{ $criteria->level }}</span>
                                                                        @endif
                                                                        <span class="editable-text flex-grow-1" data-type="criteria" data-id="{{ $criteria->id }}" data-field="uraian" style="white-space: pre-wrap; word-break: break-word;">{{ $criteria->uraian }}</span>
                                                                        @if($isLevel)
                                                                            <select class="form-control form-control-sm ml-1 criteria-level-select mt-1" data-id="{{ $criteria->id }}" style="max-width: 65px; height: 24px; font-size: 0.75rem; padding: 0 4px;">
                                                                                @for($lv = 1; $lv <= 5; $lv++)
                                                                                    <option value="{{ $lv }}" {{ $criteria->level == $lv ? 'selected' : '' }}>Lv.{{ $lv }}</option>
                                                                                @endfor
                                                                            </select>
                                                                        @endif
                                                                        <button type="button" class="btn btn-xs text-danger p-0 opacity-50 hover-opacity-100 btn-delete-criteria ml-2 mt-1" data-id="{{ $criteria->id }}" style="line-height:1">
                                                                            <i class="fas fa-times"></i>
                                                                        </button>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                            {{-- Add Criteria Form --}}
                                                            <div class="d-flex align-items-start mt-2">
                                                                <textarea class="form-control form-control-sm border-0 bg-light rounded px-2 py-1 criteria-input" placeholder="+ Kriteria (Ctrl+Enter utk simpan)" style="width: 100%; min-height: 40px; resize: vertical;" data-indicator="{{ $parameter->id }}"></textarea>
                                                                @if($isLevel)
                                                                <select class="form-control form-control-sm ml-1 criteria-level-input mt-1" style="max-width: 70px; height: 30px;" data-indicator="{{ $parameter->id }}">
                                                                    @for($lv = 1; $lv <= 5; $lv++)
                                                                        <option value="{{ $lv }}">Lv.{{ $lv }}</option>
                                                                    @endfor
                                                                </select>
                                                                @endif
                                                                <button type="button" class="btn btn-xs btn-light text-success ml-1 mt-1 btn-add-criteria" data-indicator="{{ $parameter->id }}" title="Simpan Kriteria"><i class="fas fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Langkah Kerja Column --}}
                                                    <div class="col-lg-6 mb-3 pl-lg-3">
                                                        <h6 class="text-xs font-weight-bold text-secondary mb-2 text-uppercase"><i class="fas fa-shoe-prints mr-1"></i> Langkah Kerja Standar</h6>
                                                        <div id="langkah-list-{{ $parameter->id }}">
                                                            <ul class="list-unstyled mb-1">
                                                                @foreach($parameter->langkahs as $langkah)
                                                                    <li class="text-muted text-sm d-flex align-items-start py-1 langkah-item" id="langkah-{{ $langkah->id }}">
                                                                        <i class="fas fa-circle mr-2 text-primary" style="font-size: 0.4rem; margin-top: 0.5rem;"></i>
                                                                        <span class="editable-text flex-grow-1" data-type="langkah" data-id="{{ $langkah->id }}" data-field="uraian" style="white-space: pre-wrap; word-break: break-word;">{{ $langkah->uraian }}</span>
                                                                        <select class="form-control form-control-sm ml-1 langkah-jenis-select mt-1" data-id="{{ $langkah->id }}" style="max-width: 140px; height: 24px; font-size: 0.75rem; padding: 0 4px;">
                                                                            <option value="" {{ !$langkah->jenis_prosedur ? 'selected' : '' }}>-- Jenis --</option>
                                                                            <option value="wawancara" {{ $langkah->jenis_prosedur == 'wawancara' ? 'selected' : '' }}>Wawancara</option>
                                                                            <option value="observasi" {{ $langkah->jenis_prosedur == 'observasi' ? 'selected' : '' }}>Observasi</option>
                                                                            <option value="inspeksi_dokumen" {{ $langkah->jenis_prosedur == 'inspeksi_dokumen' ? 'selected' : '' }}>Inspeksi Dok.</option>
                                                                            <option value="analisis_data" {{ $langkah->jenis_prosedur == 'analisis_data' ? 'selected' : '' }}>Analisis Data</option>
                                                                            <option value="konfirmasi" {{ $langkah->jenis_prosedur == 'konfirmasi' ? 'selected' : '' }}>Konfirmasi</option>
                                                                            <option value="rekalkulasi" {{ $langkah->jenis_prosedur == 'rekalkulasi' ? 'selected' : '' }}>Rekalkulasi</option>
                                                                            <option value="kuesioner" {{ $langkah->jenis_prosedur == 'kuesioner' ? 'selected' : '' }}>Kuesioner</option>
                                                                            <option value="lainnya" {{ $langkah->jenis_prosedur == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                                                                        </select>
                                                                        <button type="button" class="btn btn-xs text-danger p-0 opacity-50 hover-opacity-100 btn-delete-langkah ml-2 mt-1" data-id="{{ $langkah->id }}" style="line-height:1" title="Hapus Langkah">
                                                                            <i class="fas fa-times"></i>
                                                                        </button>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                            {{-- Add Langkah Form --}}
                                                            <div class="d-flex align-items-start mt-2">
                                                                <textarea class="form-control form-control-sm border-0 bg-light rounded px-2 py-1 langkah-input" placeholder="+ Langkah Kerja (Ctrl+Enter utk simpan)" style="width: 100%; min-height: 40px; resize: vertical;" data-indicator="{{ $parameter->id }}"></textarea>
                                                                <select class="form-control form-control-sm ml-1 langkah-jenis-input mt-1" style="max-width: 140px; height: 30px; font-size: 0.8rem;" data-indicator="{{ $parameter->id }}">
                                                                    <option value="">-- Jenis --</option>
                                                                    <option value="wawancara">Wawancara</option>
                                                                    <option value="observasi">Observasi</option>
                                                                    <option value="inspeksi_dokumen">Inspeksi Dok.</option>
                                                                    <option value="analisis_data">Analisis Data</option>
                                                                    <option value="konfirmasi">Konfirmasi</option>
                                                                    <option value="rekalkulasi">Rekalkulasi</option>
                                                                    <option value="kuesioner">Kuesioner</option>
                                                                    <option value="lainnya">Lainnya</option>
                                                                </select>
                                                                <button type="button" class="btn btn-xs btn-light text-primary ml-1 mt-1 btn-add-langkah btn-add-langkah-{{ $parameter->id }}" data-indicator="{{ $parameter->id }}" title="Simpan Langkah"><i class="fas fa-plus"></i></button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Matriks Temuan Section --}}
                                                <div class="mt-2 pl-3 pt-2 border-top">
                                                    <h6 class="text-xs font-weight-bold text-secondary mb-2 text-uppercase d-flex justify-content-between">
                                                        <span><i class="fas fa-exclamation-triangle mr-1 text-warning"></i> Matriks Temuan Standar (Root Cause Analysis)</span>
                                                        <button type="button" class="btn btn-xs btn-link p-0 text-warning btn-add-teo" data-indicator="{{ $parameter->id }}">
                                                            <i class="fas fa-plus-circle mr-1"></i> Tambah TEO
                                                        </button>
                                                    </h6>
                                                    <div id="teo-list-{{ $parameter->id }}" class="row">
                                                        @foreach($parameter->teos as $teo)
                                                            <div class="col-md-12 mb-3 teo-card" id="teo-{{ $teo->id }}">
                                                                <div class="card mb-0 shadow-none border" style="background-color: #fffdf5;">
                                                                    <div class="card-header p-2 bg-transparent d-flex justify-content-between align-items-center border-bottom">
                                                                        <span class="text-sm font-weight-bold text-navy">
                                                                            <i class="fas fa-bullseye mr-1 text-warning"></i> 
                                                                            TEO: <span class="teo-text" data-id="{{ $teo->id }}">{{ $teo->teo }}</span>
                                                                        </span>
                                                                        <div>
                                                                            <button type="button" class="btn btn-xs btn-outline-primary btn-modal-cause" data-teo="{{ $teo->id }}" title="Tambah Penyebab">
                                                                                <i class="fas fa-plus mr-1"></i> Penyebab
                                                                            </button>
                                                                            <button type="button" class="btn btn-xs btn-outline-success btn-modal-rec" data-teo="{{ $teo->id }}" title="Tambah Rekomendasi">
                                                                                <i class="fas fa-plus mr-1"></i> Rekomendasi
                                                                            </button>
                                                                            <button type="button" class="btn btn-xs text-info btn-edit-teo" data-id="{{ $teo->id }}" data-teo="{{ $teo->teo }}">
                                                                                <i class="fas fa-edit"></i>
                                                                            </button>
                                                                            <button type="button" class="btn btn-xs text-danger btn-delete-teo" data-id="{{ $teo->id }}">
                                                                                <i class="fas fa-trash"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="card-body p-2">
                                                                        <div class="row">
                                                                            <div class="col-md-6 border-right">
                                                                                <small class="text-xs text-muted font-weight-bold text-uppercase">Penyebab (Root Cause)</small>
                                                                                <div id="cause-list-{{ $teo->id }}" class="mt-1">
                                                                                    @foreach($teo->causes as $cause)
                                                                                        <div class="d-flex justify-content-between align-items-start mb-1 p-1 rounded cause-item hover-bg-light" id="cause-{{ $cause->id }}">
                                                                                            <span class="text-xs">
                                                                                                <i class="fas fa-minus mr-1 text-secondary"></i> {{ $cause->uraian }}
                                                                                                <br>
                                                                                                <small class="text-muted ml-3">
                                                                                                    <i class="fas fa-link mr-1"></i> {{ $cause->recommendations->count() }} Rekomendasi
                                                                                                </small>
                                                                                            </span>
                                                                                            <div class="d-flex">
                                                                                                <button type="button" class="btn btn-xs btn-link p-0 text-info mr-2 btn-link-rec" 
                                                                                                    data-id="{{ $cause->id }}" 
                                                                                                    data-teo="{{ $teo->id }}" 
                                                                                                    data-uraian="{{ $cause->uraian }}"
                                                                                                    data-selected="{{ $cause->recommendations->pluck('id')->join(',') }}"
                                                                                                    title="Hubungkan ke Rekomendasi">
                                                                                                    <i class="fas fa-network-wired"></i>
                                                                                                </button>
                                                                                                <button type="button" class="btn btn-xs btn-link p-0 text-danger btn-delete-cause" data-id="{{ $cause->id }}">
                                                                                                    <i class="fas fa-times"></i>
                                                                                                </button>
                                                                                            </div>
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <small class="text-xs text-muted font-weight-bold text-uppercase">Rekomendasi (AoI)</small>
                                                                                <div id="rec-list-{{ $teo->id }}" class="mt-1">
                                                                                    @foreach($teo->recommendations as $rec)
                                                                                        <div class="d-flex justify-content-between align-items-start mb-1 p-1 rounded rec-item hover-bg-light" id="rec-{{ $rec->id }}">
                                                                                            <span class="text-xs">
                                                                                                <i class="fas fa-check-circle mr-1 text-success"></i> {{ $rec->uraian }}
                                                                                            </span>
                                                                                            <button type="button" class="btn btn-xs btn-link p-0 text-danger btn-delete-rec" data-id="{{ $rec->id }}">
                                                                                                <i class="fas fa-times"></i>
                                                                                            </button>
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
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

    {{-- Modal Edit Indicator --}}
    <div class="modal fade" id="editIndicatorModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-sm" role="document">
            <form id="editIndicatorForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-light py-2">
                        <h6 class="modal-title font-weight-bold">Update Uraian / Bobot</h6>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-2">
                            <label class="text-sm">Uraian / Nama <span class="text-danger">*</span></label>
                            <input type="text" name="uraian" id="editUraianField" class="form-control form-control-sm" required autofocus>
                        </div>
                        <div class="form-group mb-0">
                            <label class="text-sm">Bobot (%)</label>
                            <input type="number" name="bobot" id="editBobotField" class="form-control form-control-sm" step="0.01">
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-xs btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-xs btn-info px-3"><i class="fas fa-save mr-1"></i> Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal TEO --}}
    <div class="modal fade" id="teoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="teoForm" method="POST">
                @csrf
                <input type="hidden" name="_method" id="teoMethod" value="POST">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header bg-warning py-2">
                        <h6 class="modal-title font-weight-bold text-navy"><i class="fas fa-bullseye mr-2"></i> <span id="teoModalTitle">Tambah TEO (Permasalahan)</span></h6>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label class="text-sm">Uraian TEO <span class="text-danger">*</span></label>
                            <textarea name="teo" id="teoInput" class="form-control form-control-sm" rows="3" placeholder="Contoh: Pengelolaan aset belum memadai..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-xs btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-xs btn-warning px-4 font-weight-bold" id="btnSubmitTeo">Simpan TEO</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Cause/Rec --}}
    <div class="modal fade" id="causeRecModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="causeRecForm" method="POST">
                @csrf
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header py-2" id="causeRecHeader">
                        <h6 class="modal-title font-weight-bold"><span id="causeRecModalTitle">Tambah Item</span></h6>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label class="text-sm">Uraian <span class="text-danger">*</span></label>
                            <textarea name="uraian" id="causeRecInput" class="form-control form-control-sm" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-xs btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-xs px-4 font-weight-bold text-white" id="btnSubmitCauseRec">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Linker (Pivot Cause -> Recommendation) --}}
    <div class="modal fade" id="linkerModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form id="linkerForm" method="POST">
                @csrf
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                    <div class="modal-header bg-info py-2">
                        <h6 class="modal-title font-weight-bold text-white"><i class="fas fa-link mr-2"></i> Hubungkan ke Rekomendasi</h6>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-xs text-muted mb-2"><b>Penyebab:</b> <span id="linkerCauseText"></span></p>
                        <label class="text-sm d-block border-bottom pb-1 mb-2">Pilih Rekomendasi yang relevan:</label>
                        <div id="linkerRecOptions" style="max-height: 300px; overflow-y: auto;">
                            {{-- Checkboxes will be injected here --}}
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-xs btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-xs btn-info px-4 font-weight-bold" id="btnSubmitLinker">Simpan Kaitan</button>
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

    // ─── Edit Indicator Modal ────────────────────────────────
    $('#editIndicatorModal').appendTo("body");
    $(document).on('click', '.btn-edit-indicator', function() {
        var id = $(this).data('id');
        var uraian = $(this).data('uraian');
        var bobot = $(this).data('bobot');

        $('#editUraianField').val(uraian);
        $('#editBobotField').val(bobot);
        $('#editIndicatorForm').attr('action', '/indicators/' + id);
        $('#editIndicatorForm').data('id', id);
        
        $('#editIndicatorModal').modal('show');
    });

    $('#editIndicatorForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var url = form.attr('action');
        var id = form.data('id');
        var btn = form.find('button[type="submit"]');
        var newVal = $('#editUraianField').val();
        var newBobot = $('#editBobotField').val();
        var data = form.serialize();

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: url,
            type: 'POST', // Method overriding with _method=PUT is in form
            data: data,
            success: function(resp) {
                if(resp.success) {
                    $('#editIndicatorModal').modal('hide');
                    toastr.success('Berhasil diupdate');
                    // Refresh bobot summary if bobot changed, otherwise just update text
                    location.reload(); 
                }
            },
            error: function(xhr) {
                toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan perubahan');
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update');
            }
        });
    });

    // ─── Inline Edit (Double-click) ──────────────────────
    $(document).on('dblclick', '.editable-text', function() {
        var el = $(this);
        if (el.find('input, select, textarea').length) return; // Already editing

        var type = el.data('type');
        var id = el.data('id');
        var field = el.data('field');
        var currentVal = el.text().trim().replace('%', '');
        var isBobot = field === 'bobot';

        var input;
        if (isBobot) {
            input = $('<input type="number" step="0.01" class="form-control form-control-sm d-inline" style="width:80px;">').val(currentVal);
        } else {
            input = $('<textarea class="form-control form-control-sm d-inline" style="width:100%; resize:vertical; min-height: 48px;"></textarea>').val(currentVal);
        }

        el.data('original', el.html());
        el.html('').append(input);
        input.focus();
        
        if (isBobot) input.select();

        // Use blur for saving
        input.on('blur', function() {
            var newVal = $(this).val();
            if (!newVal || newVal === currentVal) {
                el.html(el.data('original'));
                return;
            }

            var url = type === 'indicator' ? '/indicators/' + id 
                    : (type === 'langkah' ? '/template-langkah/' + id : '/criteria/' + id);
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
                            location.reload(); // Refresh bobot summary
                        } else {
                            el.text(newVal);
                        }
                        toastr.success('Tersimpan');
                    }
                },
                error: function() {
                    el.html(el.data('original'));
                    toastr.error('Gagal menyimpan');
                }
            });
        });

        // Use Escape to cancel
        input.on('keydown', function(e) {
            if (e.key === 'Escape') {
                el.html(el.data('original'));
            } else if (e.key === 'Enter' && isBobot) {
                $(this).blur(); // Trigger save
            }
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
        var btn = $(this);
        var indicatorId = btn.data('indicator');
        var inputEl = btn.siblings('.criteria-input');
        var levelEl = btn.siblings('.criteria-level-input');
        var uraian = inputEl.val();
        var level = levelEl.length ? levelEl.val() : 1;

        if (!uraian) { inputEl.focus(); return; }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/indicators/' + indicatorId + '/criteria',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', uraian: uraian, level: level },
            success: function(resp) {
                btn.prop('disabled', false).html('<i class="fas fa-plus"></i>');
                try {
                    if (resp.success) {
                        var newCriteria = resp.criteria;
                        var criteriaList = $('#criteria-list-' + indicatorId + ' ul');
                        
                        var levelBadge = '';
                        var levelSelect = '';
                        
                        // Only render level parts if the template uses levels
                        if ($('.criteria-level-input').length > 0) {
                            levelBadge = newCriteria.level > 0 ? `<span class="badge badge-light border mr-2 mt-1" style="font-size: 0.65rem;">Lv.${newCriteria.level}</span>` : '';
                            
                            var options = '';
                            for (var lv = 1; lv <= 5; lv++) {
                                options += `<option value="${lv}" ${newCriteria.level == lv ? 'selected' : ''}>Lv.${lv}</option>`;
                            }
                            
                            levelSelect = `
                                <select class="form-control form-control-sm ml-1 criteria-level-select mt-1" data-id="${newCriteria.id}" style="max-width: 65px; height: 24px; font-size: 0.75rem; padding: 0 4px;">
                                    ${options}
                                </select>
                            `;
                        }

                        // Escape HTML for safety and proper format
                        var escapedUraian = $('<div>').text(newCriteria.uraian).html();

                        var newLi = `
                            <li class="text-muted text-sm d-flex align-items-start py-1 criteria-item" id="criteria-${newCriteria.id}">
                                <i class="fas fa-check mr-2 text-success" style="font-size: 0.7rem; margin-top: 0.35rem;"></i>
                                ${levelBadge}
                                <span class="editable-text flex-grow-1" data-type="criteria" data-id="${newCriteria.id}" data-field="uraian" style="white-space: pre-wrap; word-break: break-word;">${escapedUraian}</span>
                                ${levelSelect}
                                <button type="button" class="btn btn-xs text-danger p-0 opacity-50 hover-opacity-100 btn-delete-criteria ml-2 mt-1" data-id="${newCriteria.id}" style="line-height:1">
                                    <i class="fas fa-times"></i>
                                </button>
                            </li>
                        `;
                        
                        criteriaList.append(newLi);
                        inputEl.val('');
                        toastr.success('Kriteria ditambahkan');
                    }
                } catch(e) {
                    console.error("DOM append error:", e);
                    toastr.success('Tersimpan di database (Refresh untuk melihat)');
                    inputEl.val('');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-plus"></i>');
                toastr.error(xhr.responseJSON?.message || 'Gagal menambah kriteria');
            }
        });
    });

    // Ctrl+Enter or Shift+Enter key on criteria config or we just do Ctrl+Enter
    $(document).on('keydown', '.criteria-input', function(e) {
        if (e.key === 'Enter' && e.ctrlKey) {
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

    // ─── AJAX Update Langkah Jenis Prosedur ───────────────
    $(document).on('change', '.langkah-jenis-select', function() {
        var id = $(this).data('id');
        var jenis = $(this).val();

        $.ajax({
            url: '/template-langkah/' + id,
            type: 'POST',
            data: { _method: 'PUT', _token: '{{ csrf_token() }}', jenis_prosedur: jenis },
            success: function(resp) {
                if (resp.success) toastr.success('Jenis Prosedur diupdate');
            },
            error: function() { toastr.error('Gagal update Jenis Prosedur'); }
        });
    });

    // ─── AJAX Add Langkah Kerja ───────────────────────────
    $(document).on('click', '.btn-add-langkah', function() {
        var btn = $(this);
        var indicatorId = btn.data('indicator');
        // Target container securely by adding a debug step
        var inputEl = $('.langkah-input[data-indicator="' + indicatorId + '"]');
        var jenisEl = $('.langkah-jenis-input[data-indicator="' + indicatorId + '"]');
        
        if (inputEl.length === 0) {
            Swal.fire('Error UI', 'Elemen input tidak ditemukan untuk ID indikator ' + indicatorId, 'error');
            return;
        }

        var uraian = inputEl.val() ? inputEl.val().trim() : '';
        var jenis_prosedur = jenisEl.val() ? jenisEl.val() : '';

        if (!uraian) { 
            inputEl.focus(); 
            toastr.warning('Langkah kerja tidak boleh kosong');
            return; 
        }

        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

        $.ajax({
            url: '/indicators/' + indicatorId + '/langkah',
            type: 'POST',
            data: { _token: '{{ csrf_token() }}', uraian: uraian, jenis_prosedur: jenis_prosedur },
            success: function(resp) {
                btn.prop('disabled', false).html('<i class="fas fa-plus mr-1"></i>Tambah');
                try {
                    if (resp.success) {
                        var newLangkah = resp.langkah;
                        var langkahList = $('#langkah-list-' + indicatorId + ' ul');
                        var escapedUraian = $('<div>').text(newLangkah.uraian).html();
                        // Map jenis prosedur to label
                        var map = {
                            '': '-- Jenis --',
                            'wawancara': 'Wawancara', 'observasi': 'Observasi', 'inspeksi_dokumen': 'Inspeksi Dokumen',
                            'analisis_data': 'Analisis Data', 'konfirmasi': 'Konfirmasi', 'rekalkulasi': 'Rekalkulasi',
                            'kuesioner': 'Kuesioner', 'lainnya': 'Lainnya'
                        };

                        var optionsHtml = '';
                        Object.keys(map).forEach(function(k) {
                            optionsHtml += `<option value="${k}" ${newLangkah.jenis_prosedur === k ? 'selected' : ''}>${map[k]}</option>`;
                        });

                        var selectHtml = `
                            <select class="form-control form-control-sm ml-1 langkah-jenis-select mt-1" data-id="${newLangkah.id}" style="max-width: 140px; height: 24px; font-size: 0.75rem; padding: 0 4px;">
                                ${optionsHtml}
                            </select>
                        `;

                        var newLi = `
                            <li class="text-muted text-sm d-flex align-items-start py-1 langkah-item" id="langkah-${newLangkah.id}">
                                <i class="fas fa-circle mr-2 text-primary" style="font-size: 0.4rem; margin-top: 0.5rem;"></i>
                                <span class="editable-text flex-grow-1" data-type="langkah" data-id="${newLangkah.id}" data-field="uraian" style="white-space: pre-wrap; word-break: break-word;">${escapedUraian}</span>
                                ${selectHtml}
                                <button type="button" class="btn btn-xs text-danger p-0 opacity-50 hover-opacity-100 btn-delete-langkah ml-2 mt-1" data-id="${newLangkah.id}" style="line-height:1" title="Hapus Langkah">
                                    <i class="fas fa-times"></i>
                                </button>
                            </li>
                        `;
                        
                        langkahList.append(newLi);
                        inputEl.val('');
                        jenisEl.val('');
                        toastr.success('Langkah Kerja ditambahkan');
                    }
                } catch(e) {
                    console.error("DOM append error:", e);
                    toastr.success('Tersimpan di database (Refresh untuk melihat)');
                    inputEl.val('');
                }
            },
            error: function(xhr) {
                btn.prop('disabled', false).html('<i class="fas fa-plus mr-1"></i>Tambah');
                var errMsg = xhr.responseJSON?.message;
                if (!errMsg) {
                    if (xhr.status === 419) errMsg = "Sesi telah habis (Page Expired). Silakan refresh halaman.";
                    else if (xhr.status === 500) errMsg = "Terjadi kesalahan internal server (500). " + xhr.responseText.substring(0,100) + '...';
                    else errMsg = 'HTTP Error ' + xhr.status + ': ' + xhr.statusText;
                }
                Swal.fire('Gagal Menyimpan', errMsg, 'error');
            }
        });
    });

    // Ctrl+Enter or Shift+Enter key on langkah config
    $(document).on('keydown', '.langkah-input', function(e) {
        if (e.key === 'Enter' && e.ctrlKey) {
            e.preventDefault();
            var indicatorId = $(this).data('indicator');
            $('.btn-add-langkah-' + indicatorId).click();
        }
    });

    // ─── AJAX Delete Langkah Kerja ────────────────────────
    $(document).on('click', '.btn-delete-langkah', function() {
        var id = $(this).data('id');
        var li = $(this).closest('.langkah-item');

        Swal.fire({
            title: 'Hapus langkah kerja ini?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/template-langkah/' + id,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function(resp) {
                        if (resp.success) {
                            li.fadeOut(200, function() { $(this).remove(); });
                            toastr.success('Langkah kerja dihapus');
                        }
                    },
                    error: function() { toastr.error('Gagal menghapus langkah'); }
                });
            }
        });
    });

    // ─── Matriks Temuan (TEO -> Cause -> Rec) Management ───
    $('#teoModal, #causeRecModal, #linkerModal').appendTo("body");

    $(document).on('click', '.btn-add-teo', function() {
        var indicatorId = $(this).data('indicator');
        $('#teoModalTitle').text('Tambah TEO (Permasalahan)');
        $('#teoForm').attr('action', '/templates/indicators/' + indicatorId + '/teos');
        $('#teoMethod').val('POST');
        $('#teoForm')[0].reset();
        $('#teoModal').modal('show');
    });

    $(document).on('click', '.btn-edit-teo', function() {
        var btn = $(this);
        var id = btn.data('id');
        $('#teoModalTitle').text('Edit TEO (Permasalahan)');
        $('#teoForm').attr('action', '/templates/teos/' + id);
        $('#teoMethod').val('PUT');
        $('#teoInput').val(btn.data('teo'));
        $('#teoModal').modal('show');
    });

    $('#teoForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btnSubmitTeo');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(resp) {
                if (resp.success) {
                    $('#teoModal').modal('hide');
                    toastr.success(resp.message);
                    location.reload(); 
                }
            },
            error: function(xhr) { toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan TEO'); },
            complete: function() { btn.prop('disabled', false).text('Simpan TEO'); }
        });
    });

    $(document).on('click', '.btn-delete-teo', function() {
        var id = $(this).data('id');
        var card = $('#teo-' + id);
        Swal.fire({
            title: 'Hapus TEO?',
            text: 'Semua penyebab dan rekomendasi di dalamnya akan ikut dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/templates/teos/' + id,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function(resp) {
                        if (resp.success) {
                            card.fadeOut(300, function() { $(this).remove(); });
                            toastr.success('TEO dihapus');
                        }
                    },
                    error: function() { toastr.error('Gagal menghapus TEO'); }
                });
            }
        });
    });

    // --- Cause & Rec Modals ---
    $(document).on('click', '.btn-modal-cause', function() {
        var teoId = $(this).data('teo');
        $('#causeRecModalTitle').text('Tambah Penyebab (Root Cause)');
        $('#causeRecHeader').removeClass('bg-success').addClass('bg-primary');
        $('#causeRecForm').attr('action', '/templates/teos/' + teoId + '/causes');
        $('#btnSubmitCauseRec').removeClass('btn-success').addClass('btn-primary').text('Simpan Penyebab');
        $('#causeRecForm')[0].reset();
        $('#causeRecModal').modal('show');
    });

    $(document).on('click', '.btn-modal-rec', function() {
        var teoId = $(this).data('teo');
        $('#causeRecModalTitle').text('Tambah Rekomendasi (AoI)');
        $('#causeRecHeader').removeClass('bg-primary').addClass('bg-success');
        $('#causeRecForm').attr('action', '/templates/teos/' + teoId + '/recommendations');
        $('#btnSubmitCauseRec').removeClass('btn-primary').addClass('btn-success').text('Simpan Rekomendasi');
        $('#causeRecForm')[0].reset();
        $('#causeRecModal').modal('show');
    });

    $('#causeRecForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btnSubmitCauseRec');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...');

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(resp) {
                if (resp.success) {
                    $('#causeRecModal').modal('hide');
                    toastr.success(resp.message);
                    location.reload(); 
                }
            },
            error: function(xhr) { toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan item'); },
            complete: function() { btn.prop('disabled', false).text('Simpan'); }
        });
    });

    $(document).on('click', '.btn-delete-cause', function() {
        var id = $(this).data('id');
        var item = $('#cause-' + id);
        $.ajax({
            url: '/templates/causes/' + id,
            type: 'POST',
            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
            success: function(resp) {
                if (resp.success) {
                    item.fadeOut(200, function() { $(this).remove(); });
                    toastr.success('Penyebab dihapus');
                }
            }
        });
    });

    $(document).on('click', '.btn-delete-rec', function() {
        var id = $(this).data('id');
        var item = $('#rec-' + id);
        $.ajax({
            url: '/templates/recommendations/' + id,
            type: 'POST',
            data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
            success: function(resp) {
                if (resp.success) {
                    item.fadeOut(200, function() { $(this).remove(); });
                    toastr.success('Rekomendasi dihapus');
                }
            }
        });
    });

    // --- Linker Management ---
    $(document).on('click', '.btn-link-rec', function() {
        var causeId = $(this).data('id');
        var teoId = $(this).data('teo');
        var uraian = $(this).data('uraian');
        var selectedIds = ($(this).data('selected') + '').split(',');

        $('#linkerCauseText').text(uraian);
        $('#linkerForm').attr('action', '/templates/causes/' + causeId + '/sync-recommendations');
        
        // Get all recommendations for this TEO
        var recContainer = $('#linkerRecOptions');
        recContainer.empty();
        
        $('#rec-list-' + teoId + ' .rec-item').each(function() {
            var recId = $(this).attr('id').replace('rec-', '');
            var recText = $(this).find('span').text().trim();
            var checked = selectedIds.includes(recId) ? 'checked' : '';
            
            recContainer.append(`
                <div class="custom-control custom-checkbox mb-1">
                    <input class="custom-control-input" type="checkbox" name="recommendation_ids[]" id="chk-rec-${recId}" value="${recId}" ${checked}>
                    <label class="custom-control-label text-xs font-weight-normal" for="chk-rec-${recId}">
                        ${recText}
                    </label>
                </div>
            `);
        });

        if (recContainer.children().length === 0) {
            recContainer.html('<p class="text-xs text-danger">Belum ada Rekomendasi di TEO ini.</p>');
        }

        $('#linkerModal').modal('show');
    });

    $('#linkerForm').on('submit', function(e) {
        e.preventDefault();
        var form = $(this);
        var btn = $('#btnSubmitLinker');
        btn.prop('disabled', true);

        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            success: function(resp) {
                if (resp.success) {
                    $('#linkerModal').modal('hide');
                    toastr.success(resp.message);
                    location.reload(); 
                }
            },
            error: function() { toastr.error('Gagal memperbarui kaitan'); },
            complete: function() { btn.prop('disabled', false); }
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
    .hover-info:hover { color: #17a2b8 !important; }
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
