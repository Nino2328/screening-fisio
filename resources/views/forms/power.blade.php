@extends('layouts.app')
@section('title', 'Power Jump') @section('page-title', 'Power Jump')
@section('content')
    <a href="{{ route('tests.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 hover:text-white"
        style="border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">← Kembali</a>
    <div class="max-w-3xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <x-form-header icon="🦵" title="POWER — STANDING LONG JUMP"
            subtitle="Broad Jump Test — Mengukur power eksplosif otot tungkai" tag="POWER" tagColor="orange" />
        <form action="{{ route('forms.power.store') }}" method="POST">@csrf
            <x-athlete-identity :athletes="$athletes ?? []" :withVitals="false" />
            <x-section-title title="Hasil Standing Long Jump (3 Percobaan)" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div class="form-group"><label>Percobaan 1 (cm)</label><input type="number" name="try1"
                        value="{{ old('try1') }}" placeholder="cm"></div>
                <div class="form-group"><label>Percobaan 2 (cm)</label><input type="number" name="try2"
                        value="{{ old('try2') }}" placeholder="cm"></div>
                <div class="form-group"><label>Percobaan 3 (cm)</label><input type="number" name="try3"
                        value="{{ old('try3') }}" placeholder="cm"></div>
                <div class="form-group"><label>Hasil Terbaik (cm) <span class="text-red-400">*</span></label><input
                        type="number" name="best" value="{{ old('best') }}" placeholder="cm" style="border-color:#00E676"
                        required></div>
                <div class="form-group"><label>Kategori</label>
                    <select name="rating">
                        <option value="">Pilih...</option>
                        @foreach(['Luar Biasa', 'Baik Sekali', 'Diatas Rata-Rata', 'Rata-Rata', 'Dibawah Rata-Rata', 'Miskin', 'Sangat Miskin'] as $r)
                            <option value="{{ $r }}" {{ old('rating') == $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm norma-table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Pria (cm)</th>
                            <th>Wanita (cm)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([['Luar Biasa', '> 250', '> 200', 'excellent'], ['Baik Sekali', '241–250', '191–200', 'good'], ['Diatas Rata', '231–240', '181–190', 'good'], ['Rata-Rata', '221–230', '171–180', 'average'], ['Dibawah Rata', '211–220', '161–170', 'below'], ['Miskin', '191–210', '141–160', 'poor'], ['Sangat Miskin', '< 191', '< 141', 'poor']] as [$r, $p, $w, $b])
                            <tr>
                                <td><x-rating-badge :rating="$b" /></td>
                                <td>{{ $p }}</td>
                                <td>{{ $w }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <x-section-title title="Kesimpulan" />
            <div class="rounded-xl p-4" style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.15)">
                <label class="block text-xs uppercase tracking-widest font-mono2 mb-2" style="color:#00E676">💬
                    Kesimpulan</label>
                <textarea name="conclusion" rows="3"
                    class="w-full bg-transparent border-none outline-none text-sm resize-none"
                    placeholder="Kesimpulan...">{{ old('conclusion') }}</textarea>
            </div>
            <div class="flex justify-end gap-3 mt-6"><a href="{{ route('tests.index') }}" class="btn-ghost">Batal</a><button
                    type="submit" class="btn-primary">💾 Simpan Data</button></div>
        </form>
    </div>
@endsection