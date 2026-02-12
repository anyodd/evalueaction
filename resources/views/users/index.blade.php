@extends('adminlte::page')

@section('title', 'Manajemen User')

@section('content_header')
    <h1>Daftar Pengguna</h1>
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

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-4 shadow-sm" role="alert" style="border-radius: 10px;">
                <i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        @endif

        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-header bg-white border-0 py-3">
                <h3 class="card-title font-weight-bold text-navy"><i class="fas fa-users mr-2"></i> User System</h3>
                <div class="card-tools">
                    <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm"> <i class="fas fa-plus mr-1"></i> User Baru</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle m-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="pl-4">Profil</th>
                                <th>Email / NIP</th>
                                <th>Role</th>
                                <th>Perwakilan</th>
                                <th class="text-right pr-4" style="width: 150px">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td class="pl-4">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-navy rounded-circle d-flex align-items-center justify-content-center mr-3 shadow-sm" style="width: 40px; height: 40px;">
                                                <i class="fas fa-user text-white"></i>
                                            </div>
                                            <div>
                                                <span class="font-weight-bold text-navy d-block">{{ $user->name }}</span>
                                                <small class="text-muted">ID: #{{ $user->id }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="d-block">{{ $user->email }}</span>
                                        <small class="text-muted font-italic">{{ $user->nip ?? 'NIP Belum Diisi' }}</small>
                                    </td>
                                    <td><span class="badge border border-navy text-navy px-2 py-1">{{ $user->role->name ?? '-' }}</span></td>
                                    <td><span class="text-muted">{{ $user->perwakilan->nama_perwakilan ?? 'Pusat' }}</span></td>
                                    <td class="text-right pr-4">
                                        <div class="btn-group shadow-sm" style="border-radius: 8px; overflow: hidden;">
                                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning btn-xs shadow-none" title="Edit"><i class="fas fa-edit"></i></a>
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-xs shadow-none" title="Hapus" onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5">Data user tidak ditemukan.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop
