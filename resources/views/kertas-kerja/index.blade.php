@extends('adminlte::page')

@section('title', 'Kertas Kerja Saya')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <h1 class="m-0 text-dark font-weight-bold">Kertas Kerja Saya</h1>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="kk-table">
                                <thead class="bg-navy text-white">
                                    <tr>
                                        <th>No</th>
                                        <th>Nomor ST</th>
                                        <th>Judul Penugasan</th>
                                        <th>Jenis Penugasan</th>
                                        <th>Status KK</th>
                                        <th class="text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assignments as $st)
                                        @php
                                            $kk = $st->kertasKerja->first(); // KK milik user ini
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <span class="font-weight-bold d-block">{{ $st->nomor_st }}</span>
                                                <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($st->tgl_st)->format('d M Y') }}</small>
                                            </td>
                                            <td>{{ $st->nama_objek }}</td>
                                            <td>
                                                @if($st->jenisPenugasan)
                                                    <span class="badge badge-info">{{ $st->jenisPenugasan->nama }}</span>
                                                @else
                                                    <span class="badge badge-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($kk)
                                                    @if($kk->status_approval == 'Draft')
                                                        <span class="badge badge-warning">Draft</span>
                                                    @else
                                                        <span class="badge badge-success">{{ $kk->status_approval }}</span>
                                                    @endif
                                                @else
                                                    <span class="badge badge-secondary">Belum Dibuat</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($st->template_id)
                                                    @if($kk)
                                                        <a href="{{ route('kertas-kerja.edit', $kk->id) }}" class="btn btn-primary btn-sm rounded-pill px-3">
                                                            <i class="fas fa-edit mr-1"></i> Lanjutkan
                                                        </a>
                                                    @else
                                                        <a href="{{ route('kertas-kerja.generate', $st->id) }}" class="btn btn-success btn-sm rounded-pill px-3">
                                                            <i class="fas fa-plus mr-1"></i> Buat KK
                                                        </a>
                                                    @endif
                                                @else
                                                    <button class="btn btn-secondary btn-sm rounded-pill px-3" disabled title="Template belum tersedia">
                                                        <i class="fas fa-ban mr-1"></i> N/A
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        .table thead th { vertical-align: middle; border-bottom: none; }
        .table tbody td { vertical-align: middle; }
        .card { transition: transform 0.2s; }
        /* .card:hover { transform: translateY(-5px); } */
    </style>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#kk-table').DataTable({
                "responsive": true,
                "autoWidth": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json"
                }
            });
        });
    </script>
@stop
