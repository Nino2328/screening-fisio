@extends('layouts.app')
@section('title', 'Kecepatan Reaksi') @section('page-title', 'Kecepatan Reaksi')
@section('content')
    <a href="{{ route('tests.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 hover:text-white"
        style="border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">← Kembali</a>
    <div class="max-w-3xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <x-form-header icon="👁️" title="TEST KECEPATAN REAKSI"
            subtitle="Ruler Drop Test — Mengukur waktu reaksi terhadap stimulus visual" tag="REACTION" tagColor="red" />
        <form action="{{ route('forms.reaction.store') }}" method="POST">@csrf
            <x-athlete-identity :athletes="$athletes ?? []" :withVitals="false" />
            <x-section-title title="Hasil Tes Kecepatan Reaksi" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div class="form-group"><label>Nilai Murni (cm) <span class="text-red-400">*</span></label><input
                        type="number" name="value_cm" value="{{ old('value_cm') }}" step="0.1" placeholder="cm" required>
                </div>
                <div class="form-group"><label>Rating</label>
                    <select name="rating">
                        <option value="">Pilih...</option>
                        @foreach(['Excellent', 'Above Average', 'Average', 'Below Average', 'Poor'] as $r)
                            <option value="{{ $r }}" {{ old('rating') == $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm norma-table">
                    <thead>
                        <tr>
                            <th>Keterangan</th>
                            <th>Nilai Murni</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([['Excellent', '< 7.5 cm', 'excellent'], ['Above Average', '7.5 – 15.9 cm', 'good'], ['Average', '15.9 – 20.4 cm', 'average'], ['Below Average', '20.4 – 28 cm', 'below'], ['Poor', '> 28 cm', 'poor']] as [$r, $v, $b])
                            <tr>
                                <td><x-rating-badge :rating="$b" /></td>
                                <td>{{ $v }}</td>
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