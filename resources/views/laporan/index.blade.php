@extends('adminlte::page')

@section('title', 'Pelaporan & QA')

@section('content_header')
    <h1>Pelaporan & QA (Rendal)</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">Daftar Evaluasi Final</h3>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <p>Halaman ini menampilkan Kertas Kerja yang sudah disetujui Korwas dan menunggu QA akhir Rendal.</p>
                     <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Perwakilan</th>
                                <th>Objek Evaluasi</th>
                                <th>Tanggal Selesai</th>
                                <th width="150">Nilai Akhir</th>
                                <th>QA Status</th>
                                <th width="150" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="font-weight-bold d-block">{{ optional(optional($report->suratTugas)->perwakilan)->nama_perwakilan ?? 'N/A' }}</span>
                                        <small class="text-muted">{{ optional($report->suratTugas)->nomor_st ?? '-' }}</small>
                                    </td>
                                    <td>{{ optional($report->suratTugas)->nama_objek ?? '-' }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($report->updated_at)->format('d M Y') }}
                                    </td>
                                    <td>
                                        <div>
                                            <span class="text-muted small">Tim:</span> 
                                            <span class="font-weight-bold">{{ number_format($report->nilai_akhir, 2) }}</span>
                                        </div>
                                        @if($report->nilai_akhir_qa)
                                            <div class="mt-1">
                                                <span class="text-navy small font-weight-bold">QA:</span> 
                                                <span class="font-weight-bold text-navy">{{ number_format($report->nilai_akhir_qa, 2) }}</span>
                                                @if($report->nilai_akhir_qa > $report->nilai_akhir)
                                                    <i class="fas fa-arrow-up text-success small ml-1" title="Naik"></i>
                                                @elseif($report->nilai_akhir_qa < $report->nilai_akhir)
                                                    <i class="fas fa-arrow-down text-danger small ml-1" title="Turun"></i>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center"><span class="badge badge-success">FINAL</span></td>
                                    <td class="text-center">
                                        @if(auth()->user()->hasRole('Rendal') || auth()->user()->hasRole('Admin Perwakilan'))
                                            <a href="{{ route('kertas-kerja.qa', $report->id) }}" class="btn btn-navy btn-sm rounded-pill mb-1 mr-1" title="Lakukan QA">
                                                <i class="fas fa-check-double"></i> QA
                                            </a>
                                        @endif

                                        <a href="{{ route('kertas-kerja.review-sheet', $report->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill mb-1 mr-1" target="_blank" title="Cetak Lembar Review">
                                            <i class="fas fa-print"></i>
                                        </a>
                                        <a href="{{ route('kertas-kerja.edit', $report->id) }}" class="btn btn-primary btn-sm rounded-pill mb-1" title="Lihat Dokumen">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Belum ada Kertas Kerja yang berstatus Final.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
@stop
