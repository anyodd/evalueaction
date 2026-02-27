@extends('adminlte::page')

@section('title', 'QA &Laporan')

@section('content_header')
    <h1>Quality Assurance</h1>
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
                                <th>Perwakilan / Objek</th>
                                <th>Nilai Akhir / QA</th>
                                <th>QA Status</th>
                                <th width="250" class="text-center">Laporan & Lampiran</th>
                                <th width="150" class="text-center">Aksi / QA</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($reports as $report)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="font-weight-bold d-block text-dark">{{ optional(optional($report->suratTugas)->perwakilan)->nama_perwakilan ?? 'N/A' }}</span>
                                        <div class="small text-muted mb-1">{{ optional($report->suratTugas)->nama_objek ?? '-' }}</div>
                                        <span class="badge badge-light border">{{ optional($report->suratTugas)->nomor_st ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-1 mb-1">
                                            <span class="text-muted small">Tim:</span> 
                                            <span class="font-weight-bold">{{ number_format($report->nilai_akhir, 2) }}</span>
                                        </div>
                                        @if($report->nilai_akhir_qa)
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-navy small font-weight-bold">QA:</span> 
                                                <span class="font-weight-bold text-navy">{{ number_format($report->nilai_akhir_qa, 2) }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if($report->status_qa == 'Final')
                                            <span class="badge badge-success px-3 py-2 rounded-pill shadow-sm">FINAL</span>
                                            @if(auth()->user()->hasRole('Rendal') || auth()->user()->hasRole('Superadmin'))
                                                <form action="{{ route('kertas-kerja.unfinalize-qa', $report->id) }}" method="POST" class="mt-2">
                                                    @csrf
                                                    <button type="button" class="btn btn-xs btn-outline-danger btn-confirm"
                                                        data-title="Batalkan Final QA?"
                                                        data-text="Apakah Anda yakin ingin membatalkan status Final QA?"
                                                        data-icon="warning"
                                                        data-confirm-text="Ya, Batalkan"
                                                        {{ $report->file_laporan ? 'disabled title="Laporan sudah diupload, tidak bisa dibatalkan"' : '' }}>
                                                        Batalkan QA
                                                    </button>
                                                </form>
                                            @endif
                                        @else
                                            <span class="badge badge-warning px-3 py-2 rounded-pill shadow-sm">DRAFT</span>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        {{-- Only show if Final --}}
                                        @if($report->status_qa == 'Final')
                                            {{-- File Laporan Section --}}
                                            <div class="mb-2 border-bottom pb-2">
                                                @if($report->file_laporan)
                                                    <a href="{{ Storage::url($report->file_laporan) }}" target="_blank" class="btn btn-outline-primary btn-sm btn-block text-left mb-1">
                                                        <i class="fas fa-file-pdf mr-2 text-danger"></i> Download Laporan
                                                    </a>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <small class="text-muted">{{ \Carbon\Carbon::parse($report->updated_at)->format('d/m H:i') }}</small>
                                                        <div>
                                                            {{-- Allow Re-upload for Owner/Admin --}}
                                                            <button type="button" class="btn btn-xs btn-default text-muted mr-1" data-toggle="modal" data-target="#modalUpload-{{ $report->id }}" title="Re-upload">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            {{-- Delete Button for Rendal/Superadmin --}}
                                                            @if(auth()->user()->hasRole('Rendal') || auth()->user()->hasRole('Superadmin'))
                                                                <form action="{{ route('laporan.delete', $report->id) }}" method="POST" style="display:inline">
                                                                    @csrf @method('DELETE')
                                                                    <button type="button" class="btn btn-xs btn-outline-danger btn-confirm"
                                                                        data-title="Hapus Laporan?"
                                                                        data-text="Hapus file laporan akhir ini? Aksi ini akan mengaktifkan kembali tombol Batal QA."
                                                                        data-icon="warning"
                                                                        title="Hapus Laporan">
                                                                        <i class="fas fa-trash"></i>
                                                                    </button>
                                                                </form>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @else
                                                    {{-- Upload Button for Owner --}}
                                                    <button type="button" class="btn btn-primary btn-sm btn-block shadow-sm" data-toggle="modal" data-target="#modalUpload-{{ $report->id }}">
                                                        <i class="fas fa-cloud-upload-alt mr-2"></i> Upload Laporan Akhir
                                                    </button>
                                                    <small class="text-muted d-block text-center mt-1">Belum ada file.</small>
                                                @endif
                                            </div>

                                            {{-- Lampiran Section --}}
                                            <div>
                                                <a href="{{ route('kertas-kerja.print', $report->id) }}" class="btn btn-default btn-sm btn-block text-left" target="_blank">
                                                    <i class="fas fa-print mr-2 text-navy"></i> Cetak Kertas Kerja
                                                </a>
                                            </div>

                                            {{-- Modal Moved to Bottom --}}
                                        @else
                                            <div class="text-center text-muted small">
                                                <i class="fas fa-lock mr-1"></i> Menunggu Finalisasi QA
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center align-middle">
                                        @if(auth()->user()->hasRole('Rendal') || auth()->user()->hasRole('Admin Perwakilan'))
                                            <a href="{{ route('kertas-kerja.qa', $report->id) }}" class="btn btn-navy btn-sm rounded-pill mb-1 btn-block" title="Lakukan QA">
                                                <i class="fas fa-check-double mr-1"></i> Mode QA
                                            </a>
                                        @else
                                            {{-- Team Members --}}
                                            <a href="{{ route('kertas-kerja.qa', $report->id) }}" class="btn btn-info btn-sm rounded-pill mb-1 mr-1 btn-block" title="Lihat Hasil QA">
                                                <i class="fas fa-eye mr-1"></i> Lihat Hasil QA
                                            </a>
                                        @endif
                                        <a href="{{ route('kertas-kerja.review-sheet', $report->id) }}" class="btn btn-default btn-xs btn-block text-muted" target="_blank">
                                            <i class="fas fa-history mr-1"></i> Review Sheet
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-5">
                                        <i class="fas fa-inbox fa-3x mb-3 text-gray-300"></i><br>
                                        Belum ada Kertas Kerja yang berstatus Final.
                                    </td>
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

    {{-- Modals placed outside the table to fix Z-Index issues --}}
    @foreach($reports as $report)
        @if($report->status_qa == 'Final')
            <div class="modal fade" id="modalUpload-{{ $report->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title"><i class="fas fa-upload mr-2"></i> Upload Laporan Akhir</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('laporan.upload', $report->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body text-left">
                                <div class="form-group">
                                    <label>Pilih File Laporan (PDF/DOC, Max 10MB)</label>
                                    <input type="file" name="file_laporan" class="form-control-file" required>
                                </div>
                                <div class="alert alert-info small mb-0">
                                    <i class="fas fa-info-circle mr-1"></i> File ini akan menjadi arsip hasil evaluasi final.
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="submit" class="btn btn-primary">Upload</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Fix Bootstrap Modal Z-Index Issue
            $('.modal').appendTo("body");
        });
    </script>
@stop
