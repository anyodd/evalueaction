@extends('adminlte::page')

@section('title', 'Jejak Rekam (Audit Trail)')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <h1 class="m-0 text-dark font-weight-bold">Jejak Rekam (Audit Trail)</h1>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-striped" id="audit-table">
                                <thead class="bg-navy text-white">
                                    <tr>
                                        <th>Waktu</th>
                                        <th>User</th>
                                        <th>Kertas Kerja</th>
                                        <th>Aksi</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($audits as $audit)
                                        <tr>
                                            <td>{{ $audit->created_at->format('d M Y H:i:s') }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="mr-2">
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($audit->user->name ?? 'System') }}&background=random" class="img-circle elevation-1" width="30">
                                                    </div>
                                                    <div>
                                                        <span class="font-weight-bold d-block">{{ $audit->user->name ?? 'System' }}</span>
                                                        <small class="text-muted">{{ $audit->user->roles->pluck('name')->first() ?? '' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                @if($audit->kertasKerja)
                                                    <a href="{{ route('kertas-kerja.edit', $audit->kertasKerja->id) }}" class="text-navy font-weight-bold">
                                                        {{ $audit->kertasKerja->judul_kk }}
                                                    </a>
                                                    <br>
                                                    <small class="text-muted">ST: {{ $audit->kertasKerja->suratTugas->nomor_st ?? '-' }}</small>
                                                @else
                                                    <span class="text-muted">N/A</span>
                                                @endif
                                            </td>
                                            <td>
                                                @php
                                                    $badgeClass = 'badge-secondary';
                                                    if(strtolower($audit->action) == 'simpan') $badgeClass = 'badge-primary';
                                                    if(strtolower($audit->action) == 'kirim') $badgeClass = 'badge-info';
                                                    if(strtolower($audit->action) == 'setuju') $badgeClass = 'badge-success';
                                                    if(strtolower($audit->action) == 'tolak') $badgeClass = 'badge-danger';
                                                    if(strtolower($audit->action) == 'export') $badgeClass = 'badge-success';
                                                    if(strtolower($audit->action) == 'import') $badgeClass = 'badge-warning';
                                                @endphp
                                                <span class="badge {{ $badgeClass }} px-2 py-1">{{ $audit->action }}</span>
                                            </td>
                                            <td>{{ $audit->description }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $audits->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script>
        $(document).ready(function() {
            $('#audit-table').DataTable({
                "paging": false,
                "ordering": true,
                "info": false,
                "searching": true,
                "autoWidth": false,
                "responsive": true,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/Indonesian.json"
                }
            });
        });
    </script>
@stop
