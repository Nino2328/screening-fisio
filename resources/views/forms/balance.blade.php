@extends('layouts.app')
@section('title', 'Y Balance Test') @section('page-title', 'Y Balance Test')

@section('content')
    <a href="{{ route('tests.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 hover:text-white"
        style="border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">← Kembali</a>

    <div class="max-w-4xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <x-form-header icon="⚖️" title="Y BALANCE TEST (YBT)"
            subtitle="Stabilitas dinamis — 3 arah: Anterior (A), Posteromedial (PM), Posterolateral (PL)" tag="BALANCE"
            tagColor="green" />

        <form action="{{ route('forms.balance.store') }}" method="POST" id="ybtForm">@csrf
            <x-athlete-identity :athletes="$athletes ?? []" :withVitals="false" />

            {{-- Leg Length --}}
            <div class="form-group mt-2 max-w-xs">
                <label>Panjang Tungkai (cm) <span class="text-red-400">*</span></label>
                <input type="number" name="leg_length" id="legLength" value="{{ old('leg_length') }}" placeholder="cm"
                    required>
            </div>

            <x-section-title title="Pengukuran YBT" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Kaki Kiri --}}
                <div class="rounded-xl p-4" style="background:#141B22; border:1px solid rgba(0,230,118,0.12)">
                    <h4 class="text-xs font-mono2 uppercase tracking-widest mb-4" style="color:#00E676">🦵 Kaki Kiri</h4>
                    <div class="form-group"><label>Anterior (cm)</label><input type="number" name="left_a" id="left_a"
                            value="{{ old('left_a') }}" step="0.1" placeholder="cm" class="ybt-input"></div>
                    <div class="form-group"><label>Posteromedial (cm)</label><input type="number" name="left_pm"
                            id="left_pm" value="{{ old('left_pm') }}" step="0.1" placeholder="cm" class="ybt-input"></div>
                    <div class="form-group"><label>Posterolateral (cm)</label><input type="number" name="left_pl"
                            id="left_pl" value="{{ old('left_pl') }}" step="0.1" placeholder="cm" class="ybt-input"></div>
                </div>
                {{-- Kaki Kanan --}}
                <div class="rounded-xl p-4" style="background:#141B22; border:1px solid rgba(0,230,118,0.12)">
                    <h4 class="text-xs font-mono2 uppercase tracking-widest mb-4" style="color:#00E676">🦵 Kaki Kanan</h4>
                    <div class="form-group"><label>Anterior (cm)</label><input type="number" name="right_a" id="right_a"
                            value="{{ old('right_a') }}" step="0.1" placeholder="cm" class="ybt-input"></div>
                    <div class="form-group"><label>Posteromedial (cm)</label><input type="number" name="right_pm"
                            id="right_pm" value="{{ old('right_pm') }}" step="0.1" placeholder="cm" class="ybt-input"></div>
                    <div class="form-group"><label>Posterolateral (cm)</label><input type="number" name="right_pl"
                            id="right_pl" value="{{ old('right_pl') }}" step="0.1" placeholder="cm" class="ybt-input"></div>
                </div>
                {{-- Formula --}}
                <div class="rounded-xl p-4" style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.2)">
                    <h4 class="text-xs font-mono2 uppercase tracking-widest mb-4" style="color:#00E676">📊 Perhitungan</h4>
                    <div class="text-xs leading-relaxed mb-4 p-3 rounded-lg" style="background:#1C252F; color:#7A8D9E">
                        Composite (%) =<br>
                        (A + PM + PL) ÷<br>
                        (3 × Panjang Tungkai) × 100
                    </div>
                    <button type="button" onclick="calculateYBT()"
                        class="w-full py-2.5 rounded-lg text-sm font-semibold transition hover:-translate-y-px"
                        style="background:#00E676; color:#080C10">
                        🔢 Hitung Otomatis
                    </button>
                </div>
            </div>

            <x-section-title title="Hasil Perhitungan" />
            <div class="overflow-x-auto mb-4">
                <table class="w-full text-sm norma-table">
                    <thead>
                        <tr>
                            <th>Arah</th>
                            <th>Kiri</th>
                            <th>Kanan</th>
                            <th>Difference</th>
                            <th>Standard</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach([['anterior', 'Anterior'], ['pm', 'Posteromedial'], ['pl', 'Posterolateral']] as [$k, $label])
                            <tr>
                                <td><strong>{{ $label }}</strong></td>
                                <td><input type="text" name="res_left_{{ $k }}" id="res_left_{{ $k }}"
                                        value="{{ old('res_left_' . $k) }}" placeholder="—" class="table-input" readonly></td>
                                <td><input type="text" name="res_right_{{ $k }}" id="res_right_{{ $k }}"
                                        value="{{ old('res_right_' . $k) }}" placeholder="—" class="table-input" readonly></td>
                                <td><input type="text" name="res_diff_{{ $k }}" id="res_diff_{{ $k }}"
                                        value="{{ old('res_diff_' . $k) }}" placeholder="—" class="table-input" readonly></td>
                                <td>
                                    <select name="std_{{ $k }}" class="table-select">
                                        <option value="">Pilih...</option>
                                        <option value="optimal" {{ old('std_' . $k) == 'optimal' ? 'selected' : '' }}>Optimal (&lt;4cm)
                                        </option>
                                        <option value="below" {{ old('std_' . $k) == 'below' ? 'selected' : '' }}>Below (&gt;4cm)
                                        </option>
                                    </select>
                                </td>
                            </tr>
                        @endforeach
                        <tr style="background:rgba(0,230,118,0.05)">
                            <td><strong>Composite (%)</strong></td>
                            <td><input type="text" name="composite_left" id="composite_left"
                                    value="{{ old('composite_left') }}" placeholder="%" class="table-input" readonly></td>
                            <td><input type="text" name="composite_right" id="composite_right"
                                    value="{{ old('composite_right') }}" placeholder="%" class="table-input" readonly></td>
                            <td>—</td>
                            <td>
                                <select name="std_composite" class="table-select">
                                    <option value="">Pilih...</option>
                                    <option value="optimal" {{ old('std_composite') == 'optimal' ? 'selected' : '' }}>Optimal
                                        (&gt;89%)</option>
                                    <option value="below" {{ old('std_composite') == 'below' ? 'selected' : '' }}>Below (&lt;89%)
                                    </option>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <x-section-title title="Kesimpulan" />
            <div class="rounded-xl p-4" style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.15)">
                <label class="block text-xs uppercase tracking-widest font-mono2 mb-2" style="color:#00E676">💬
                    Kesimpulan</label>
                <textarea name="conclusion" rows="3"
                    class="w-full bg-transparent border-none outline-none text-sm resize-none"
                    placeholder="Tuliskan kesimpulan hasil Y Balance Test...">{{ old('conclusion') }}</textarea>
            </div>
            <div class="flex justify-end gap-3 mt-6">
                <a href="{{ route('tests.index') }}" class="btn-ghost">Batal</a>
                <button type="submit" class="btn-primary">💾 Simpan Data</button>
            </div>
        </form>
    </div>
