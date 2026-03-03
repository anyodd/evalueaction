@extends('adminlte::page')

@section('title', 'Program Kerja')

@section('content_header')
    <div class="container-fluid animate__animated animate__fadeIn">
        <div class="d-flex justify-content-between align-items-center">
            <h1 class="m-0 text-dark font-weight-bold">
                <i class="fas fa-tasks text-primary mr-2"></i>Program Kerja
            </h1>
            <a href="{{ route('program-kerja.create') }}" class="btn btn-primary rounded-pill shadow-sm px-4">
                <i class="fas fa-plus mr-2"></i>Buat Program Kerja
            </a>
        </div>
        <small class="text-muted">Kelola program kerja audit/evaluasi/monitoring/reviu</small>
    </div>
@stop

@section('content')
    <div class="container-fluid animate__animated animate__fadeInUp">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" style="border-radius: 10px;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" style="border-radius: 10px;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
            </div>
        @endif

        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body">
                <table id="pka-table" class="table table-hover table-striped table-stack w-100">
                    <thead class="bg-light">
                        <tr>
                            <th width="5%">#</th>
                            <th>Judul PKA</th>
                            <th>Surat Tugas</th>
                            <th>Objek</th>
                            <th width="15%">Progres</th>
                            <th width="10%">Status</th>
                            <th width="12%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($programKerja as $index => $pka)
                            <tr>
                                <td data-label="#">{{ $index + 1 }}</td>
                                <td data-label="Judul PKA">
                                    <a href="{{ route('program-kerja.show', $pka->id) }}" class="font-weight-bold text-primary">
                                        {{ $pka->judul }}
                                    </a>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-user-edit mr-1"></i>{{ $pka->creator->name ?? '-' }}
                                        · {{ $pka->created_at->format('d/m/Y') }}
                                    </small>
                                </td>
                                <td data-label="Surat Tugas">
                                    <small>{{ $pka->suratTugas->nomor_st ?? '-' }}</small>
                                </td>
                                <td data-label="Objek">{{ $pka->suratTugas->nama_objek ?? '-' }}</td>
                                <td data-label="Progres">
                                    @php $progress = $pka->progressPercentage(); @endphp
                                    <div class="progress" style="height: 20px; border-radius: 10px;">
                                        <div class="progress-bar {{ $progress >= 100 ? 'bg-success' : ($progress >= 50 ? 'bg-primary' : 'bg-warning') }}" 
                                             role="progressbar" 
                                             style="width: {{ $progress }}%; border-radius: 10px;"
                                             aria-valuenow="{{ $progress }}">
                                            {{ $progress }}%
                                        </div>
                                    </div>
                                    <small class="text-muted">{{ $pka->langkah->where('status', 'completed')->count() }}/{{ $pka->langkah->count() }} langkah</small>
                                </td>
                                <td data-label="Status">
                                    <span class="badge {{ $pka->status_badge }} p-2">{{ $pka->status_label }}</span>
                                </td>
                                <td data-label="Aksi">
                                        <div class="btn-group">
                                            <a href="{{ route('program-kerja.show', $pka->id) }}" class="btn btn-sm btn-outline-primary" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('program-kerja.edit', $pka->id) }}" class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('program-kerja.print', $pka->id) }}" class="btn btn-sm btn-outline-secondary" title="Cetak" target="_blank">
                                                <i class="fas fa-print"></i>
                                            </a>
                                            @if($pka->status === 'draft')
                                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete-pka" data-id="{{ $pka->id }}" data-judul="{{ $pka->judul }}" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    Belum ada Program Kerja. Klik tombol <strong>"Buat Program Kerja"</strong> untuk memulai.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#pka-table').DataTable({
                responsive: true,
                language: {
                    search: "Cari:",
                    lengthMenu: "Tampilkan _MENU_ data",
                    info: "Menampilkan _START_ - _END_ dari _TOTAL_ data",
                    paginate: { previous: "‹", next: "›" },
                    emptyTable: "Tidak ada data"
                }
            });

            // Delete Program Kerja
            $(document).on('click', '.btn-delete-pka', function() {
                let id = $(this).data('id');
                let judul = $(this).data('judul');
                let url = "{{ route('program-kerja.destroy', ':id') }}";
                url = url.replace(':id', id);

                Swal.fire({
                    title: 'Hapus Program Kerja?',
                    html: `Anda yakin ingin menghapus PKA <strong>"${judul}"</strong>?<br><small class="text-danger">Tindakan ini tidak dapat dibatalkan!</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create a form dynamically and submit it
                        let form = $('<form>', {
                            'method': 'POST',
                            'action': url
                        });

                        form.append($('<input>', {
                            'type': 'hidden',
                            'name': '_token',
                            'value': '{{ csrf_token() }}'
                        }));

                        form.append($('<input>', {
                            'type': 'hidden',
                            'name': '_method',
                            'value': 'DELETE'
                        }));

                        $('body').append(form);
                        form.submit();
                    }
                });
            });
        });
    </script>
@stop
