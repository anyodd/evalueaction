<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kertas Kerja Evaluasi - {{ $kertasKerja->suratTugas->nama_objek ?? 'Objek Evaluasi' }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; line-height: 1.4; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h1 { margin: 0; font-size: 18px; text-transform: uppercase; }
        .header h2 { margin: 5px 0 0; font-size: 14px; font-weight: normal; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 5px; vertical-align: top; }
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .content-table th, .content-table td { border: 1px solid #999; padding: 8px; vertical-align: top; }
        .content-table th { background-color: #f2f2f2; font-weight: bold; text-align: left; }
        .score-box { background-color: #f9f9f9; border: 1px solid #ddd; padding: 10px; margin-top: 5px; }
        .qa-note { color: #001f3f; font-style: italic; margin-top: 5px; }
        .footer { margin-top: 30px; text-align: right; font-size: 10px; color: #777; }
        @media print {
            .no-print { display: none; }
            body { -webkit-print-color-adjust: exact; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">Cetak Dokumen</button>
    </div>

    <div class="header">
        <h1>KERTAS KERJA EVALUASI</h1>
        <h2>{{ $kertasKerja->judul_kk }}</h2>
    </div>

    <table class="info-table">
        <tr>
            <td width="150"><strong>Perwakilan</strong></td>
            <td>: {{ $kertasKerja->suratTugas->perwakilan->nama_perwakilan ?? '-' }}</td>
            <td width="150"><strong>Nomor ST</strong></td>
            <td>: {{ $kertasKerja->suratTugas->nomor_st ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Objek Evaluasi</strong></td>
            <td>: {{ $kertasKerja->suratTugas->nama_objek ?? '-' }}</td>
            <td><strong>Tanggal Cetak</strong></td>
            <td>: {{ date('d M Y') }}</td>
        </tr>
    </table>

    <table class="content-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="40%">Uraian / Kriteria</th>
                <th width="35%">Hasil Evaluasi & Catatan (Tim)</th>
                <th width="20%">Validasi QA</th>
            </tr>
        </thead>
        <tbody>
            @foreach($indicators as $indicator)
                <tr style="background-color: #e9ecef;">
                    <td colspan="4"><strong>{{ $indicator->code }} - {{ $indicator->name }}</strong></td>
                </tr>
                
                @foreach($indicator->children as $child)
                    <tr>
                        <td colspan="4" style="background-color: #f8f9fa; padding-left: 20px;">
                            <strong>{{ $child->code }} - {{ $child->name }}</strong>
                        </td>
                    </tr>

                    @foreach($child->children as $subChild)
                        <tr>
                            <td colspan="4" style="padding-left: 40px; font-style: italic; color: #555;">
                                {{ $subChild->code }} - {{ $subChild->name }}
                            </td>
                        </tr>

                        @foreach($subChild->criteria as $criteria)
                            @php
                                $answer = $kertasKerja->answers->where('indikator_id', $criteria->indicator_id)->first();
                                $detail = $answer ? $answer->details->where('criteria_id', $criteria->id)->first() : null;
                                
                                $score = $detail ? $detail->score : 0;
                                // Normalize score for display if it's decimal (0-1) -> 0-100, checking if <=1
                                if ($score <= 1 && $score > 0) $score = $score * 100;
                                
                                $qaScore = $detail ? $detail->score_qa : null;
                                $catatan = $detail ? $detail->catatan : '-';
                                $catatanQa = $detail ? $detail->catatan_qa : null;
                                $tanggapanQa = $detail ? $detail->tanggapan_qa : null;
                            @endphp
                            <tr>
                                <td align="center">{{ $loop->iteration }}</td>
                                <td>
                                    {{ $criteria->name }}
                                    <div style="font-size: 11px; color: #777; margin-top: 5px;">
                                        <em>Bobot: {{ $criteria->weight }}%</em>
                                    </div>
                                </td>
                                <td>
                                    @if($detail)
                                        <div><strong>Nilai Tim:</strong> {{ number_format($score, 2) }}</div>
                                        <div><strong>Catatan:</strong> {{ $catatan }}</div>
                                        @if($detail->evidence_file)
                                            <div style="font-size: 10px; margin-top: 3px; color: green;">[Ada Bukti Dukung]</div>
                                        @endif
                                    @else
                                        <span style="color: red;">Belum Diisi</span>
                                    @endif
                                </td>
                                <td style="background-color: #f0f7ff;">
                                    @if($qaScore !== null)
                                        <!-- Nilai QA assumed 0-100 -->
                                        <div><strong>Nilai QA:</strong> {{ number_format($qaScore, 2) }}</div>
                                        @if($catatanQa)
                                            <div style="font-style: italic; margin-top: 2px;">Note: {{ $catatanQa }}</div>
                                        @endif
                                        @if($tanggapanQa)
                                            <div class="qa-note">Tanggapan: "{{ $tanggapanQa }}"</div>
                                        @endif
                                    @else
                                        <div style="color: #999;">Sama dengan Tim</div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endforeach
                @endforeach
            @endforeach
        </tbody>
    </table>

    <div style="page-break-inside: avoid; margin-top: 30px;">
        <table class="content-table" style="width: 50%; float: right;">
            <tr>
                <th colspan="2" style="text-align: center;">REKAPITULASI NILAI</th>
            </tr>
            <tr>
                <td><strong>Nilai Akhir Tim</strong></td>
                <td align="right">{{ number_format($kertasKerja->nilai_akhir, 2) }}</td>
            </tr>
            <tr>
                <td><strong>Nilai Akhir QA</strong></td>
                <td align="right" style="font-weight: bold; color: #001f3f;">
                    {{ number_format($kertasKerja->nilai_akhir_qa ?? $kertasKerja->nilai_akhir, 2) }}
                </td>
            </tr>
        </table>
        <div style="clear: both;"></div>
    </div>

    <div class="footer">
        Dicetak dari Aplikasi EvalueAction pada {{ date('d M Y H:i') }}
    </div>

</body>
</html>
