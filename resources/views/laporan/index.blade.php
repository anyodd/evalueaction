@extends('adminlte::page')

@section('title', 'Pelaporan & QA')

@section('content_header')
    <h1>Pelaporan & QA (Rendal)</h1>
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
                                <th>No</th>
                                <th>Perwakilan</th>
                                <th>Objek Evaluasi</th>
                                <th>Tanggal Selesai</th>
                                <th>QA Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                             <tr>
                                <td colspan="6" class="text-center">Belum ada data final</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
        <!-- /.col -->
    </div>
@stop
