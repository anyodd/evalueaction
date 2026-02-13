@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0 text-dark font-weight-bold">Dashboard Overview</h1>
            </div>
            <div class="col-sm-6 text-right">
                <span class="text-muted">Selamat datang kembali, <strong>{{ Auth::user()->name }}</strong></span>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="container-fluid">
        <!-- Dashboard Greeting -->
        <div class="row">
            <div class="col-md-12">
                <div class="card bg-gradient-navy shadow-lg" style="border-radius: 20px; overflow: hidden;">
                    <div class="card-body p-4">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h2 class="display-4 font-weight-bold"><span class="brand-e">e</span>-<span class="brand-value">Value</span>-<span class="brand-a">A</span><span class="brand-ctio">ctio</span><span class="brand-n">N</span></h2>
                                <p class="lead">Sistem Monitoring dan Evaluasi Penugasan Terintegrasi.</p>
                                <a href="{{ route('surat-tugas.create') }}" class="btn btn-outline-light btn-lg rounded-pill px-4 shadow">
                                    <i class="fas fa-plus-circle mr-2"></i> Buat Penugasan Baru
                                </a>
                            </div>
                            <div class="col-md-4 text-center d-none d-md-block opacity-2">
                                <i class="fas fa-chart-pie fa-10x" style="color: rgba(255,255,255,0.1)"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Metric Widgets -->
        <div class="row mt-4">
            <div class="col-lg-3 col-6">
                <div class="small-box shadow animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                    <div class="inner p-4">
                        <h3 class="font-weight-bold">{{ $totalST }}</h3>
                        <p>Total Penugasan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-file-invoice text-white-50"></i>
                    </div>
                    <a href="{{ route('surat-tugas.index') }}" class="small-box-footer py-2">
                        View Details <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-info shadow animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                    <div class="inner p-4">
                        <h3 class="font-weight-bold">{{ $newST }}</h3>
                        <p>Penugasan Baru</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-plus-circle text-white-50"></i>
                    </div>
                    <a href="{{ route('surat-tugas.index') }}?status=Baru" class="small-box-footer py-2">
                        View Details <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-warning shadow animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                    <div class="inner p-4 text-white">
                        <h3 class="font-weight-bold">{{ $activeST }}</h3>
                        <p>Sedang Berjalan</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-spinner text-white-50"></i>
                    </div>
                    <a href="{{ route('surat-tugas.index') }}?status=On Progress" class="small-box-footer py-2">
                        View Details <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="small-box bg-success shadow animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                    <div class="inner p-4">
                        <h3 class="font-weight-bold">{{ $completedST }}</h3>
                        <p>Selesai</p>
                    </div>
                    <div class="icon">
                        <i class="fas fa-check-circle text-white-50"></i>
                    </div>
                    <a href="{{ route('surat-tugas.index') }}?status=Selesai" class="small-box-footer py-2">
                        View Details <i class="fas fa-arrow-circle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Recent Activity & Charts -->
        <div class="row mt-4">
            <div class="col-md-8">
                <div class="card shadow-sm border-0 animate__animated animate__fadeInUp" style="animation-delay: 0.5s">
                    <div class="card-header border-0 bg-white">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-history mr-2 text-primary"></i> Aktivitas Terakhir</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover m-0">
                                <thead>
                                    <tr>
                                        <th>Objek Pengawasan</th>
                                        <th>Jenis</th>
                                        <th>Status</th>
                                        <th>Update Terakhir</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentActivities as $st)
                                        <tr>
                                            <td>{{ $st->nama_objek }}</td>
                                            <td><span class="badge badge-light">{{ $st->jenisPenugasan->nama_jenis ?? '-' }}</span></td>
                                            <td>
                                                @if($st->status == 'Selesai')
                                                    <span class="badge badge-success">Selesai</span>
                                                @elseif($st->status == 'On Progress')
                                                    <span class="badge badge-warning text-white">On Progress</span>
                                                @else
                                                    <span class="badge badge-secondary">Baru</span>
                                                @endif
                                            </td>
                                            <td>{{ $st->updated_at->diffForHumans() }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">Belum ada aktivitas penugasan.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm border-0 animate__animated animate__fadeInUp" style="animation-delay: 0.6s">
                    <div class="card-header border-0 bg-white">
                        <h3 class="card-title font-weight-bold"><i class="fas fa-info-circle mr-2 text-info"></i> Info Perwakilan</h3>
                    </div>
                    <div class="card-body">
                        <div class="text-center py-4">
                             <img src="{{ asset('vendor/adminlte/dist/img/AdminLTELogo.png') }}" alt="Logo" class="img-fluid mb-3" style="width: 80px; opacity: 0.8">
                             <h5 class="font-weight-bold mb-1">{{ Auth::user()->perwakilan->nama_perwakilan ?? 'BPKP PUSAT' }}</h5>
                             <p class="text-muted small">Kode Wilayah: {{ Auth::user()->perwakilan->kode_wilayah ?? '00' }}</p>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Admin Sistem</span>
                            <span class="font-weight-bold text-success"><i class="fas fa-circle fa-xs mr-1"></i> Online</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    {{-- Custom CSS already loaded via config --}}
@stop

@section('js')
    <script>
        $(document).ready(function() {
            // Subtle hover animation for cards
            $('.card').hover(
                function() { $(this).addClass('shadow-lg'); },
                function() { $(this).removeClass('shadow-lg'); }
            );
        });
    </script>
@stop

