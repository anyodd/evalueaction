<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Surat Tugas - {{ $surat_tuga->nomor_st }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; padding: 40px; }
        .header { text-align: center; border-bottom: 3px double #000; padding-bottom: 20px; margin-bottom: 30px; }
        .title { font-size: 18px; font-weight: bold; text-decoration: underline; }
        .content { line-height: 1.6; }
        .footer { margin-top: 50px; float: right; width: 250px; text-align: center; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Klik Cetak</button>
        <button onclick="window.close()" style="padding: 10px 20px; cursor: pointer;">Tutup</button>
    </div>

    <div class="header">
        <h2>BADAN PENGAWASAN KEUANGAN DAN PEMBANGUNAN</h2>
        <h3>PERWAKILAN PROVINSI {{ strtoupper($surat_tuga->perwakilan->nama_perwakilan ?? 'PUSAT') }}</h3>
    </div>

    <div style="text-align: center;">
        <p class="title">SURAT TUGAS</p>
        <p>Nomor: {{ $surat_tuga->nomor_st }}</p>
    </div>

    <div class="content">
        <p>Dengan ini menugaskan kepada tim tersebut di bawah ini untuk melaksanakan evaluasi pada:</p>
        <table style="width: 100%; border-collapse: collapse; margin-top: 20px;">
            <tr>
                <td style="width: 30%;">Objek Evaluasi</td>
                <td>: {{ $surat_tuga->nama_objek }}</td>
            </tr>
            <tr>
                <td>Tahun Evaluasi</td>
                <td>: {{ $surat_tuga->tahun_evaluasi }}</td>
            </tr>
            <tr>
                <td>Tanggal Penugasan</td>
                <td>: {{ \Carbon\Carbon::parse($surat_tuga->tgl_st)->format('d F Y') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Dikeluarkan di: {{ $surat_tuga->perwakilan->nama_perwakilan ?? 'Pusat' }}</p>
        <p>Pada tanggal: {{ \Carbon\Carbon::parse($surat_tuga->tgl_st)->format('d F Y') }}</p>
        <br><br><br>
        <p><b>Kepala Perwakilan</b></p>
    </div>
</body>
</html>
