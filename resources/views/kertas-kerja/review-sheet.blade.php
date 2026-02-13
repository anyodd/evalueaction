@extends('adminlte::page')

@section('title', 'Lembar Review - ' . $kk->judul_kk)

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1>Lembar Review Berjenjang</h1>
            </div>
            <div class="col-sm-6 text-right">
                <button onclick="window.print()" class="btn btn-primary no-print shadow-sm">
                    <i class="fas fa-print mr-1"></i> Cetak Lembar Review
                </button>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body p-5">
                {{-- Header Surat (Kop) --}}
                <div class="text-center mb-4">
                    <h4 class="font-weight-bold mb-1">LEMBAR REVIEW BERJENJANG</h4>
                    <h5 class="text-uppercase mb-0">{{ $kk->judul_kk }}</h5>
                    <hr class="border-dark" style="border-width: 2px;">
                </div>

                {{-- Informasi Dasar --}}
                <div class="row mb-4">
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="150" class="font-weight-bold">Nomor Surat Tugas</td>
                                <td width="20">:</td>
                                <td>{{ $kk->suratTugas->nomor_st }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Objek Pengawasan</td>
                                <td>:</td>
                                <td>{{ $kk->suratTugas->nama_objek }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-sm table-borderless">
                            <tr>
                                <td width="150" class="font-weight-bold">Penyusun</td>
                                <td width="20">:</td>
                                <td>{{ $kk->user->name }}</td>
                            </tr>
                            <tr>
                                <td class="font-weight-bold">Status Akhir</td>
                                <td>:</td>
                                <td><span class="badge badge-success">{{ $kk->status_approval }}</span></td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- Tabel Catatan Review --}}
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th width="50">No</th>
                                <th width="150">Tanggal</th>
                                <th width="200">Reviewer / Jabatan</th>
                                <th>Catatan / Tanggapan Review</th>
                                <th width="100">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($kk->reviewNotes as $note)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($note->created_at)->format('d/m/Y') }}<br>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($note->created_at)->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <span class="font-weight-bold">{{ $note->reviewer->name }}</span><br>
                                        @php
                                            $role = \App\Models\StPersonel::where('st_id', $kk->st_id)
                                                ->where('user_id', $note->reviewer_id)
                                                ->value('role_dalam_tim');
                                        @endphp
                                        <small class="badge badge-info">{{ $role ?: 'Reviewer' }}</small>
                                    </td>
                                    <td>{{ $note->catatan }}</td>
                                    <td class="text-center">
                                        @if($note->status == 'Approved')
                                            <span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Setuju</span>
                                        @else
                                            <span class="text-danger font-weight-bold"><i class="fas fa-undo mr-1"></i> Perbaikan</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada catatan review yang terekam.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Tanda Tangan (Opsional) --}}
                <div class="row mt-5">
                    <div class="col-4 text-center">
                        <p class="mb-5">Ketua Tim,</p>
                        <br><br>
                        <p class="font-weight-bold mb-0">( ............................ )</p>
                    </div>
                    <div class="col-4 text-center">
                        <p class="mb-5">Dalnis,</p>
                        <br><br>
                        <p class="font-weight-bold mb-0">( ............................ )</p>
                    </div>
                    <div class="col-4 text-center">
                        <p class="mb-5">Korwas,</p>
                        <br><br>
                        <p class="font-weight-bold mb-0">( ............................ )</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        @media print {
            .no-print { display: none !important; }
            .content-wrapper { background: white !important; }
            .main-footer, .main-header, .sidebar { display: none !important; }
            .card { box-shadow: none !important; border: none !important; }
            .p-5 { padding: 0 !important; }
        }
    </style>
@stop
