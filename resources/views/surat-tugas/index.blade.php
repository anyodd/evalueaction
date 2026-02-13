@extends('adminlte::page')

@section('title', 'Surat Tugas')

@section('content_header')
    <h1>Daftar Surat Tugas</h1>
@stop

@section('content')
    <div class="animate__animated animate__fadeIn">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius: 10px;">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="card-title font-weight-bold text-navy"><i class="fas fa-file-alt mr-2"></i> Manajemen Surat Tugas</h3>
                <div class="card-tools">
                    <a href="{{ route('surat-tugas.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"> <i class="fas fa-plus mr-1"></i> Buat ST Baru</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-4">No. ST / Tanggal</th>
                                <th>Objek Evaluasi</th>
                                <th>Tahun</th>
                                <th>Personel</th>
                                <th class="text-right pr-4" style="width: 180px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($suratTugas as $st)
                                <tr>
                                    <td class="pl-4">
                                        <span class="font-weight-bold text-navy d-block">{{ $st->nomor_st }}</span>
                                        <small class="text-muted"><i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($st->tgl_st)->format('d M Y') }}</small>
                                    </td>
                                    <td>
                                        <span class="d-block font-weight-bold">{{ $st->nama_objek }}</span>
                                        <small class="text-muted badge bg-light border"><i class="fas fa-map-marker-alt mr-1"></i> {{ $st->perwakilan->nama_perwakilan ?? '-' }}</small>
                                    </td>
                                    <td><span class="badge border border-info text-info px-2 py-1">{{ $st->tahun_evaluasi }}</span></td>
                                    <td>
                                        <div class="avatar-group d-flex align-items-center">
                                            <span class="badge bg-navy rounded-pill px-2 shadow-sm">{{ $st->personel()->count() }} Personel</span>
                                        </div>
                                    </td>
                                    <td class="text-right pr-4">
                                        <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                            <a href="{{ route('surat-tugas.print', $st->id) }}" target="_blank" class="btn btn-default btn-xs shadow-none" title="Cetak"><i class="fas fa-print"></i></a>
                                            <a href="{{ route('surat-tugas.edit', $st->id) }}" class="btn btn-warning btn-xs shadow-none text-white" title="Edit"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('surat-tugas.destroy', $st->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs shadow-none" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus ST ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-file-invoice fa-3x mb-3 opacity-2"></i><br>
                                            Belum ada surat tugas yang diterbitkan.
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
