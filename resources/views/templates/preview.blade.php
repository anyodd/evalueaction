@extends('adminlte::page')

@section('title', 'Preview Template')

@section('css')
<style>
    .preview-card { border-radius: 10px; }
    .preview-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 10px 10px 0 0; }
    .aspect-card { border-left: 4px solid #667eea; }
    .indicator-card { border-left: 3px solid #17a2b8; background: #f8f9fa; }
    .parameter-card { border-left: 3px solid #6c757d; }
    .criteria-row:nth-child(even) { background: #fafbfc; }
    .level-header { background: #eef2ff; border-left: 2px solid #667eea; }
    .radio-preview { pointer-events: none; opacity: 0.5; }
</style>
@stop

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-dark font-weight-light mb-0">
                <i class="fas fa-eye text-info mr-2"></i> Preview: <b>{{ $template->nama }}</b>
            </h1>
            <small class="text-muted">Tampilan ini menunjukkan bagaimana template akan terlihat oleh auditor di Perwakilan saat mengisi Kertas Kerja.</small>
        </div>
        <div>
            <a href="{{ route('templates.builder', $template->id) }}" class="btn btn-info btn-sm"><i class="fas fa-tools mr-1"></i> Kembali ke Builder</a>
        </div>
    </div>
@stop

@section('content')
    @php
        $metode = $template->metode_penilaian ?? 'tally';
        $isLevel = in_array($metode, ['building_block', 'criteria_fulfillment']);
        $isBB = $metode === 'building_block';
        $metodeLabels = ['tally' => 'Tally (%)', 'building_block' => 'Building Block (Lv 1-5)', 'criteria_fulfillment' => 'Pemenuhan Kriteria (Lv 1-5)'];
    @endphp

    {{-- Info Bar --}}
    <div class="callout callout-info">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $template->jenisPenugasan->nama ?? '-' }}</strong> · Tahun {{ $template->tahun }}
                <span class="badge badge-primary ml-2">{{ $metodeLabels[$metode] ?? 'Tally' }}</span>
            </div>
            <div>
                @php $totalBobot = $indicators->sum('bobot'); @endphp
                <span class="badge {{ abs($totalBobot - 100) < 0.01 ? 'badge-success' : 'badge-danger' }} p-2">
                    Total Bobot: {{ number_format($totalBobot, 2) }}%
                </span>
            </div>
        </div>
    </div>

    {{-- Score Dashboard Preview --}}
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px; position: sticky; top: 57px; z-index: 100;">
        <div class="card-body py-2">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div class="d-flex align-items-center flex-wrap">
                    <small class="text-muted mr-2 font-weight-bold"><i class="fas fa-chart-bar mr-1"></i>Skor:</small>
                    @foreach($indicators as $asp)
                        <span class="badge badge-light border mr-2 p-2 mb-1">
                            {{ \Illuminate\Support\Str::limit($asp->uraian, 18) }}:
                            <strong>{{ $isLevel ? '0.00' : '0.0%' }}</strong>
                        </span>
                    @endforeach
                </div>
                <div class="d-flex align-items-center">
                    <span class="text-muted mr-2 font-weight-bold">Skor Akhir:</span>
                    <span class="badge badge-secondary p-2 px-3" style="font-size: 1.1em">{{ $isLevel ? '0.00' : '0.00%' }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Template Content --}}
    @foreach($indicators as $aspect)
        <div class="card mb-3 aspect-card shadow-sm">
            <div class="card-header py-2 bg-gradient-light">
                <h5 class="m-0 text-primary font-weight-bold">
                    <i class="fas fa-layer-group mr-2"></i>{{ $aspect->uraian }}
                    <span class="badge badge-outline-primary ml-1">{{ $aspect->bobot }}%</span>
                </h5>
            </div>
            <div class="card-body p-0">
                @foreach($aspect->children as $indicator)
                    <div class="p-3 pl-4 border-bottom">
                        <h6 class="text-info font-weight-bold mb-3">
                            <i class="fas fa-chevron-right mr-2 text-xs text-muted"></i>{{ $indicator->uraian }}
                            <span class="badge badge-info ml-1">{{ $indicator->bobot }}%</span>
                        </h6>

                        @foreach($indicator->children as $parameter)
                            <div class="ml-4 mb-3 pl-3 parameter-card rounded p-2">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-weight-bold text-dark">
                                        <i class="far fa-dot-circle mr-2 text-secondary text-xs"></i>{{ $parameter->uraian }}
                                        <small class="text-muted">({{ $parameter->bobot }}%)</small>
                                    </span>
                                </div>

                                {{-- Criteria Table --}}
                                @if($parameter->criteria->count())
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover mb-0 w-100" style="font-size: 0.85rem;">
                                        <thead class="bg-light text-center">
                                            <tr>
                                                @if($isLevel)
                                                    <th style="width: 60px;">Level</th>
                                                @endif
                                                <th style="min-width: 250px;">Uraian Kriteria</th>
                                                <th style="min-width: 150px;">Nilai</th>
                                                <th style="min-width: 180px;">Bukti Dukung</th>
                                                <th style="min-width: 150px;">Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $lastLevel = null; @endphp
                                            @foreach($parameter->criteria->sortBy('level') as $criteria)
                                                @if($isLevel && $criteria->level > 0 && $criteria->level !== $lastLevel)
                                                    <tr class="level-header">
                                                        <td colspan="{{ $isLevel ? 5 : 4 }}" class="py-1">
                                                            <strong class="text-primary">
                                                                <i class="fas fa-layer-group mr-1"></i>Level {{ $criteria->level }}
                                                            </strong>
                                                        </td>
                                                    </tr>
                                                    @php $lastLevel = $criteria->level; @endphp
                                                @endif
                                                <tr class="criteria-row">
                                                    @if($isLevel)
                                                        <td class="text-center align-middle">
                                                            <span class="badge badge-light border">Lv.{{ $criteria->level }}</span>
                                                        </td>
                                                    @endif
                                                    <td class="align-middle">{{ $criteria->uraian }}</td>
                                                    <td class="align-middle text-center">
                                                        <div class="d-flex justify-content-center radio-preview">
                                                            <div class="icheck-success d-inline mr-2">
                                                                <input type="radio" disabled id="prev-{{ $criteria->id }}-f"><label for="prev-{{ $criteria->id }}-f">Ya</label>
                                                            </div>
                                                            @if(!$isBB)
                                                            <div class="icheck-warning d-inline mr-2">
                                                                <input type="radio" disabled id="prev-{{ $criteria->id }}-p"><label for="prev-{{ $criteria->id }}-p">Sebagian</label>
                                                            </div>
                                                            @endif
                                                            <div class="icheck-danger d-inline">
                                                                <input type="radio" disabled checked id="prev-{{ $criteria->id }}-n"><label for="prev-{{ $criteria->id }}-n">Tidak</label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="align-middle text-center text-muted"><small><i class="fas fa-upload mr-1"></i>Upload / Link</small></td>
                                                    <td class="align-middle text-center text-muted"><small><i class="fas fa-pen mr-1"></i>Catatan...</small></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-1 text-right">
                                    @if($isLevel)
                                        <span class="badge badge-info p-2" style="font-size: 0.8em;">Skor: <strong>0.00</strong> (Level 0)</span>
                                    @else
                                        <span class="badge badge-info p-2" style="font-size: 0.8em;">Skor Parameter: 0%</span>
                                    @endif
                                </div>
                                @else
                                    <div class="text-muted text-center py-3 bg-light rounded">
                                        <i class="fas fa-exclamation-circle mr-1"></i> Belum ada kriteria untuk parameter ini.
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    @if($indicators->isEmpty())
        <div class="text-center py-5 text-muted">
            <i class="fas fa-drafting-compass fa-3x mb-3 text-light"></i>
            <p>Template ini belum memiliki indikator. Silakan buka <a href="{{ route('templates.builder', $template->id) }}">Builder</a> untuk menambah.</p>
        </div>
    @endif
@stop
