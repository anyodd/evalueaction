@extends('adminlte::page')

@section('title', 'Builder Template PKA')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="text-dark font-weight-light mb-0">
                <i class="fas fa-clipboard-list text-primary mr-2"></i>
                {{ $template->judul }}
                @if($template->status === 'published')
                    <span class="badge badge-success ml-2" style="font-size: 0.5em;">Published</span>
                @else
                    <span class="badge badge-secondary ml-2" style="font-size: 0.5em;">Draft</span>
                @endif
            </h1>
            <small class="text-muted">{{ $totalLangkah }} langkah · Dibuat oleh {{ $template->creator->name ?? '-' }}</small>
        </div>
        <div>
            @if($template->status === 'draft')
                <form action="{{ route('template-pka.update', $template->id) }}" method="POST" style="display:inline">
                    @csrf @method('PUT')
                    <input type="hidden" name="judul" value="{{ $template->judul }}">
                    <input type="hidden" name="status" value="published">
                    <button type="submit" class="btn btn-success btn-sm mr-1" onclick="return confirm('Publish template ini? Setelah publish, template dapat digunakan oleh Perwakilan.')">
                        <i class="fas fa-globe mr-1"></i> Publish
                    </button>
                </form>
            @else
                <form action="{{ route('template-pka.update', $template->id) }}" method="POST" style="display:inline">
                    @csrf @method('PUT')
                    <input type="hidden" name="judul" value="{{ $template->judul }}">
                    <input type="hidden" name="status" value="draft">
                    <button type="submit" class="btn btn-outline-secondary btn-sm mr-1" onclick="return confirm('Tarik kembali ke Draft?')">
                        <i class="fas fa-undo mr-1"></i> Unpublish
                    </button>
                </form>
            @endif
            <a href="{{ route('template-pka.edit', $template->id) }}" class="btn btn-warning btn-sm mr-1"><i class="fas fa-edit mr-1"></i> Edit Info</a>
            <a href="{{ route('template-pka.index') }}" class="btn btn-default btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>{{ session('success') }}</div>
    @endif

    {{-- Template Info Summary --}}
    @if($template->deskripsi || $template->tujuan || $template->ruang_lingkup || $template->metodologi)
    <div class="card shadow-sm border-0 mb-3" style="border-radius: 10px;">
        <div class="card-body py-2">
            <div class="row text-sm">
                @if($template->tujuan)
                <div class="col-md-4"><strong class="text-muted">Tujuan:</strong><br>{{ \Illuminate\Support\Str::limit($template->tujuan, 100) }}</div>
                @endif
                @if($template->ruang_lingkup)
                <div class="col-md-4"><strong class="text-muted">Ruang Lingkup:</strong><br>{{ \Illuminate\Support\Str::limit($template->ruang_lingkup, 100) }}</div>
                @endif
                @if($template->metodologi)
                <div class="col-md-4"><strong class="text-muted">Metodologi:</strong><br>{{ \Illuminate\Support\Str::limit($template->metodologi, 100) }}</div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Langkah Builder --}}
    <div class="card shadow-sm border-0" style="border-radius: 10px;">
        <div class="card-header bg-gradient-light py-2 d-flex justify-content-between align-items-center">
            <h5 class="m-0 font-weight-bold text-dark"><i class="fas fa-list-ol mr-2"></i>Langkah-langkah Audit/Evaluasi</h5>
            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#addLangkahModal" data-parent="">
                <i class="fas fa-plus mr-1"></i> Tambah Langkah
            </button>
        </div>
        <div class="card-body p-0">
            @if($langkahRoot->count())
                <div class="list-group list-group-flush" id="langkah-list">
                    @foreach($langkahRoot as $langkah)
                        @include('template-pka.partials.langkah-item', ['langkah' => $langkah, 'depth' => 0])
                    @endforeach
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-clipboard-list fa-3x mb-3" style="color: #dee2e6;"></i>
                    <p>Belum ada langkah. Klik <strong>Tambah Langkah</strong> untuk memulai.</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="addLangkahModal" tabindex="-1">
        <div class="modal-dialog">
            <form action="{{ route('template-pka.langkah.store', $template->id) }}" method="POST">
                @csrf
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-light py-2">
                        <h6 class="modal-title font-weight-bold"><i class="fas fa-plus mr-1"></i> Tambah Langkah</h6>
                        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="parent_id" id="langkahParentId">
                        <div class="form-group mb-2">
                            <label class="text-sm font-weight-bold">Judul Langkah <span class="text-danger">*</span></label>
                            <input type="text" name="judul" class="form-control" required placeholder="Contoh: Review Dokumen Perencanaan">
                        </div>
                        <div class="form-group mb-2">
                            <label class="text-sm">Deskripsi / Prosedur</label>
                            <textarea name="deskripsi" class="form-control" rows="2" placeholder="Detail langkah yang harus dilakukan..."></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="text-sm">Jenis Prosedur</label>
                                    <select name="jenis_prosedur" class="form-control form-control-sm">
                                        <option value="">— Pilih —</option>
                                        <option value="wawancara">Wawancara</option>
                                        <option value="observasi">Observasi</option>
                                        <option value="inspeksi_dokumen">Inspeksi Dokumen</option>
                                        <option value="analisis_data">Analisis Data</option>
                                        <option value="konfirmasi">Konfirmasi</option>
                                        <option value="rekalkulasi">Rekalkulasi</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label class="text-sm">Target (hari kerja)</label>
                                    <input type="number" name="target_hari" class="form-control form-control-sm" min="1" placeholder="3">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light">
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-primary px-3">Simpan</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('js')
<script>
    $('#addLangkahModal').appendTo('body');
    $('#addLangkahModal').on('show.bs.modal', function(e) {
        var parentId = $(e.relatedTarget).data('parent') || '';
        $(this).find('#langkahParentId').val(parentId);
    });

    // AJAX Delete
    $(document).on('click', '.btn-delete-langkah', function() {
        var id = $(this).data('id');
        var el = $(this).closest('.langkah-item');

        Swal.fire({
            title: 'Hapus langkah ini?',
            text: 'Sub-langkah juga akan dihapus.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/template-pka/langkah/' + id,
                    type: 'POST',
                    data: { _method: 'DELETE', _token: '{{ csrf_token() }}' },
                    success: function(resp) {
                        if (resp.success) {
                            el.fadeOut(300, function() { $(this).remove(); });
                            toastr.success('Langkah dihapus');
                        }
                    },
                    error: function() { toastr.error('Gagal menghapus'); }
                });
            }
        });
    });

    // Inline edit (double-click)
    $(document).on('dblclick', '.langkah-editable', function() {
        var el = $(this);
        if (el.find('input').length) return;

        var id = el.data('id');
        var field = el.data('field');
        var current = el.text().trim();
        var input = $('<input type="text" class="form-control form-control-sm d-inline" style="width:400px;">').val(current);

        el.data('original', el.html());
        el.html('').append(input);
        input.focus().select();

        input.on('blur keydown', function(e) {
            if (e.type === 'keydown' && e.key !== 'Enter') return;
            e.preventDefault();
            var newVal = $(this).val();
            if (!newVal || newVal === current) { el.html(el.data('original')); return; }

            $.ajax({
                url: '/template-pka/langkah/' + id,
                type: 'POST',
                data: { _method: 'PUT', _token: '{{ csrf_token() }}', [field]: newVal },
                success: function() { el.text(newVal); toastr.success('Tersimpan'); },
                error: function() { el.html(el.data('original')); toastr.error('Gagal'); }
            });
        });
    });
</script>
<style>
    .langkah-editable { cursor: default; border-bottom: 1px dashed transparent; transition: all 0.2s; }
    .langkah-editable:hover { border-bottom-color: #007bff; cursor: text; }
</style>
@stop
