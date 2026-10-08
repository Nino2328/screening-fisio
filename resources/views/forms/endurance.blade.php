@extends('layouts.app')
@section('title', 'YoYo Test')
@section('page-title', 'YoYo Test')

@section('content')

<a href="{{ route('tests.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 transition-all hover:text-white"
   style="background:transparent; border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">
    ← Kembali ke Semua Tes
</a>

<div class="max-w-3xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">

    <x-form-header icon="🏃" title="YOYO INTERMITTENT RECOVERY TEST"
        subtitle="Endurance Test — Yo-Yo Intermittent Recovery Test Level 1"
        tag="ENDURANCE" tagColor="green" />

    <form action="{{ route('forms.endurance.store') }}" method="POST">
        @csrf

        <x-athlete-identity :athletes="$athletes ?? []" />

        <x-section-title title="Hasil Tes YoYo" />

        {{-- Norma Table --}}
        <div class="overflow-x-auto mb-5">
            <table class="w-full text-sm norma-table">
                <thead>
                    <tr>
                        <th>Rating</th><th>Meters</th><th>Level</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['Elite',    '> 2400 m',      '> 20.1',       'excellent'],
                        ['Excellent','2000 – 2400 m',  '18.7 – 20.1', 'good'],
                        ['Good',     '1520 – 1960 m',  '17.3 – 18.6', 'average'],
                        ['Average',  '1040 – 1480 m',  '15.7 – 17.2', 'below'],
                        ['Below Avg','520 – 1000 m',   '14.2 – 15.6', 'below'],
                        ['Very Poor','< 520 m',        '< 14.2',       'poor'],
                    ] as [$r,$m,$l,$badge])
                    <tr>
                        <td><x-rating-badge :rating="$badge" /></td>
                        <td>{{ $m }}</td>
                        <td>{{ $l }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="form-group">
                <label>Jarak Tempuh (m) <span class="text-red-400">*</span></label>
                <input type="number" name="distance" value="{{ old('distance') }}"
                       placeholder="meter" required min="0">
            </div>
            <div class="form-group">
                <label>Level Tercapai</label>
                <input type="text" name="level" value="{{ old('level') }}" placeholder="cth: 17.3">
            </div>
            <div class="form-group">
                <label>Rating</label>
                <select name="rating">
                    <option value="">Pilih...</option>
                    @foreach(['Elite','Excellent','Good','Average','Below Average','Very Poor'] as $r)
                        <option value="{{ $r }}" {{ old('rating') == $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <x-section-title title="Kesimpulan" />
        <div class="rounded-xl p-4" style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.15)">
            <label class="block text-xs uppercase tracking-widest font-mono2 mb-2" style="color:#00E676">💬 Kesimpulan</label>
            <textarea name="conclusion" rows="3"
                      class="w-full bg-transparent border-none outline-none text-sm resize-none"
                      placeholder="Tuliskan kesimpulan hasil tes endurance atlet...">{{ old('conclusion') }}</textarea>
        </div>

        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('tests.index') }}" class="btn-ghost">Batal</a>
            <button type="submit" class="btn-primary">💾 Simpan Data</button>
        </div>
    </form>
</div>
@endsection
