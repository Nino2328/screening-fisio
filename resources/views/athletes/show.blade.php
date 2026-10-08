@extends('layouts.app')
@section('title', $athlete->name . ' - Detail Atlet')
@section('page-title', $athlete->name)

@section('content')

{{-- Back Button & Actions --}}
<div class="flex items-center justify-between mb-6">
    <a href="{{ route('athletes.index') }}" class="flex items-center gap-2 text-sm transition hover:text-opacity-80"
        style="color:#00E676">
        ← Kembali ke Data Atlet
    </a>
    <div class="flex gap-2">
        <button onclick="editAthleteModal()"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition hover:-translate-y-px"
            style="background:rgba(255,152,0,0.2); color:#FFB74D">
            Edit Data
        </button>
        <button onclick="deleteAthlete({{ $athlete->id }})"
            class="px-4 py-2 rounded-lg text-sm font-semibold transition hover:-translate-y-px"
            style="background:rgba(255,68,68,0.2); color:#FF4444">
            Hapus
        </button>
    </div>
</div>

@if(auth()->user()->role === 'pelatih')
@php
    $coachTestCount = $mentalAssessments->count() + $taktikAssessments->count();
@endphp
<div class="mb-6 rounded-3xl p-6" style="background:#0B1014; border:1px solid rgba(0,230,118,0.2);">
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-display text-3xl lg:text-4xl tracking-[0.15em] uppercase" style="color:#FFFFFF">
                {{ $athlete->name }}</h1>
            <p class="mt-2 text-sm" style="color:#7A8D9E">Profil atlet dan hasil assessment mental serta taktik.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('mental.create', ['athlete_id' => $athlete->id]) }}"
                class="inline-flex items-center gap-2 rounded-full px-5 py-3 text-sm font-semibold"
                style="background:#3A2E08; color:#FFE9A3; border:1px solid rgba(255,183,0,0.25);">
                + Mental
            </a>
            <a href="{{ route('taktik.create', ['athlete_id' => $athlete->id]) }}"
                class="inline-flex items-center gap-2 rounded-full px-5 py-3 text-sm font-semibold"
                style="background:#3A1515; color:#FFD0D0; border:1px solid rgba(255,107,107,0.25);">
                + Taktik
            </a>
        </div>
    </div>

    <div class="rounded-[2rem] border border-[#00E676]/15 bg-[#0E1419] p-6">
        <div class="flex flex-col md:flex-row gap-6 items-start">
            <div
                class="w-28 h-28 rounded-[2rem] overflow-hidden border border-[#00E676]/25 bg-[#101820] flex items-center justify-center">
                @if($athlete->photo)
                    <img src="{{ asset($athlete->photo) }}" alt="{{ $athlete->name }}"
                        class="w-full h-full object-cover">
                @else
                    <span class="text-4xl font-bold text-white">{{ strtoupper(substr($athlete->name, 0, 1)) }}</span>
                @endif
            </div>
            <div class="flex-1">
                <h2 class="text-3xl font-bold tracking-tight text-white">{{ $athlete->name }}</h2>
                <p class="mt-3 text-sm text-[#7A8D9E]">Bergabung:
                    {{ $athlete->created_at?->format('d M Y') ?? '-' }}</p>
                <div class="mt-5 flex flex-wrap gap-3">
                    <span
                        class="inline-flex items-center rounded-full bg-[#0A1F2F] px-4 py-2 text-xs font-semibold text-[#8AC7FF] border border-[#8AC7FF]/15">{{ $athlete->position ?? '-' }}</span>
                    <span
                        class="inline-flex items-center rounded-full bg-[#0F2810] px-4 py-2 text-xs font-semibold text-[#C4FFD7] border border-[#C4FFD7]/15">{{ $coachTestCount }}
                        Assessment Tercatat</span>
                </div>
            </div>
        </div>

        <div class="mt-8">
            <h3 class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-4">Data Fisik</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-3xl border border-[#00E676]/10 bg-[#11181F] p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-2">Usia</p>
                    <p class="font-semibold text-base text-white">
                        {{ $athlete->age ? $athlete->age . ' tahun' : '-' }}</p>
                </div>
                <div class="rounded-3xl border border-[#00E676]/10 bg-[#11181F] p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-2">Jenis Kelamin</p>
                    <p class="font-semibold text-base text-white">
                        {{ $athlete->gender === 'M' ? 'Laki-laki' : ($athlete->gender === 'F' ? 'Perempuan' : '-') }}
                    </p>
                </div>
                <div class="rounded-3xl border border-[#00E676]/10 bg-[#11181F] p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-2">Tempat, Tanggal Lahir</p>
                    <p class="font-semibold text-base text-white">
                        {{ ($athlete->birth_place ?? '-') . ', ' . ($athlete->birth_date ? \Carbon\Carbon::parse($athlete->birth_date)->format('d M Y') : '-') }}
                    </p>
                </div>
                <div class="rounded-3xl border border-[#00E676]/10 bg-[#11181F] p-4">
                    <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-2">Nomor Telepon</p>
                    <p class="font-semibold text-base text-white">{{ $athlete->phone ?? '-' }}</p>
                </div>
            </div>
            <div class="mt-4 rounded-[1.75rem] border border-[#00E676]/10 bg-[#11181F] p-5">
                <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-2">Tinggi Badan</p>
                <p class="font-semibold text-base text-white">
                    {{ $athlete->height ? number_format($athlete->height, 1) . ' cm' : '-' }}</p>
            </div>
            <div class="mt-4 rounded-[1.75rem] border border-[#00E676]/10 bg-[#11181F] p-5">
                <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-2">Berat Badan</p>
                <p class="font-semibold text-base text-white">
                    {{ $athlete->weight ? number_format($athlete->weight, 1) . ' kg' : '-' }}</p>
            </div>
        </div>
    </div>
