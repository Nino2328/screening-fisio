{{-- resources/views/components/athlete-identity.blade.php --}}
{{-- Usage: <x-athlete-identity :athletes="$athletes" :selected="old('athlete_id')" /> --}}

@props(['athletes' => [], 'selected' => null, 'withVitals' => true])

@php($selected = $selected ?? old('athlete_id', request()->query('athlete_id')))

<x-section-title title="Identitas Atlet" />

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Pilih atau isi nama atlet --}}
    @if(count($athletes) > 0)
        <div class="form-group">
            <label>Pilih Atlet</label>
            <select name="athlete_id" onchange="fillAthleteData(this)">
                <option value="">-- Pilih Atlet --</option>
                @foreach($athletes as $athlete)
                    <option value="{{ $athlete->id }}" data-name="{{ $athlete->name }}" data-age="{{ $athlete->age }}"
                        data-gender="{{ $athlete->gender }}" data-position="{{ $athlete->position }}"
                        data-height="{{ $athlete->height }}" data-weight="{{ $athlete->weight }}"
                        data-phone="{{ $athlete->phone }}" {{ $selected == $athlete->id ? 'selected' : '' }}>
                        {{ $athlete->name }} — {{ $athlete->position }}
                    </option>
                @endforeach
            </select>
        </div>
        <div></div>
    @endif

    <div class="form-group">
        <label>Nama Lengkap <span class="text-red-400">*</span></label>
        <input type="text" name="name" value="{{ old('name') }}" placeholder="Nama atlet..." required>
    </div>
    <div class="form-group">
        <label>Usia</label>
        <input type="number" name="age" value="{{ old('age') }}" placeholder="Tahun" min="10" max="50">
    </div>
    <div class="form-group">
        <label>Tinggi Badan (cm)</label>
        <input type="number" name="height" value="{{ old('height') }}" placeholder="cm">
    </div>
    <div class="form-group">
        <label>Berat Badan (kg)</label>
        <input type="number" name="weight" value="{{ old('weight') }}" placeholder="kg">
    </div>
    <div class="form-group">
        <label>Jenis Kelamin <span class="text-red-400">*</span></label>
        <select name="gender" required>
            <option value="">Pilih...</option>
            <option value="putra" {{ old('gender') == 'putra' ? 'selected' : '' }}>Putra</option>
            <option value="putri" {{ old('gender') == 'putri' ? 'selected' : '' }}>Putri</option>
        </select>
    </div>
    <div class="form-group">
        <label>Posisi Pemain</label>
        <select name="position">
            <option value="">Pilih...</option>
            @foreach(['Striker', 'Gelandang', 'Defender', 'Kiper', 'Winger', 'Playmaker'] as $pos)
                <option value="{{ $pos }}" {{ old('position') == $pos ? 'selected' : '' }}>{{ $pos }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Hari, Tanggal & Jam <span class="text-red-400">*</span></label>
        <input type="datetime-local" name="test_date" value="{{ old('test_date', now()->format('Y-m-d\TH:i')) }}"
            required>
    </div>
    <div class="form-group">
        <label>Nomor HP</label>
        <input type="tel" name="phone" value="{{ old('phone') }}" placeholder="08xx...">
    </div>
</div>

@if($withVitals)
    <x-section-title title="Vital Sign & Riwayat Keluhan" />
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <div class="form-group">
            <label>TD / Tekanan Darah</label>
            <input type="text" name="vital_td" value="{{ old('vital_td') }}" placeholder="mmHg">
        </div>
        <div class="form-group">
            <label>Suhu</label>
            <input type="text" name="vital_suhu" value="{{ old('vital_suhu') }}" placeholder="°C">
        </div>
        <div class="form-group">
            <label>Nadi</label>
            <input type="text" name="vital_nadi" value="{{ old('vital_nadi') }}" placeholder="bpm">
        </div>
        <div class="form-group">
            <label>Respirasi</label>
            <input type="text" name="vital_respirasi" value="{{ old('vital_respirasi') }}" placeholder="/mnt">
        </div>
    </div>
    <div class="form-group">
        <label>Riwayat Keluhan</label>
        <textarea name="complaint" rows="2"
            placeholder="Tuliskan riwayat keluhan atau cedera sebelumnya...">{{ old('complaint') }}</textarea>
    </div>
@endif

@if(count($athletes) > 0)
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const athleteSelect = document.querySelector('select[name="athlete_id"]');
                if (athleteSelect?.value) fillAthleteData(athleteSelect);
            });
        </script>
    @endpush
@endif