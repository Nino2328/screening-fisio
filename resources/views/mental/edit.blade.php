@extends('layouts.app')
@section('title', 'Edit Mental Assessment')
@section('page-title', 'Edit Mental Assessment')

@section('content')

    <a href="{{ route('mental.show', $assessment->id) }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 transition-all hover:text-white"
        style="background:transparent; border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">
        ← Kembali ke Detail
    </a>

    <div class="max-w-4xl mx-auto rounded-xl p-7" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">

        <h2 class="text-2xl font-bold mb-2" style="color:#00E676">EDIT MENTAL ASSESSMENT</h2>
        <p class="text-sm mb-6" style="color:#7A8D9E">Update penilaian mental atlet</p>

        @if ($errors->any())
            <div class="p-4 mb-6 rounded-lg" style="background:#FF6B6B20; border:1px solid #FF6B6B">
                <ul class="text-sm" style="color:#FF6B6B">
                    @foreach ($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('mental.update', $assessment->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- Identitas Atlet --}}
            <div style="background:rgba(0,230,118,0.05); border:1px solid rgba(0,230,118,0.15)" class="rounded-lg p-4 mb-6">
                <label class="block text-xs uppercase tracking-widest font-mono2 mb-3" style="color:#00E676">📋 Identitas
                    Atlet</label>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="form-group">
                        <label>Pilih Atlet <span class="text-red-400">*</span></label>
                        <select name="athlete_id" required>
                            <option value="">-- Pilih Atlet --</option>
                            @foreach ($athletes as $athlete)
                                <option value="{{ $athlete->id }}" {{ $assessment->athlete_id == $athlete->id ? 'selected' : '' }}>
                                    {{ $athlete->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Tanggal Assessment <span class="text-red-400">*</span></label>
                        <input type="datetime-local" name="assessment_date"
                            value="{{ $assessment->assessment_date->format('Y-m-d\TH:i') }}" required>
                    </div>
                </div>
            </div>

            {{-- Mental Assessments --}}
            <div style="background:rgba(0,230,118,0.05); border:1px solid rgba(0,230,118,0.15)" class="rounded-lg p-4 mb-6">
                <label class="block text-xs uppercase tracking-widest font-mono2 mb-4" style="color:#00E676">🧠 Penilaian
                    Mental (Skala 1-5)</label>

                @foreach ($categories as $key => $label)
                    <div class="mb-5">
                        <div class="flex justify-between items-center mb-2">
                            <label class="text-sm font-semibold">{{ $label }}</label>
                            <span id="{{ $key }}_score" class="text-xs px-2 py-1 rounded"
                                style="background:#00E67630; color:#00E676">
                                {{ $assessment->$key ? $assessment->$key . '/5 · ' . ($scoreLabels[$key][$assessment->$key] ?? '') : '-' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-5 gap-2 mb-2">
                            @for ($i = 1; $i <= 5; $i++)
                                <label class="flex flex-col items-center gap-1 cursor-pointer">
                                    <input type="radio" name="{{ $key }}" value="{{ $i }}" class="scale-rating"
                                        onchange="updateScore('{{ $key }}', {{ $i }})" {{ $assessment->$key == $i ? 'checked' : '' }}>
                                    <span class="text-xs">{{ $i }}</span>
                                    <span class="text-[10px] leading-tight text-center">{{ $scoreLabels[$key][$i] }}</span>
                                </label>
                            @endfor
                        </div>

                        <textarea name="{{ $key }}_note" rows="2" class="form-control"
                            placeholder="Catatan untuk {{ strtolower($label) }}..."
                            style="background:#0E1419; border:1px solid rgba(0,230,118,0.2); color:#fff">{{ $assessment->{$key . '_note'} ?? '' }}</textarea>
                    </div>
                @endforeach
            </div>

            {{-- Overall Assessment & Recommendations --}}
            <div style="background:rgba(0,230,118,0.05); border:1px solid rgba(0,230,118,0.15)" class="rounded-lg p-4 mb-6">
                <label class="block text-xs uppercase tracking-widest font-mono2 mb-3" style="color:#00E676">📝 Kesimpulan &
                    Rekomendasi</label>

                <div class="form-group mb-4">
                    <label>Penilaian Keseluruhan</label>
                    <textarea name="overall_assessment" rows="4" class="form-control"
                        placeholder="Tuliskan penilaian mental keseluruhan atlet...">{{ $assessment->overall_assessment }}</textarea>
                </div>

                <div class="form-group">
                    <label>Rekomendasi Peningkatan</label>
                    <textarea name="recommendations" rows="4" class="form-control"
                        placeholder="Tuliskan rekomendasi untuk meningkatkan mental atlet...">{{ $assessment->recommendations }}</textarea>
                </div>
            </div>

            {{-- Buttons --}}
            <div class="flex gap-3">
                <button type="submit" class="px-6 py-2 rounded-lg font-semibold transition-all"
                    style="background:#00E676; color:#0E1419">
                    💾 Perbarui Assessment
                </button>
                <a href="{{ route('mental.show', $assessment->id) }}"
                    class="px-6 py-2 rounded-lg font-semibold transition-all"
                    style="background:transparent; border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">
                    ✕ Batal
                </a>
            </div>
        </form>

    </div>

    <style>
        .scale-rating {
            appearance: none;
            -webkit-appearance: none;
            width: 28px;
            height: 28px;
            border: 2px solid rgba(0, 230, 118, 0.3);
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .scale-rating:hover {
            border-color: #00E676;
            background-color: rgba(0, 230, 118, 0.1);
        }

        .scale-rating:checked {
            background-color: #00E676;
            border-color: #00E676;
        }
    </style>

    <script>
        const scoreLabels = @json($scoreLabels);

        function updateScore(key, value) {
            document.getElementById(key + '_score').textContent = `${value}/5 · ${scoreLabels[key][value]}`;
        }
    </script>

@endsection