@endsection

@push('scripts')
    <script>
        function calculateYBT() {
            const leg = parseFloat(document.getElementById('legLength').value) || 0;
            const lA = parseFloat(document.getElementById('left_a').value) || 0;
            const lPM = parseFloat(document.getElementById('left_pm').value) || 0;
            const lPL = parseFloat(document.getElementById('left_pl').value) || 0;
            const rA = parseFloat(document.getElementById('right_a').value) || 0;
            const rPM = parseFloat(document.getElementById('right_pm').value) || 0;
            const rPL = parseFloat(document.getElementById('right_pl').value) || 0;

            const set = (id, val) => { const el = document.getElementById(id); if (el) el.value = val; };

            set('res_left_anterior', lA || '—');
            set('res_left_pm', lPM || '—');
            set('res_left_pl', lPL || '—');
            set('res_right_anterior', rA || '—');
            set('res_right_pm', rPM || '—');
            set('res_right_pl', rPL || '—');
            set('res_diff_anterior', Math.abs(lA - rA).toFixed(1));
            set('res_diff_pm', Math.abs(lPM - rPM).toFixed(1));
            set('res_diff_pl', Math.abs(lPL - rPL).toFixed(1));

            if (leg > 0) {
                const cL = ((lA + lPM + lPL) / (3 * leg) * 100).toFixed(1);
                const cR = ((rA + rPM + rPL) / (3 * leg) * 100).toFixed(1);
                set('composite_left', cL + '%');
                set('composite_right', cR + '%');
            }
            showToast('Perhitungan YBT berhasil!', '📊');
        }
    </script>
@endpush