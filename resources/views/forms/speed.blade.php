@extends('layouts.app')
@section('title', 'Speed 50M')
@section('page-title', 'Speed 50M')

@section('content')
<a href="{{ route('tests.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 transition-all hover:text-white"
   style="border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">← Kembali ke Semua Tes</a>

<div class="max-w-3xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
    <x-form-header icon="⚡" title="SPEED 50 METER"
        subtitle="Mengukur kecepatan sprint, akselerasi, dan kecepatan lari maksimum"
        tag="SPEED" tagColor="yellow" />

    <form action="{{ route('forms.speed.store') }}" method="POST">
        @csrf
        <x-athlete-identity :athletes="$athletes ?? []" />

        <x-section-title title="Hasil Tes Speed 50M" />
        <div class="overflow-x-auto mb-5">
            <table class="w-full text-sm norma-table">
                <thead><tr><th>Skor</th><th>Putra (detik)</th><th>Kriteria</th><th>Putri (detik)</th></tr></thead>
                <tbody>
                    @foreach([
                        ['5','s.d – 6.7','Baik Sekali','s.d – 7.7','excellent'],
                        ['4','6.8 – 7.6','Baik','7.8 – 8.7','good'],
                        ['3','7.7 – 8.7','Cukup','8.8 – 9.9','average'],
                        ['2','8.8 – 10.3','Kurang','10.0 – 11.9','below'],
                        ['1','10.4 – dst','Kurang Sekali','12.0 – dst','poor'],
                    ] as [$s,$p,$k,$w,$b])
                    <tr>
                        <td class="font-bold font-mono2" style="color:#00E676">{{ $s }}</td>
                        <td>{{ $p }}</td>
                        <td><x-rating-badge :rating="$b" /></td>
                        <td>{{ $w }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="form-group">
                <label>Waktu Tempuh (detik) <span class="text-red-400">*</span></label>
                <input type="number" name="time_seconds" value="{{ old('time_seconds') }}"
                       step="0.01" placeholder="detik" required>
            </div>
            <div class="form-group">
                <label>Skor (1–5)</label>
                <input type="number" name="score" value="{{ old('score') }}" min="1" max="5" placeholder="1 - 5">
            </div>
            <div class="form-group">
                <label>Kriteria</label>
                <select name="rating">
                    <option value="">Pilih...</option>
                    @foreach(['Baik Sekali','Baik','Cukup','Kurang','Kurang Sekali'] as $r)
                        <option value="{{ $r }}" {{ old('rating') == $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <x-section-title title="Kesimpulan" />
        <div class="rounded-xl p-4" style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.15)">
            <label class="block text-xs uppercase tracking-widest font-mono2 mb-2" style="color:#00E676">💬 Kesimpulan</label>
            <textarea name="conclusion" rows="3" class="w-full bg-transparent border-none outline-none text-sm resize-none"
                      placeholder="Tuliskan kesimpulan hasil tes kecepatan atlet...">{{ old('conclusion') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('tests.index') }}" class="btn-ghost">Batal</a>
            <button type="submit" class="btn-primary">💾 Simpan Data</button>
        </div>
    </form>
</div>
@endsection
