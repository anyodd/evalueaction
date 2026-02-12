@extends('adminlte::page')

@section('title', 'Review Berjenjang')

@section('content_header')
    <h1>Review Berjenjang</h1>
@stop

@section('content')
    <div class="card card-warning card-outline">
        <div class="card-header">
            <h3 class="card-title">Antrian Review</h3>
        </div>
        <div class="card-body">
            <p class="text-muted">Daftar Kertas Kerja yang perlu direview oleh Anda.</p>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Prioritas</th>
                        <th>Nomor ST</th>
                        <th>Judul KK</th>
                        <th>Diajukan Oleh</th>
                        <th>Status Saat Ini</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                     <tr>
                        <td colspan="6" class="text-center">Tidak ada item yang perlu direview</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@stop
