<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program Kerja - {{ $pka->judul }}</title>
    <style>
        @page { margin: 2cm; size: A4 landscape; }
        body { font-family: 'Times New Roman', serif; font-size: 12pt; line-height: 1.5; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 3px double #333; padding-bottom: 15px; }
        .header h2 { margin: 0; font-size: 16pt; text-transform: uppercase; letter-spacing: 2px; }
        .header h3 { margin: 5px 0 0; font-size: 13pt; font-weight: normal; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 4px 8px; vertical-align: top; }
        .info-table td:first-child { width: 180px; font-weight: bold; }
        .info-table td:nth-child(2) { width: 10px; text-align: center; }
        table.main { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table.main th, table.main td { border: 1px solid #333; padding: 6px 8px; text-align: left; vertical-align: top; }
        table.main th { background-color: #e9ecef; font-weight: bold; text-align: center; }
        .footer { margin-top: 40px; }
        .sign-table { width: 100%; }
        .sign-table td { width: 50%; text-align: center; vertical-align: top; padding: 10px; }
        .sign-line { margin-top: 60px; border-bottom: 1px solid #333; width: 200px; margin-left: auto; margin-right: auto; }
        @media print { body { margin: 0; } .no-print { display: none; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; cursor: pointer; font-size: 14px; background: #007bff; color: white; border: none; border-radius: 20px;">
            🖨️ Cetak
        </button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer; font-size: 14px; background: #6c757d; color: white; border: none; border-radius: 20px; margin-left: 10px;">
            ✕ Tutup
        </button>
    </div>

    <div class="header">
        <h2>Program Kerja Audit</h2>
        <h3>{{ $pka->judul }}</h3>
    </div>

    <table class="info-table">
        <tr>
            <td>Nomor Surat Tugas</td>
            <td>:</td>
            <td>{{ $pka->suratTugas->nomor_st ?? '-' }}</td>
        </tr>
        <tr>
            <td>Objek Pemeriksaan</td>
            <td>:</td>
            <td>{{ $pka->suratTugas->nama_objek ?? '-' }}</td>
        </tr>
        <tr>
            <td>Jenis Penugasan</td>
            <td>:</td>
            <td>{{ $pka->suratTugas->jenisPenugasan->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td>Tahun Evaluasi</td>
            <td>:</td>
            <td>{{ $pka->suratTugas->tahun_evaluasi ?? '-' }}</td>
        </tr>
        <tr>
            <td>Periode Pelaksanaan</td>
            <td>:</td>
            <td>{{ $pka->tgl_mulai ? $pka->tgl_mulai->format('d/m/Y') : '-' }} s.d. {{ $pka->tgl_selesai ? $pka->tgl_selesai->format('d/m/Y') : '-' }}</td>
        </tr>
        @if($pka->tujuan)
        <tr>
            <td>Tujuan</td>
            <td>:</td>
            <td>{{ $pka->tujuan }}</td>
        </tr>
        @endif
        @if($pka->ruang_lingkup)
        <tr>
            <td>Ruang Lingkup</td>
            <td>:</td>
            <td>{{ $pka->ruang_lingkup }}</td>
        </tr>
        @endif
        @if($pka->metodologi)
        <tr>
            <td>Metodologi</td>
            <td>:</td>
            <td>{{ $pka->metodologi }}</td>
        </tr>
        @endif
    </table>

    <h4 style="margin-bottom: 10px;">Langkah-langkah Program Kerja</h4>
    <table class="main">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="25%">Langkah / Prosedur</th>
                <th width="10%">Jenis</th>
                <th width="15%">Pelaksana</th>
                <th width="10%">Target (Hari)</th>
                <th width="10%">Realisasi</th>
                <th width="10%">Kertas Kerja</th>
                <th width="15%">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 0; @endphp
            @foreach($pka->langkahRoot as $langkah)
                @php $no++; @endphp
                <tr>
                    <td style="text-align: center;">{{ $no }}</td>
                    <td>
                        <strong>{{ $langkah->judul }}</strong>
                        @if($langkah->deskripsi)<br><small>{{ $langkah->deskripsi }}</small>@endif
                    </td>
                    <td>{{ $langkah->jenis_prosedur_label }}</td>
                    <td>{{ $langkah->assignee_names ?: '-' }}</td>
                    <td style="text-align: center;">{{ $langkah->target_hari ?? '-' }}</td>
                    <td style="text-align: center;">
                        @if($langkah->tgl_mulai && $langkah->tgl_selesai)
                            {{ $langkah->tgl_mulai->format('d/m') }} - {{ $langkah->tgl_selesai->format('d/m') }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $langkah->kertasKerja->judul_kk ?? '-' }}</td>
                    <td>{{ $langkah->catatan_hasil ?? '-' }}</td>
                </tr>
                @foreach($langkah->children as $child)
                    @php $no++; @endphp
                    <tr>
                        <td style="text-align: center;">{{ $no }}</td>
                        <td style="padding-left: 24px;">
                            ↳ {{ $child->judul }}
                            @if($child->deskripsi)<br><small>{{ $child->deskripsi }}</small>@endif
                        </td>
                        <td>{{ $child->jenis_prosedur_label }}</td>
                        <td>{{ $child->assignee_names ?: '-' }}</td>
                        <td style="text-align: center;">{{ $child->target_hari ?? '-' }}</td>
                        <td style="text-align: center;">-</td>
                        <td>{{ $child->kertasKerja->judul_kk ?? '-' }}</td>
                        <td>{{ $child->catatan_hasil ?? '-' }}</td>
                    </tr>
                @endforeach
            @endforeach
        </tbody>
    </table>

    <h4 style="margin-bottom: 10px;">Susunan Tim</h4>
    <table class="main">
        <thead>
            <tr>
                <th width="5%">No.</th>
                <th width="40%">Nama</th>
                <th width="20%">NIP</th>
                <th width="20%">Peran dalam Tim</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pka->suratTugas->personel as $idx => $personel)
                <tr>
                    <td style="text-align: center;">{{ $idx + 1 }}</td>
                    <td>{{ $personel->user->name ?? '-' }}</td>
                    <td>{{ $personel->user->nip ?? '-' }}</td>
                    <td>{{ $personel->role_dalam_tim }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <table class="sign-table">
            <tr>
                <td>
                    @php
                        $ketuaTim = $pka->suratTugas->personel->firstWhere('role_dalam_tim', 'Ketua Tim');
                    @endphp
                    <p>Disusun oleh,<br>Ketua Tim</p>
                    <div class="sign-line"></div>
                    <p><strong>{{ $ketuaTim ? $ketuaTim->user->name : '........................' }}</strong><br>
                       NIP. {{ $ketuaTim ? ($ketuaTim->user->nip ?? '-') : '........................' }}</p>
                </td>
                <td>
                    @php
                        $dalnis = $pka->suratTugas->personel->firstWhere('role_dalam_tim', 'Dalnis');
                    @endphp
                    <p>Menyetujui,<br>Pengendali Teknis</p>
                    <div class="sign-line"></div>
                    <p><strong>{{ $dalnis ? $dalnis->user->name : '........................' }}</strong><br>
                       NIP. {{ $dalnis ? ($dalnis->user->nip ?? '-') : '........................' }}</p>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
