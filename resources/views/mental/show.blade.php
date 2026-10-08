@extends('layouts.app')
@section('title', 'Detail Mental Assessment - ' . $assessment->name)
@section('page-title', 'Detail Mental Assessment')

@section('content')

    <a href="{{ route('mental.index') }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm mb-5 transition-all hover:text-white"
        style="background:transparent; border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">
        ← Kembali ke Mental Assessment
    </a>

    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="rounded-xl p-7 mb-6" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <h2 class="text-2xl font-bold mb-1" style="color:#00E676">{{ $assessment->name }}</h2>
                    <p class="text-sm" style="color:#7A8D9E">Assessment ID: #{{ $assessment->id }}</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold" style="color:#00E676">{{ $assessment->average_score }}/5</div>
                    <div class="text-xs" style="color:#7A8D9E">Rata-rata Skor</div>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <div style="color:#7A8D9E" class="text-xs">Tanggal Assessment</div>
                    <div class="font-semibold">{{ $assessment->assessment_date->format('d M Y H:i') }}</div>
                </div>
                <div>
                    <div style="color:#7A8D9E" class="text-xs">Atlet</div>
                    <div class="font-semibold">{{ $assessment->athlete->name ?? '-' }}</div>
                </div>
                <div>
                    <div style="color:#7A8D9E" class="text-xs">Pelatih</div>
                    <div class="font-semibold">{{ $assessment->user->name ?? '-' }}</div>
                </div>
                <div>
                    <div style="color:#7A8D9E" class="text-xs">Dibuat</div>
                    <div class="font-semibold">{{ $assessment->created_at->format('d M Y') }}</div>
                </div>
            </div>
        </div>

        {{-- Scores Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
            @foreach ($categories as $key => $label)
                @php
                    $score = $assessment->$key;
                    $noteField = $key . '_note';
                    $note = $assessment->$noteField;
                @endphp
                <div class="rounded-lg p-5" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
                    <div class="flex justify-between items-center mb-3">
                        <h3 class="font-semibold">{{ $label }}</h3>
                        @if ($score)
                            <span class="px-3 py-1 rounded-full text-sm font-bold"
                                style="background:rgba(0,230,118,0.1); color:#00E676">
                                {{ $score }}/5 · {{ $scoreLabels[$key][$score] ?? '' }}
                            </span>
                        @else
                            <span class="px-3 py-1 rounded-full text-sm text-xs"
                                style="background:rgba(128,128,128,0.1); color:#7A8D9E">
                                Tidak dinilai
                            </span>
                        @endif
                    </div>

                    {{-- Score Bar --}}
                    @if ($score)
                        <div class="w-full h-2 rounded-full mb-3" style="background:rgba(0,230,118,0.1)">
                            <div class="h-full rounded-full transition-all"
                                style="width: {{ ($score / 5) * 100 }}%; background:#00E676"></div>
                        </div>
                    @endif

                    {{-- Note --}}
                    @if ($note)
                        <p class="text-sm" style="color:#7A8D9E">{{ $note }}</p>
                    @else
                        <p class="text-sm italic" style="color:#7A8D9E">Tidak ada catatan</p>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Overall Assessment & Recommendations --}}
        @if ($assessment->overall_assessment || $assessment->recommendations)
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                @if ($assessment->overall_assessment)
                    <div class="rounded-lg p-5" style="background:rgba(0,230,118,0.05); border:1px solid rgba(0,230,118,0.15)">
                        <h3 class="font-semibold mb-3" style="color:#00E676">📋 Penilaian Keseluruhan</h3>
                        <p class="text-sm leading-relaxed">{{ $assessment->overall_assessment }}</p>
                    </div>
                @endif

                @if ($assessment->recommendations)
                    <div class="rounded-lg p-5" style="background:rgba(0,230,118,0.05); border:1px solid rgba(0,230,118,0.15)">
                        <h3 class="font-semibold mb-3" style="color:#00E676">💡 Rekomendasi</h3>
                        <p class="text-sm leading-relaxed">{{ $assessment->recommendations }}</p>
                    </div>
                @endif
            </div>
        @endif

        {{-- Action Buttons --}}
        <div class="flex gap-3 justify-between">
            <div class="flex gap-3">
                <a href="{{ route('mental.edit', $assessment->id) }}"
                    class="px-4 py-2 rounded-lg font-semibold transition-all" style="background:#FFB700; color:#0E1419">
                    ✏️ Edit
                </a>
                <form action="{{ route('mental.destroy', $assessment->id) }}" method="POST" class="inline"
                    onsubmit="return confirm('Hapus assessment ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 rounded-lg font-semibold transition-all"
                        style="background:#FF6B6B; color:#fff">
                        🗑️ Hapus
                    </button>
                </form>
            </div>

            <a href="{{ route('mental.index') }}" class="px-4 py-2 rounded-lg font-semibold transition-all"
                style="background:transparent; border:1px solid rgba(0,230,118,0.2); color:#7A8D9E">
                Kembali
            </a>
        </div>
    </div>

@endsection