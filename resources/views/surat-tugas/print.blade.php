<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat Tugas - {{ $surat_tuga->nomor_st }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 2cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.5;
            color: #000;
        }
        .header-container {
            display: flex;
            align-items: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .logo {
            width: 100px;
            height: auto;
            margin-right: 20px;
        }
        .header-text {
            text-align: center;
            flex-grow: 1;
        }
        .header-text h2 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 0;
            font-size: 10pt;
        }
        .title-container {
            text-align: center;
            margin-bottom: 20px;
        }
        .title-container h3 {
            margin: 0;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .content {
            text-align: justify;
        }
        table.personel-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table.personel-table th, table.personel-table td {
            border: 1px solid #000;
            padding: 5px 10px;
        }
        table.personel-table th {
            background-color: #f0f0f0;
            text-align: center;
        }
        .footer-container {
            margin-top: 30px;
            display: flex;
            justify-content: flex-end;
        }
        .signature-block {
            width: 40%;
            text-align: left;
        }
        .disclaimer {
            margin-top: 20px;
            font-size: 9pt;
        }
        
        @media print {
            body {
                -webkit-print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <div class="header-container">
        <!-- Logo BPKP directly from URL as requested by user -->
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6f/Logo_BPKP_%282020%29.png/600px-Logo_BPKP_%282020%29.png" alt="Logo BPKP" class="logo">
        
        <div class="header-text">
            <h2>BADAN PENGAWASAN KEUANGAN DAN PEMBANGUNAN</h2>
            <h3>PERWAKILAN PROVINSI ACEH</h3> <!-- Assuming Aceh based on user's context, dynamically populated below -->
        </div>
    </div>
    
    <!-- Dynamic Header Override if data exists -->
    @if($surat_tuga->perwakilan)
    <style>
        .header-text h3 { content: "{{ $surat_tuga->perwakilan->nama_perwakilan }}"; }
        .header-details::after { content: "{{ $surat_tuga->perwakilan->alamat }} | Telp: {{ $surat_tuga->perwakilan->telepon }}"; }
    </style>
    <!-- Re-rendering header properly with PHP -->
    <script>
        document.querySelector('.header-text h3').textContent = "{{ strtoupper($surat_tuga->perwakilan->nama_perwakilan) }}";
    </script>
    <div class="header-text" style="display:none;"> <!-- Hidden duplicate for script to grab data if needed, but better to render directly --></div>
    @endif
    
    <!-- Re-render Header with proper PHP logic -->
    <div class="header-container" style="border-bottom: 3px solid #000; margin-top: -120px; background: white; position: relative;">
         <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/6/6f/Logo_BPKP_%282020%29.png/600px-Logo_BPKP_%282020%29.png" alt="Logo BPKP" class="logo">
         <div class="header-text">
            <h2>BADAN PENGAWASAN KEUANGAN DAN PEMBANGUNAN</h2>
            <h3>{{ strtoupper($surat_tuga->perwakilan->nama_perwakilan ?? 'Perwakilan Provinsi Aceh') }}</h3>
            <p>{{ $surat_tuga->perwakilan->alamat ?? 'Jl. T. Panglima Nyak Makam No. 8, Banda Aceh' }}</p>
            <p>Telepon: {{ $surat_tuga->perwakilan->telepon ?? '(0651) 28133' }}, Email: {{ $surat_tuga->perwakilan->email ?? 'aceh@bpkp.go.id' }}</p>
         </div>
    </div>

    <div class="title-container">
        <h3>SURAT TUGAS</h3>
        <p>Nomor: {{ $surat_tuga->nomor_st }}</p>
    </div>

    <div class="content">
        <p>Kepala Perwakilan Badan Pengawasan Keuangan dan Pembangunan Provinsi Aceh dengan ini menugaskan:</p>

        <table class="personel-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th>Nama</th>
                    <th>NIP</th>
                    <th>Jabatan/Peran</th>
                </tr>
            </thead>
            <tbody>
                @foreach($surat_tuga->personel as $index => $personel)
                <tr>
                    <td style="text-align: center;">{{ $index + 1 }}</td>
                    <td>{{ $personel->user->name }}</td>
                    <td style="text-align: center;">{{ $personel->user->nip ?? '-' }}</td>
                    <td>{{ $personel->role_dalam_tim }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <p>Untuk melaksanakan {{ $surat_tuga->nama_objek }}.</p>

        @if($surat_tuga->tgl_mulai && $surat_tuga->tgl_selesai)
        <p>Penugasan ini dilaksanakan selama {{ \Carbon\Carbon::parse($surat_tuga->tgl_mulai)->diffInDays(\Carbon\Carbon::parse($surat_tuga->tgl_selesai)) + 1 }} hari kerja mulai tanggal {{ \Carbon\Carbon::parse($surat_tuga->tgl_mulai)->isoFormat('D MMMM Y') }} sampai dengan tanggal {{ \Carbon\Carbon::parse($surat_tuga->tgl_selesai)->isoFormat('D MMMM Y') }}.</p>
        @else
        <p>Waktu pelaksanaan: {{ \Carbon\Carbon::parse($surat_tuga->tgl_st)->isoFormat('D MMMM Y') }}.</p>
        @endif

        <p>Biaya kegiatan ini dibebankan pada anggaran DIPA Perwakilan BPKP Provinsi Aceh Tahun {{ $surat_tuga->tahun_evaluasi }}.</p>
        
        <div class="disclaimer">
            <p><strong>Catatan:</strong> Pegawai BPKP dalam melaksanakan tugas tidak menerima/meminta gratifikasi dan suap.</p>
        </div>

        <p>Demikian untuk dilaksanakan dengan penuh tanggung jawab.</p>
    </div>

    <div class="footer-container">
        <div class="signature-block">
            <p>{{ $surat_tuga->perwakilan->kota ?? 'Banda Aceh' }}, {{ \Carbon\Carbon::parse($surat_tuga->tgl_st)->isoFormat('D MMMM Y') }}</p>
            <p>Kepala Perwakilan,</p>
            <br><br><br>
            <p><strong>(Nama Kepala Perwakilan)</strong></p>
            <p>NIP. ...........................</p>
        </div>
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
