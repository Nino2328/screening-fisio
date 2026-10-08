{{-- ============================================================
     resources/views/forms/strength.blade.php
     ============================================================ --}}
@extends('layouts.app')
@section('title', 'Strength Test')
@section('page-title', 'Strength Test')

@section('content')
<a href="{{ route('tests.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 transition-all hover:text-white"
   style="border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">← Kembali ke Semua Tes</a>

<div class="max-w-3xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
    <x-form-header icon="💪" title="STRENGTH — PUSH UP & SIT UP"
        subtitle="Mengukur kekuatan otot dada, bahu, lengan, dan otot inti (core)"
        tag="STRENGTH" tagColor="red" />

    <form action="{{ route('forms.strength.store') }}" method="POST">
        @csrf
        <x-athlete-identity :athletes="$athletes ?? []" :withVitals="false" />

        {{-- PUSH UP --}}
        <x-section-title title="Push Up" />
        <div class="overflow-x-auto mb-4">
            <table class="w-full text-sm norma-table">
                <thead><tr><th>Skor</th><th>Putra (rep)</th><th>Kriteria</th><th>Putri (rep)</th></tr></thead>
                <tbody>
                    @foreach([
                        ['5','≥ 38 kali','Sempurna','≥ 21 kali','excellent'],
                        ['4','29 – 37 kali','Baik Sekali','16 – 20 kali','good'],
                        ['3','20 – 28 kali','Baik','10 – 15 kali','average'],
                        ['2','12 – 19 kali','Cukup','5 – 9 kali','below'],
                        ['1','4 – 11 kali','Kurang','1 – 4 kali','poor'],
                    ] as [$s,$p,$k,$w,$b])
                    <tr>
                        <td class="font-bold font-mono2" style="color:#00E676">{{ $s }}</td>
                        <td>{{ $p }}</td><td><x-rating-badge :rating="$b" /></td><td>{{ $w }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
            <div class="form-group"><label>Jumlah Push Up (rep)</label><input type="number" name="pushup_count" value="{{ old('pushup_count') }}" placeholder="repetisi"></div>
            <div class="form-group"><label>Skor (1–5)</label><input type="number" name="pushup_score" value="{{ old('pushup_score') }}" min="1" max="5" placeholder="1-5"></div>
            <div class="form-group"><label>Kriteria</label>
                <select name="pushup_rating">
                    <option value="">Pilih...</option>
                    @foreach(['Sempurna','Baik Sekali','Baik','Cukup','Kurang'] as $r)
                        <option value="{{ $r }}" {{ old('pushup_rating') == $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group mb-2"><label>Keterangan Push Up</label><textarea name="pushup_note" rows="2" placeholder="Catatan teknis...">{{ old('pushup_note') }}</textarea></div>

        {{-- SIT UP --}}
        <x-section-title title="Sit Up" />
        <div class="overflow-x-auto mb-4">
            <table class="w-full text-sm norma-table">
                <thead><tr><th>Skor</th><th>Putra (rep)</th><th>Kriteria</th><th>Putri (rep)</th></tr></thead>
                <tbody>
                    @foreach([
                        ['5','> 90 kali','Sempurna','> 88 kali','excellent'],
                        ['4','70 – 89 kali','Baik Sekali','69 – 87 kali','good'],
                        ['3','50 – 69 kali','Baik','48 – 68 kali','average'],
                        ['2','30 – 49 kali','Cukup','29 – 47 kali','below'],
                        ['1','10 – 29 kali','Kurang','10 – 28 kali','poor'],
                    ] as [$s,$p,$k,$w,$b])
                    <tr>
                        <td class="font-bold font-mono2" style="color:#00E676">{{ $s }}</td>
                        <td>{{ $p }}</td><td><x-rating-badge :rating="$b" /></td><td>{{ $w }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-2">
            <div class="form-group"><label>Jumlah Sit Up (rep)</label><input type="number" name="situp_count" value="{{ old('situp_count') }}" placeholder="repetisi"></div>
            <div class="form-group"><label>Skor (1–5)</label><input type="number" name="situp_score" value="{{ old('situp_score') }}" min="1" max="5" placeholder="1-5"></div>
            <div class="form-group"><label>Kriteria</label>
                <select name="situp_rating">
                    <option value="">Pilih...</option>
                    @foreach(['Sempurna','Baik Sekali','Baik','Cukup','Kurang'] as $r)
                        <option value="{{ $r }}" {{ old('situp_rating') == $r ? 'selected' : '' }}>{{ $r }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="form-group mb-2"><label>Keterangan Sit Up</label><textarea name="situp_note" rows="2" placeholder="Catatan teknis...">{{ old('situp_note') }}</textarea></div>

        <x-section-title title="Kesimpulan" />
        <div class="rounded-xl p-4" style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.15)">
            <label class="block text-xs uppercase tracking-widest font-mono2 mb-2" style="color:#00E676">💬 Kesimpulan</label>
            <textarea name="conclusion" rows="3" class="w-full bg-transparent border-none outline-none text-sm resize-none" placeholder="Kesimpulan...">{{ old('conclusion') }}</textarea>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <a href="{{ route('tests.index') }}" class="btn-ghost">Batal</a>
            <button type="submit" class="btn-primary">💾 Simpan Data</button>
        </div>
    </form>
</div>
@endsection
