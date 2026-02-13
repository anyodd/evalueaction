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
                                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif

                        @if (session('warning'))
                            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle mr-2"></i> {{ session('warning') }}
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
                                                        @php
                                                            $myRole = \App\Models\StPersonel::where('st_id', $st->id)
                                                                ->where('user_id', auth()->id())
                                                                ->value('role_dalam_tim');
                                                            $isSuperadmin = auth()->user()->hasRole('Superadmin');
                                                        @endphp

                                                        {{-- Edit Button (Only if Draft or User is Reviewer/Superadmin) --}}
                                                        <a href="{{ route('kertas-kerja.edit', $kk->id) }}" class="btn btn-primary btn-sm rounded-pill px-3 mb-1">
                                                            <i class="fas fa-edit mr-1"></i> Buka KK
                                                        </a>

                                                        {{-- Submit Button (Draft -> Ketua OR Dalnis) --}}
                                                        @if(($kk->status_approval == 'Draft' || str_starts_with($kk->status_approval, 'Revisi')) && $kk->user_id == auth()->id())
                                                            <form action="{{ route('kertas-kerja.submit', $kk->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                @if($myRole == 'Ketua Tim')
                                                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 mb-1" onclick="return confirm('Kirim ke Dalnis? Data akan dikunci.')">
                                                                        <i class="fas fa-paper-plane mr-1"></i> Kirim ke Dalnis
                                                                    </button>
                                                                @else
                                                                    <button type="submit" class="btn btn-info btn-sm rounded-pill px-3 mb-1" onclick="return confirm('Lapor ke Ketua Tim bahwa KK sudah selesai diisi?')">
                                                                        <i class="fas fa-bullhorn mr-1"></i> Lapor ke Ketua
                                                                    </button>
                                                                @endif
                                                            </form>
                                                        @endif

                                                        {{-- Approval Buttons --}}
                                                        @if($kk->status_approval == 'Review Ketua' && ($myRole == 'Ketua Tim' || $isSuperadmin))
                                                            <form action="{{ route('kertas-kerja.approve', $kk->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 mb-1" title="Kirim ke Dalnis" onclick="return confirm('Kirim ke Dalnis? Data akan dikunci.')">
                                                                    <i class="fas fa-paper-plane mr-1"></i> Kirim ke Dalnis
                                                                </button>
                                                            </form>
                                                            <form action="{{ route('kertas-kerja.reject', $kk->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-warning btn-sm rounded-pill px-3 mb-1" title="Kembalikan ke Anggota" onclick="return confirm('Kembalikan ke Anggota?')">
                                                                    <i class="fas fa-undo mr-1"></i> Kembalikan
                                                                </button>
                                                            </form>
                                                        @elseif(
                                                            ($kk->status_approval == 'Review Dalnis' && ($myRole == 'Dalnis' || $isSuperadmin)) ||
                                                            ($kk->status_approval == 'Review Korwas' && ($myRole == 'Korwas'  || $isSuperadmin))
                                                        )
                                                            <form action="{{ route('kertas-kerja.approve', $kk->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 mb-1" title="Setuju">
                                                                    <i class="fas fa-check"></i> Approve
                                                                </button>
                                                            </form>
                                                            <form action="{{ route('kertas-kerja.reject', $kk->id) }}" method="POST" class="d-inline">
                                                                @csrf
                                                                <button type="submit" class="btn btn-danger btn-sm rounded-pill px-3 mb-1" title="Tolak / Revisi" onclick="return confirm('Kembalikan untuk revisi?')">
                                                                    <i class="fas fa-times"></i> Reject
                                                                </button>
                                                            </form>
                                                        @endif

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
