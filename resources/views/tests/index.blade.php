@extends('layouts.app')
@section('title', 'Semua Tes')
@section('page-title', 'Semua Tes')

@section('content')

    <div class="rounded-xl p-6" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <h3 class="font-display text-xl tracking-widest mb-6 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full" style="background:#00E676"></span>
            Semua Jenis Tes Screening Fisioterapi Sepak Bola
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach([
                    ['forms.endurance', '🏃', 'ENDURANCE', 'YoYo Intermittent Recovery Test', 'Mengukur kapasitas aerobik dan daya tahan kardiovaskular atlet dengan lari ulang-alik 20m.', 'green'],
                    ['forms.speed', '⚡', 'SPEED', 'Speed 50 Meter Sprint', 'Mengukur kecepatan maksimal, akselerasi, dan kemampuan sprint atlet dalam jarak pendek.', 'yellow'],
                    ['forms.agility', '🔄', 'AGILITY', 'Hexagonal & Illinois Agility', 'Mengukur kelincahan dan kemampuan mengubah arah dengan cepat dan akurat.', 'blue'],
                    ['forms.strength', '💪', 'STRENGTH', 'Push Up & Sit Up', 'Mengukur kekuatan otot dada, bahu, lengan, dan otot inti (core strength) atlet.', 'red'],
                    ['forms.power', '🦵', 'POWER', 'Standing Long Jump (Broad Jump)', 'Mengukur power eksplosif otot tungkai dengan lompatan dua kaki tanpa awalan.', 'orange'],
                    ['forms.accuracy', '🎯', 'SKILL', 'Akurasi Tendangan (Shooting)', 'Mengukur ketepatan tendangan bola ke sasaran dari jarak 16.5 meter ke gawang.', 'purple'],
                    ['forms.reaction', '👁️', 'REACTION', 'Test Kecepatan Reaksi', 'Mengukur waktu reaksi terhadap stimulus visual menggunakan ruler drop test.', 'red'],
                    ['forms.coordination', '🌀', 'COORDINATION', 'Fukuda Step Test (Koordinasi)', 'Mengukur keseimbangan dinamis dan koordinasi neuromuskuler melalui marching test.', 'cyan'],
                    ['forms.flexibility', '🦶', 'FLEXIBILITY', 'Ankle Flexibility Test', 'Mengukur fleksibilitas sendi ankle dan kemampuan dorsifleksi untuk mencegah cedera.', 'yellow'],
                    ['forms.balance', '⚖️', 'BALANCE', 'Y Balance Test (YBT)', 'Mengukur stabilitas dinamis dan kontrol neuromuskuler ekstremitas bawah dalam 3 arah.', 'green'],
                ] as [$route, $icon, $cat, $name, $desc, $color])

                @php
                    $tagColors = [
                        'green' => 'color:#00E676',
                        'yellow' => 'color:#FFD600',
                        'blue' => 'color:#4FC3F7',
                        'red' => 'color:#FF4444',
                        'orange' => 'color:#FF9800',
                        'purple' => 'color:#CE93D8',
                        'cyan' => 'color:#00BCD4',
                    ];
                    $tc = $tagColors[$color] ?? $tagColors['green'];
                @endphp

                <a href="{{ route($route) }}"
                   class="rounded-xl p-5 transition-all hover:-translate-y-1 group relative overflow-hidden"
                   style="background:#141B22; border:1px solid rgba(0,230,118,0.12)">
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-200"
                         style="background:#00E676"></div>
                    <div class="text-3xl mb-3">{{ $icon }}</div>
                    <div class="text-xs font-mono2 uppercase tracking-widest mb-1.5" style="{{ $tc }}">{{ $cat }}</div>
                    <div class="font-display text-lg tracking-wide mb-2">{{ $name }}</div>
                    <p class="text-xs leading-relaxed" style="color:#7A8D9E">{{ $desc }}</p>
                    <div class="mt-3 inline-block px-3 py-1 rounded-full text-xs font-bold"
                         style="background:rgba(0,230,118,0.08); {{ $tc }}">{{ $cat }}</div>
                </a>
            @endforeach
        </div>
    </div>

@endsection
