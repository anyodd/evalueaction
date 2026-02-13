@php
    // Find existing answer
    $answer = $kertasKerja->answers->where('indikator_id', $question->id)->first();
    $nilai = $answer ? $answer->nilai : '';
    $catatan = $answer ? $answer->catatan : '';
@endphp

<div class="form-group border-bottom pb-4">
    <label class="font-weight-bold text-dark mb-2">{{ $question->uraian }}</label>
    
    @if($question->tipe == 'criteria_tally')
        {{-- Full Width Layout for Matrix/Table --}}
        <div class="row mb-3">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <label class="small text-muted font-weight-bold mb-0">Kriteria Pemenuhan (Checklist)</label>
                    <span class="badge badge-warning">Bobot: {{ $question->bobot }}%</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover w-100">
                        <thead class="bg-light">
                            <tr class="text-center">
                                <th class="align-middle" style="min-width: 300px;">Uraian Kriteria</th>
                                <th class="align-middle" style="min-width: 200px;">Nilai</th>
                                <th class="align-middle" style="min-width: 250px;">Bukti Dukung</th>
                                <th class="align-middle" style="min-width: 200px;">Catatan / Keterangan</th>
                                <th class="align-middle" style="width: 50px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($question->criteria as $criteria)
                                @php
                                    $detail = $answer ? $answer->details->where('criteria_id', $criteria->id)->first() : null;
                                    $val = $detail ? $detail->answer_value : 'none'; 
                                    $scoreQa = $detail ? $detail->score_qa : '';
                                    $catatanQa = $detail ? $detail->catatan_qa : '';
                                @endphp
                                <tr>
                                    <td class="text-sm align-middle">{{ $criteria->uraian }}</td>
                                    <td class="align-middle">
                                        {{-- Original Score Display --}}
                                        <div class="d-flex flex-column flex-xl-row justify-content-center align-items-start align-items-xl-center {{ isset($isQaMode) && $isQaMode ? 'opacity-50' : '' }}">
                                            <div class="icheck-success d-inline mr-xl-2 mb-1 mb-xl-0">
                                                <input type="radio" class="criteria-radio" 
                                                    name="answers[{{ $question->id }}][criteria][{{ $criteria->id }}][value]" 
                                                    id="c-{{ $criteria->id }}-full" 
                                                    value="full" 
                                                    data-target="#file-{{ $criteria->id }}"
                                                    {{ $val == 'full' ? 'checked' : '' }} {{ !$canEdit ? 'disabled' : '' }}>
                                                <label for="c-{{ $criteria->id }}-full">Ya</label>
                                            </div>
                                            <div class="icheck-warning d-inline mx-xl-2 mb-1 mb-xl-0">
                                                <input type="radio" class="criteria-radio" 
                                                    name="answers[{{ $question->id }}][criteria][{{ $criteria->id }}][value]" 
                                                    id="c-{{ $criteria->id }}-part" 
                                                    value="partial" 
                                                    data-target="#file-{{ $criteria->id }}"
                                                    {{ $val == 'partial' ? 'checked' : '' }} {{ !$canEdit ? 'disabled' : '' }}>
                                                <label for="c-{{ $criteria->id }}-part">Sebagian</label>
                                            </div>
                                            <div class="icheck-danger d-inline ml-xl-2">
                                                <input type="radio" class="criteria-radio" 
                                                    name="answers[{{ $question->id }}][criteria][{{ $criteria->id }}][value]" 
                                                    id="c-{{ $criteria->id }}-none" 
                                                    value="none" 
                                                    data-target="#file-{{ $criteria->id }}"
                                                    {{ $val == 'none' || !$detail ? 'checked' : '' }} {{ !$canEdit ? 'disabled' : '' }}>
                                                <label for="c-{{ $criteria->id }}-none">Tidak</label>
                                            </div>
                                        </div>

                                        {{-- QA Score Input (Radio) --}}
                                        @if(isset($isQaMode) && $isQaMode)
                                            <div class="mt-2 border-top pt-2 bg-light p-2 rounded">
                                                <label class="text-xs font-weight-bold text-navy mb-1">Koreksi QA:</label>
                                                <div class="d-flex flex-column flex-xl-row justify-content-start align-items-start align-items-xl-center">
                                                    <div class="icheck-navy d-inline mr-xl-2 mb-1 mb-xl-0">
                                                        <input type="radio" class="qa-radio" 
                                                            name="qa[{{ $criteria->id }}][qa_value]" 
                                                            id="qa-{{ $criteria->id }}-full" 
                                                            value="full" 
                                                            {{ $detail && $detail->qa_value == 'full' ? 'checked' : '' }}>
                                                        <label for="qa-{{ $criteria->id }}-full" class="text-xs">Ya</label>
                                                    </div>
                                                    <div class="icheck-navy d-inline mx-xl-2 mb-1 mb-xl-0">
                                                        <input type="radio" class="qa-radio" 
                                                            name="qa[{{ $criteria->id }}][qa_value]" 
                                                            id="qa-{{ $criteria->id }}-part" 
                                                            value="partial" 
                                                            {{ $detail && $detail->qa_value == 'partial' ? 'checked' : '' }}>
                                                        <label for="qa-{{ $criteria->id }}-part" class="text-xs">Sebagian</label>
                                                    </div>
                                                    <div class="icheck-navy d-inline ml-xl-2">
                                                        <input type="radio" class="qa-radio" 
                                                            name="qa[{{ $criteria->id }}][qa_value]" 
                                                            id="qa-{{ $criteria->id }}-none" 
                                                            value="none" 
                                                            {{ $detail && $detail->qa_value == 'none' ? 'checked' : '' }}>
                                                        <label for="qa-{{ $criteria->id }}-none" class="text-xs">Tidak</label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <div class="form-group mb-0">
                                            @if($detail && $detail->evidence_file)
                                                @php
                                                    $fileUrl = '#';
                                                    $isLocal = Storage::disk('public')->exists($detail->evidence_file);
                                                    if ($isLocal) {
                                                        $fileUrl = asset('storage/' . $detail->evidence_file);
                                                    } else {
                                                        try {
                                                            $fileUrl = Storage::disk('google')->url($detail->evidence_file);
                                                        } catch (\Exception $e) {
                                                            $fileUrl = '#error';
                                                        }
                                                    }
                                                @endphp
                                                <div class="mb-1">
                                                    <a href="{{ $fileUrl }}" target="_blank" class="badge badge-primary text-wrap text-left">
                                                        <i class="fab {{ $isLocal ? 'fa-hdd' : 'fa-google-drive' }}"></i> {{ $isLocal ? 'Lihat File (Lokal)' : 'Lihat File (Drive)' }}
                                                    </a>
                                                </div>
                                            @endif
                                            
                                            <input type="file" 
                                                class="form-control-file text-sm criteria-file" 
                                                id="file-{{ $criteria->id }}"
                                                name="answers[{{ $question->id }}][criteria][{{ $criteria->id }}][evidence]"
                                                {{ ($val == 'none' || !$detail || !$canEdit) ? 'disabled' : '' }}>
                                            
                                            <input type="text" 
                                                    name="answers[{{ $question->id }}][criteria][{{ $criteria->id }}][link]" 
                                                    class="form-control form-control-sm mt-1" 
                                                    value="{{ $detail ? $detail->evidence_link : '' }}" 
                                                    placeholder="Link GDrive..."
                                                    {{ ($val == 'none' || !$detail || !$canEdit) ? 'disabled' : '' }}>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <textarea 
                                            name="answers[{{ $question->id }}][criteria][{{ $criteria->id }}][catatan]" 
                                            class="form-control form-control-sm mb-2" 
                                            rows="3" 
                                            placeholder="Catatan..." {{ !$canEdit ? 'disabled' : '' }}>{{ $detail ? $detail->catatan : '' }}</textarea>
                                        
                                        {{-- QA Note Input --}}
                                        @if(isset($isQaMode) && $isQaMode)
                                            <textarea 
                                                name="qa[{{ $criteria->id }}][catatan_qa]" 
                                                class="form-control form-control-sm qa-note-input border-navy" 
                                                rows="2" 
                                                placeholder="Catatan QA Rendal...">{{ $catatanQa }}</textarea>
                                        @endif
                                    </td>
                                    <td class="align-middle text-center">
                                        @if($canEdit)
                                            <button type="button" class="btn btn-primary btn-sm btn-save-criteria" 
                                                title="Simpan Baris Ini"
                                                data-indicator="{{ $question->id }}"
                                                data-criteria="{{ $criteria->id }}"
                                                data-kk="{{ $kertasKerja->id }}">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        @elseif(isset($isQaMode) && $isQaMode)
                                            <button type="button" class="btn btn-navy btn-sm btn-save-qa" 
                                                title="Simpan QA"
                                                data-indicator="{{ $question->id }}"
                                                data-criteria="{{ $criteria->id }}"
                                                data-kk="{{ $kertasKerja->id }}">
                                                <i class="fas fa-save"></i>
                                            </button>
                                        @else
                                            <span class="text-muted"><i class="fas fa-lock" title="Read Only"></i></span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-2 text-right">
                    <span class="badge badge-info p-2" style="font-size: 0.9em">Skor Parameter: {{ number_format((float)$nilai, 0) }}%</span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
                 <label class="small text-muted">Catatan Umum / Keterangan Parameter</label>
                 <textarea name="answers[{{ $question->id }}][catatan]" class="form-control" rows="2" style="border-radius: 10px;" placeholder="Tambahkan catatan pendukung umum..." {{ !$canEdit ? 'disabled' : '' }}>{{ $catatan }}</textarea>
            </div>
        </div>

    @else
        {{-- Split Layout for Standard Inputs --}}
        <div class="row">
            <div class="col-md-3">
                {{-- Input Logic based on Type --}}
                @if($question->tipe == 'score_manual')
                    <label class="small text-muted">Nilai (0-100)</label>
                    <input type="number" name="answers[{{ $question->id }}][nilai]" class="form-control rounded-pill" min="0" max="100" step="0.01" value="{{ $nilai }}" placeholder="0.00" {{ !$canEdit ? 'disabled' : '' }}>
                
                @elseif($question->tipe == 'input_text')
                    <label class="small text-muted">Jawaban Tekstual</label>
                    <input type="text" name="answers[{{ $question->id }}][nilai]" class="form-control rounded-pill" value="{{ $nilai }}" placeholder="Jawaban singkat..." {{ !$canEdit ? 'disabled' : '' }}>
                
                @elseif($question->tipe == 'score_reference')
                    <label class="small text-muted">Nilai Referensi</label>
                    <div class="input-group">
                        <input type="text" name="answers[{{ $question->id }}][nilai]" id="ref-val-{{ $question->id }}" class="form-control rounded-pill-left" value="{{ $nilai }}" readonly placeholder="Klik Ambil Nilai">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-info rounded-pill-right fetch-ref" 
                                data-id="{{ $question->id }}" 
                                data-ref-jenis="{{ $question->ref_jenis_id }}"
                                data-tahun="{{ $kertasKerja->suratTugas->tahun_evaluasi }}"
                                {{ !$canEdit ? 'disabled' : '' }}>
                                <i class="fas fa-sync-alt mr-1"></i> Ambil
                            </button>
                        </div>
                    </div>
                    <small class="text-info font-italic">*Mengambil nilai akhir dari penugasan terkait.</small>
                @endif
            </div>

            <div class="col-md-9">
                <label class="small text-muted">Catatan / Keterangan</label>
                <textarea name="answers[{{ $question->id }}][catatan]" class="form-control" rows="2" style="border-radius: 10px;" placeholder="Tambahkan catatan pendukung..." {{ !$canEdit ? 'disabled' : '' }}>{{ $catatan }}</textarea>
            </div>
        </div>
    @endif
</div>
