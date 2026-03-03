<table>
    <thead>
        <tr>
            <th style="background-color: #f2f2f2; font-weight: bold;">ID</th>
            <th style="background-color: #f2f2f2; font-weight: bold; width: 500px;">Indikator / Parameter</th>
            <th style="background-color: #f2f2f2; font-weight: bold; width: 100px;">Nilai/Skor</th>
            <th style="background-color: #f2f2f2; font-weight: bold; width: 300px;">Catatan</th>
            <th style="background-color: #f2f2f2; font-weight: bold; width: 300px;">Link Bukti</th>
        </tr>
    </thead>
    <tbody>
        @foreach($indicators as $aspect)
            <tr>
                <td style="background-color: #e3f2fd; font-weight: bold;">{{ $aspect->id }}</td>
                <td style="background-color: #e3f2fd; font-weight: bold;">ASPEK: {{ $aspect->uraian }}</td>
                <td style="background-color: #e3f2fd;"></td>
                <td style="background-color: #e3f2fd;"></td>
                <td style="background-color: #e3f2fd;"></td>
            </tr>
            @foreach($aspect->children as $indicator)
                <tr>
                    <td style="background-color: #f1f8e9; font-weight: bold;">{{ $indicator->id }}</td>
                    <td style="background-color: #f1f8e9; font-weight: bold; padding-left: 20px;">INDIKATOR: {{ $indicator->uraian }}</td>
                    <td style="background-color: #f1f8e9;"></td>
                    <td style="background-color: #f1f8e9;"></td>
                    <td style="background-color: #f1f8e9;"></td>
                </tr>
                @foreach($indicator->children as $parameter)
                    @php
                        $answer = $kertasKerja->answers->where('indikator_id', $parameter->id)->first();
                    @endphp
                    <tr>
                        <td style="font-weight: bold;">{{ $parameter->id }}</td>
                        <td style="padding-left: 40px;">{{ $parameter->uraian }}</td>
                        <td>{{ $answer ? $answer->nilai : 0 }}</td>
                        <td>{{ $answer ? $answer->catatan : '' }}</td>
                        <td>{{ $answer ? $answer->evidence_link : '' }}</td>
                    </tr>
                @endforeach
            @endforeach
        @endforeach
    </tbody>
</table>
