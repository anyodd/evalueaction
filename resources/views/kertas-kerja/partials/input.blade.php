@php
    // Find existing answer
    $answer = $kertasKerja->answers->where('indikator_id', $question->id)->first();
    $nilai = $answer ? $answer->nilai : '';
    $catatan = $answer ? $answer->catatan : '';
@endphp

<div class="form-group border-bottom pb-4">
    <label class="font-weight-bold text-dark mb-2">{{ $question->uraian }}</label>
    
    <div class="row">
        <div class="col-md-3">
            {{-- Input Logic based on Type --}}
            @if($question->tipe == 'score_manual')
                <label class="small text-muted">Nilai (0-100)</label>
                <input type="number" name="answers[{{ $question->id }}][nilai]" class="form-control rounded-pill" min="0" max="100" step="0.01" value="{{ $nilai }}" placeholder="0.00">
            
            @elseif($question->tipe == 'input_text')
                <label class="small text-muted">Jawaban Tekstual</label>
                <input type="text" name="answers[{{ $question->id }}][nilai]" class="form-control rounded-pill" value="{{ $nilai }}" placeholder="Jawaban singkat...">
            
            @elseif($question->tipe == 'score_reference')
                <label class="small text-muted">Nilai Referensi</label>
                <div class="input-group">
                    <input type="text" name="answers[{{ $question->id }}][nilai]" id="ref-val-{{ $question->id }}" class="form-control rounded-pill-left" value="{{ $nilai }}" readonly placeholder="Klik Ambil Nilai">
                    <div class="input-group-append">
                        <button type="button" class="btn btn-info rounded-pill-right fetch-ref" 
                            data-id="{{ $question->id }}" 
                            data-ref-jenis="{{ $question->ref_jenis_id }}"
                            data-tahun="{{ $kertasKerja->suratTugas->tahun_evaluasi }}">
                            <i class="fas fa-sync-alt mr-1"></i> Ambil
                        </button>
                    </div>
                </div>
                <small class="text-info font-italic">*Mengambil nilai akhir dari penugasan terkait.</small>

            @elseif($question->tipe == 'criteria_tally')
                <label class="small text-muted font-weight-bold">Checklist Kriteria</label>
                <div class="criteria-list ml-2" style="max-height: 200px; overflow-y: auto;">
                    @foreach($question->criteria as $criteria)
                        @php
                            $isChecked = $answer && $answer->details->where('criteria_id', $criteria->id)->where('is_checked', true)->isNotEmpty();
                        @endphp
                        <div class="custom-control custom-checkbox mb-2">
                            <input type="checkbox" class="custom-control-input criteria-check" 
                                id="criteria-{{ $criteria->id }}" 
                                name="answers[{{ $question->id }}][criteria][{{ $criteria->id }}]"
                                {{ $isChecked ? 'checked' : '' }}>
                            <label class="custom-control-label text-sm" for="criteria-{{ $criteria->id }}" style="font-weight: normal;">
                                {{ $criteria->uraian }}
                            </label>
                        </div>
                    @endforeach
                </div>
                <div class="mt-2 text-right">
                    <span class="badge badge-info">Nilai: {{ number_format($nilai, 0) }}%</span>
                </div>

            @endif
        </div>

        <div class="col-md-9">
            <label class="small text-muted">Catatan / Keterangan</label>
            <textarea name="answers[{{ $question->id }}][catatan]" class="form-control" rows="2" style="border-radius: 10px;" placeholder="Tambahkan catatan pendukung...">{{ $catatan }}</textarea>
        </div>
    </div>
</div>