</div>
@elseif(in_array(auth()->user()->role, ['admin', 'fisio'], true))
<div class="mb-6 rounded-3xl p-6" style="background:#0B1014; border:1px solid rgba(0,230,118,0.2);">
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 mb-6">
        <div>
            <h1 class="font-display text-3xl lg:text-4xl tracking-[0.15em] uppercase" style="color:#FFFFFF">
                {{ $athlete->name }}</h1>
            <p class="mt-2 text-sm" style="color:#7A8D9E">Profil atlet, progres screening, dan hasil pemeriksaan fisik.</p>
        </div>
        <a href="{{ route('forms.endurance', ['athlete_id' => $athlete->id]) }}"
            class="inline-flex items-center gap-2 rounded-full px-5 py-3 text-sm font-semibold transition hover:bg-[#0F3E1F]"
            style="background:#0F3F17; color:#C9FFD6; border:1px solid rgba(0,230,118,0.25);">
            + Input Tes
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-[2fr_1.1fr] gap-5">
        <div class="rounded-[2rem] border border-[#00E676]/15 bg-[#0E1419] p-6">
            <div class="flex flex-col md:flex-row gap-6 items-start">
                <div
                    class="w-28 h-28 rounded-[2rem] overflow-hidden border border-[#00E676]/25 bg-[#101820] flex items-center justify-center">
                    @if($athlete->photo)
                        <img src="{{ asset($athlete->photo) }}" alt="{{ $athlete->name }}"
                            class="w-full h-full object-cover">
                    @else
                        <span class="text-4xl font-bold text-white">{{ strtoupper(substr($athlete->name, 0, 1)) }}</span>
                    @endif
                </div>
                <div class="flex-1">
                    <div class="flex flex-wrap items-center gap-3">
                        <h2 class="text-3xl font-bold tracking-tight text-white">{{ $athlete->name }}</h2>
                    </div>
                    <p class="mt-3 text-sm text-[#7A8D9E]">Bergabung:
                        {{ $athlete->created_at?->format('d M Y') ?? '-' }}</p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <span
                            class="inline-flex items-center rounded-full bg-[#0A1F2F] px-4 py-2 text-xs font-semibold text-[#8AC7FF] border border-[#8AC7FF]/15">{{ $athlete->position ?? '-' }}</span>
                        <span
                            class="inline-flex items-center rounded-full bg-[#0F2810] px-4 py-2 text-xs font-semibold text-[#C4FFD7] border border-[#C4FFD7]/15">{{ $testResults->count() }}
                            Tes Tercatat</span>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <h3 class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-4">Data Fisik</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="rounded-3xl border border-[#00E676]/10 bg-[#11181F] p-4">
                        <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-2">Usia</p>
                        <p class="font-semibold text-base text-white">
                            {{ $athlete->age ? $athlete->age . ' tahun' : '-' }}</p>
                    </div>
                    <div class="rounded-3xl border border-[#00E676]/10 bg-[#11181F] p-4">
                        <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-2">Jenis Kelamin</p>
                        <p class="font-semibold text-base text-white">
                            {{ $athlete->gender === 'M' ? 'Laki-laki' : ($athlete->gender === 'F' ? 'Perempuan' : '-') }}
                        </p>
                    </div>
                    <div class="rounded-3xl border border-[#00E676]/10 bg-[#11181F] p-4">
                        <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-2">Tempat, Tanggal Lahir</p>
                        <p class="font-semibold text-base text-white">
                            {{ ($athlete->birth_place ?? '-') . ', ' . ($athlete->birth_date ? \Carbon\Carbon::parse($athlete->birth_date)->format('d M Y') : '-') }}
                        </p>
                    </div>
                    <div class="rounded-3xl border border-[#00E676]/10 bg-[#11181F] p-4">
                        <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-2">Nomor Telepon</p>
                        <p class="font-semibold text-base text-white">{{ $athlete->phone ?? '-' }}</p>
                    </div>
                </div>
                <div class="mt-4 rounded-[1.75rem] border border-[#00E676]/10 bg-[#11181F] p-5">
                    <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-2">Tinggi Badan</p>
                    <p class="font-semibold text-base text-white">
                        {{ $athlete->height ? number_format($athlete->height, 1) . ' cm' : '-' }}</p>
                </div>
                <div class="mt-4 rounded-[1.75rem] border border-[#00E676]/10 bg-[#11181F] p-5">
                    <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E] mb-2">Berat Badan</p>
                    <p class="font-semibold text-base text-white">
                        {{ $athlete->weight ? number_format($athlete->weight, 1) . ' kg' : '-' }}</p>
                </div>
            </div>
        </div>

        @php
            $centerX = 180;
            $centerY = 148;
            $radius = 88;
            $labelRadius = 119;
            $radarAngles = $radarStats->count()
                ? $radarStats->keys()->map(fn($index) => -90 + (360 / $radarStats->count()) * $index)
                : collect();
            $statColors = [
                'Daya tahan' => '#00E676',
                'Kecepatan' => '#FFD740',
                'Kelincahan' => '#40C4FF',
                'Kekuatan' => '#FF6E6E',
                'Daya ledak' => '#FFAB40',
                'Fungsional' => '#B39DDB',
            ];
            $radarCoordinates = $radarStats->map(function ($stat, $index) use ($radarAngles, $centerX, $centerY, $radius, $labelRadius) {
                $angle = deg2rad($radarAngles[$index]);
                $valueRadius = $radius * (($stat['score'] ?? 0) / 5);
                $labelX = $centerX + cos($angle) * $labelRadius;
                $labelY = $centerY + sin($angle) * $labelRadius;

                return [
                    'x' => round($centerX + cos($angle) * $valueRadius, 1),
                    'y' => round($centerY + sin($angle) * $valueRadius, 1),
                    'labelX' => round($labelX, 1),
                    'labelY' => round($labelY, 1),
                    'anchor' => $labelX < ($centerX - 14) ? 'end' : ($labelX > ($centerX + 14) ? 'start' : 'middle'),
                    'color' => $stat['color'],
                    'label' => $stat['label'],
                    'score' => $stat['score'],
                ];
            });
            $radarPoints = $radarCoordinates->map(fn($point) => $point['x'] . ',' . $point['y'])->implode(' ');
            $radarSegments = $radarCoordinates->filter(function ($point, $index) use ($radarCoordinates) {
                $next = $radarCoordinates[($index + 1) % $radarCoordinates->count()];
                return $point['score'] !== null && $next['score'] !== null;
            });
        @endphp
        <div class="rounded-[2rem] border border-[#00E676]/15 bg-[#0E1419] p-5">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <p class="text-xs uppercase tracking-[0.24em] text-[#7A8D9E]">Radar Performa</p>
                    <p class="font-semibold text-lg text-white">Skala penilaian 1–5</p>
                </div>
                <span class="text-sm font-semibold text-[#00E676]">{{ $ratedTestCount }}/10 dinilai</span>
            </div>
            <div class="mx-auto w-full max-w-90">
                <svg viewBox="0 0 360 310" role="img" aria-label="Radar performa 10 tes atlet pada skala 1 sampai 5"
                    class="w-full h-auto overflow-visible">
                    @foreach([20, 40, 60, 80, 100] as $ring)
                        @php
                            $ringPoints = $radarAngles->map(function ($angle) use ($ring, $centerX, $centerY, $radius) {
                                $r = $radius * $ring / 100;
                                return round($centerX + cos(deg2rad($angle)) * $r, 1) . ',' . round($centerY + sin(deg2rad($angle)) * $r, 1);
                            })->implode(' ');
                        @endphp
                        <polygon points="{{ $ringPoints }}" fill="none"
                            stroke="rgba(122,141,158,{{ $ring === 100 ? '0.48' : '0.2' }})"
                            stroke-width="{{ $ring === 100 ? 1.4 : 1 }}" />
                        <text x="{{ $centerX + 4 }}" y="{{ round($centerY - ($radius * $ring / 100) + 3, 1) }}"
                            fill="#73838D" font-size="8">{{ $ring / 20 }}</text>
                    @endforeach
                    @foreach($radarAngles as $angle)
                        <line x1="{{ $centerX }}" y1="{{ $centerY }}"
                            x2="{{ round($centerX + cos(deg2rad($angle)) * $radius, 1) }}"
                            y2="{{ round($centerY + sin(deg2rad($angle)) * $radius, 1) }}" stroke="rgba(122,141,158,0.3)"
                            stroke-width="1" />
                    @endforeach
                    @if($ratedTestCount === $radarStats->count() && $ratedTestCount > 2)
                        <polygon points="{{ $radarPoints }}" fill="rgba(0,230,118,0.1)" stroke="none" />
                    @endif
                    @foreach($radarSegments as $index => $point)
                    @php($nextPoint = $radarCoordinates[($index + 1) % $radarCoordinates->count()])
                                            <line x1="{{ $point['x'] }}" y1="{{ $point['y'] }}" x2="{{ $nextPoint['x'] }}"
                                                y2="{{ $nextPoint['y'] }}" stroke="{{ $point['color'] }}" stroke-width="3"
                                                stroke-linecap="round" />
                                            @endforeach
                                            @foreach($radarCoordinates as $point)
                                                @if($point['score'] !== null)
                                                    <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="3.5" fill="#E8F0F7"
                                                        stroke="{{ $point['color'] }}" stroke-width="2">
                                                        <title>{{ $point['label'] }}: {{ number_format($point['score'], 1) }}/5</title>
                                                    </circle>
                                                @endif
                                                <text x="{{ $point['labelX'] }}" y="{{ $point['labelY'] }}" text-anchor="{{ $point['anchor'] }}"
                                                    dominant-baseline="middle" fill="{{ $point['score'] !== null ? '#D6E0DB' : '#71808A' }}"
                                                    font-size="9.5">{{ $point['label'] }}</text>
                                            @endforeach
                                        </svg>
                                    </div>
                                    <p class="mt-1 text-center text-[10px] text-[#71808A]">Garis menunjukkan rating tiap tes; kategori tanpa
                                        rating ditampilkan sebagai celah.</p>
                                    <div class="mt-2 mb-2 text-[10px] font-semibold uppercase tracking-wider text-[#82938A]">Cakupan tes per
                                        kelompok</div>
                                    <div class="grid grid-cols-2 gap-x-4 gap-y-3">
                                        @foreach($screeningStats as $stat)
                                            <div>
                                                <div class="flex justify-between text-[11px] mb-1">
                                                    <span class="flex items-center gap-1.5 text-[#A9B4BE]">
                                                        <span class="inline-block w-2 h-2 rounded-full"
                                                            style="background:{{ $statColors[$stat['label']] ?? '#00E676' }}"></span>
                                                        {{ $stat['label'] }}
                                                    </span>
                                                    <span
                                                        style="color:{{ $statColors[$stat['label']] ?? '#00E676' }}">{{ $stat['completed'] }}/{{ $stat['total'] }}</span>
                                                </div>
                                                <div class="h-1.5 rounded-full bg-[#19232B] overflow-hidden">
                                                    <div class="h-full rounded-full"
                                                        style="width:{{ $stat['percentage'] }}%; background:{{ $statColors[$stat['label']] ?? '#00E676' }}">
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
{{-- Athlete Profile Card (Fisio View) --}}
<div class="grid grid-cols-1 gap-5 mb-6">
    <div class="rounded-xl p-6" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <div class="flex flex-col md:flex-row gap-6 items-start mb-6">
            <div class="w-24 h-24 rounded-3xl flex items-center justify-center text-3xl flex-shrink-0 overflow-hidden"
                style="background:rgba(0,230,118,0.1); border:2px solid rgba(0,230,118,0.2)">
                @if($athlete->photo)
                    <img src="{{ asset($athlete->photo) }}" alt="{{ $athlete->name }}" class="w-full h-full object-cover">
                @else
                    {{ strtoupper(substr($athlete->name, 0, 1)) }}
                @endif
            </div>
            <div class="flex-1">
                <h2 class="font-display text-2xl tracking-wide mb-1">{{ $athlete->name }}</h2>
                <p class="text-sm mb-4" style="color:#7A8D9E">{{ $athlete->age ? $athlete->age . ' tahun' : '-' }} ·
                    {{ $athlete->gender === 'M' ? 'Laki-laki' : ($athlete->gender === 'F' ? 'Perempuan' : '-') }}</p>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="rounded-2xl p-4" style="background:#141B22">
                        <p class="text-xs uppercase tracking-widest mb-2" style="color:#7A8D9E">Posisi</p>
                        <p class="font-semibold">{{ $athlete->position ?? '-' }}</p>
                    </div>
                    <div class="rounded-2xl p-4" style="background:#141B22">
                        <p class="text-xs uppercase tracking-widest mb-2" style="color:#7A8D9E">Nomor Telepon</p>
                        <p class="font-semibold">{{ $athlete->phone ?? '-' }}</p>
                    </div>
                    <div class="rounded-2xl p-4" style="background:#141B22">
                        <p class="text-xs uppercase tracking-widest mb-2" style="color:#7A8D9E">Tinggi Badan</p>
                        <p class="font-semibold">{{ $athlete->height ? $athlete->height . ' cm' : '-' }}</p>
                    </div>
                    <div class="rounded-2xl p-4" style="background:#141B22">
                        <p class="text-xs uppercase tracking-widest mb-2" style="color:#7A8D9E">Berat Badan</p>
                        <p class="font-semibold">{{ $athlete->weight ? $athlete->weight . ' kg' : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="rounded-3xl p-4" style="background:#141B22; border:1px solid rgba(0,230,118,0.12)">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-xs uppercase tracking-widest mb-2" style="color:#7A8D9E">Nama</p>
                    <p class="font-semibold">{{ $athlete->name }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest mb-2" style="color:#7A8D9E">Usia</p>
                    <p class="font-semibold">{{ $athlete->age ? $athlete->age . ' thn' : '-' }}</p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest mb-2" style="color:#7A8D9E">Jenis Kelamin</p>
                    <p class="font-semibold">
                        {{ $athlete->gender === 'M' ? 'Laki-laki' : ($athlete->gender === 'F' ? 'Perempuan' : '-') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs uppercase tracking-widest mb-2" style="color:#7A8D9E">Posisi</p>
                    <p class="font-semibold">{{ $athlete->position ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mt-4 text-sm">
            <div class="rounded-2xl p-4" style="background:#141B22">
                <p class="text-xs uppercase tracking-widest mb-2" style="color:#7A8D9E">Total Tes</p>
                <p class="font-display text-2xl" style="color:#00E676">{{ $testResults->count() }}</p>
            </div>
            <div class="rounded-2xl p-4" style="background:#141B22">
                <p class="text-xs uppercase tracking-widest mb-2" style="color:#7A8D9E">Terakhir Tes</p>
                <p class="font-semibold">{{ $latestTestDate?->format('d M Y') ?? '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->role === 'pelatih')
    <div class="grid grid-cols-1 gap-5">
        <div class="rounded-xl p-6" style="background:#0E1419; border:1px solid rgba(255,183,0,0.2)">
            <h3 class="font-display text-lg tracking-widest mb-5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full inline-block" style="background:#FFB700"></span>
                Hasil Assessment Mental
            </h3>
            @if($mentalAssessments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="border-bottom:2px solid rgba(255,183,0,0.15)">
                                <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Tanggal</th>
                                <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Rata-rata</th>
                                <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Ringkasan</th>
                                <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mentalAssessments as $assessment)
                                <tr style="border-bottom:1px solid rgba(255,183,0,0.08)">
                                    <td class="py-4 px-4 whitespace-nowrap" style="color:#7A8D9E">
                                        {{ $assessment->assessment_date?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="py-4 px-4 font-semibold" style="color:#FFB700">{{ $assessment->average_score }}/5</td>
                                    <td class="py-4 px-4" style="color:#7A8D9E">{{ $assessment->overall_assessment ?: '—' }}</td>
                                    <td class="py-4 px-4">
                                        <a href="{{ route('mental.show', $assessment->id) }}" class="text-xs font-semibold"
                                            style="color:#FFB700">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-lg mb-3" style="color:#7A8D9E">Belum ada assessment mental untuk atlet ini</p>
                    <a href="{{ route('mental.create', ['athlete_id' => $athlete->id]) }}"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition hover:-translate-y-px inline-block"
                        style="background:#FFB700; color:#080C10">
                        Mulai Assessment Mental
                    </a>
                </div>
            @endif
        </div>

        <div class="rounded-xl p-6" style="background:#0E1419; border:1px solid rgba(255,107,107,0.2)">
            <h3 class="font-display text-lg tracking-widest mb-5 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full inline-block" style="background:#FF6B6B"></span>
                Hasil Assessment Taktik
            </h3>
            @if($taktikAssessments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr style="border-bottom:2px solid rgba(255,107,107,0.15)">
                                <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Tanggal</th>
                                <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Rata-rata</th>
                                <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Ringkasan</th>
                                <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($taktikAssessments as $assessment)
                                <tr style="border-bottom:1px solid rgba(255,107,107,0.08)">
                                    <td class="py-4 px-4 whitespace-nowrap" style="color:#7A8D9E">
                                        {{ $assessment->assessment_date?->format('d M Y H:i') ?? '-' }}</td>
                                    <td class="py-4 px-4 font-semibold" style="color:#FF6B6B">{{ $assessment->average_score }}/5</td>
                                    <td class="py-4 px-4" style="color:#7A8D9E">{{ $assessment->overall_assessment ?: '—' }}</td>
                                    <td class="py-4 px-4">
                                        <a href="{{ route('taktik.show', $assessment->id) }}" class="text-xs font-semibold"
                                            style="color:#FF6B6B">Detail</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <p class="text-lg mb-3" style="color:#7A8D9E">Belum ada assessment taktik untuk atlet ini</p>
                    <a href="{{ route('taktik.create', ['athlete_id' => $athlete->id]) }}"
                        class="px-4 py-2 rounded-lg text-sm font-semibold transition hover:-translate-y-px inline-block"
                        style="background:#FF6B6B; color:#fff">
                        Mulai Assessment Taktik
                    </a>
                </div>
            @endif
        </div>
    </div>
@elseif(in_array(auth()->user()->role, ['admin', 'fisio'], true))
{{-- Test Results --}}
<div class="rounded-xl p-6" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
    <h3 class="font-display text-lg tracking-widest mb-5 flex items-center gap-2">
        <span class="w-2 h-2 rounded-full inline-block" style="background:#00E676"></span>
        Hasil Tes Fisioterapi
    </h3>

    @if($testResults->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:2px solid rgba(0,230,118,0.15)">
                        <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Jenis Tes</th>
                        <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Hasil</th>
                        <th class="text-left py-3 px-4 font-semibold" style="color:#7A8D9E">Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($testResults as $result)
                        <tr style="border-bottom:1px solid rgba(0,230,118,0.08)">
                            <td class="py-4 px-4 font-semibold">{{ $result->test_type_label }}</td>
                            <td class="py-4 px-4">
                                <div class="flex flex-wrap gap-x-4 gap-y-1">
                                    @forelse($result->display_results as $label => $value)
                                        <span><span class="text-[#7A8D9E]">{{ $label }}:</span> <strong
                                                class="text-[#00E676]">{{ $value }}</strong></span>
                                    @empty
                                        <span class="text-[#7A8D9E]">Detail hasil belum tersedia</span>
                                    @endforelse
                                </div>
                                @if($result->conclusion)
                                    <p class="mt-2 text-xs text-[#7A8D9E]">{{ $result->conclusion }}</p>
                                @endif
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap" style="color:#7A8D9E">
                                {{ $result->test_date?->format('d M Y H:i') ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-12">
            <p class="text-lg mb-3" style="color:#7A8D9E">Belum ada hasil tes untuk atlet ini</p>
            <a href="{{ route('forms.endurance', ['athlete_id' => $athlete->id]) }}"
                class="px-4 py-2 rounded-lg text-sm font-semibold transition hover:-translate-y-px inline-block"
                style="background:#00E676; color:#080C10">
                Mulai Input Tes
            </a>
        </div>
    @endif
</div>
@endif

@endsection

@push('scripts')
    <script>
        // Modal untuk edit
        function editAthleteModal() {
            const modal = document.getElementById('editModal');
            if (modal) {
                document.getElementById('editName').value = '{{ $athlete->name }}';
                document.getElementById('editPosition').value = '{{ $athlete->position }}';
                document.getElementById('editAge').value = '{{ $athlete->age }}';
                document.getElementById('editGender').value = '{{ $athlete->gender }}';
                document.getElementById('editHeight').value = '{{ $athlete->height }}';
                document.getElementById('editWeight').value = '{{ $athlete->weight }}';
                document.getElementById('editPhone').value = '{{ $athlete->phone }}';
                modal.classList.remove('hidden');
            } else {
                // Redirect ke halaman edit jika modal tidak ada
                alert('Fitur edit akan datang segera');
            }
        }

        function deleteAthlete(id) {
            if (confirm('Apakah Anda yakin ingin menghapus data atlet ini? Data tidak dapat dipulihkan!')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/athletes/${id}`;
                form.innerHTML = `@csrf <input type="hidden" name="_method" value="DELETE">`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            if (modal) modal.classList.add('hidden');
        }

        // Close modal on Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') closeEditModal();
        });
    </script>
@endpush