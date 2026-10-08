@props(['rating'])

@php
    $map = [
        'excellent' => ['bg' => 'rgba(0,230,118,0.15)', 'color' => '#00E676'],
        'elite' => ['bg' => 'rgba(0,230,118,0.15)', 'color' => '#00E676'],
        'luar biasa' => ['bg' => 'rgba(0,230,118,0.15)', 'color' => '#00E676'],
        'baik sekali' => ['bg' => 'rgba(0,230,118,0.15)', 'color' => '#00E676'],
        'optimal' => ['bg' => 'rgba(0,230,118,0.15)', 'color' => '#00E676'],
        'good' => ['bg' => 'rgba(79,195,247,0.14)', 'color' => '#4FC3F7'],
        'baik' => ['bg' => 'rgba(79,195,247,0.14)', 'color' => '#4FC3F7'],
        'above average' => ['bg' => 'rgba(0,188,212,0.15)', 'color' => '#26C6DA'],
        'diatas rata-rata' => ['bg' => 'rgba(0,188,212,0.15)', 'color' => '#26C6DA'],
        'diatas rata rata' => ['bg' => 'rgba(0,188,212,0.15)', 'color' => '#26C6DA'],
        'average' => ['bg' => 'rgba(255,214,0,0.12)', 'color' => '#FFD740'],
        'rata-rata' => ['bg' => 'rgba(255,214,0,0.12)', 'color' => '#FFD740'],
        'rata rata' => ['bg' => 'rgba(255,214,0,0.12)', 'color' => '#FFD740'],
        'below average' => ['bg' => 'rgba(255,171,64,0.16)', 'color' => '#FFAB40'],
        'below' => ['bg' => 'rgba(255,171,64,0.16)', 'color' => '#FFAB40'],
        'di bawah rata-rata' => ['bg' => 'rgba(255,171,64,0.16)', 'color' => '#FFAB40'],
        'dibawah rata-rata' => ['bg' => 'rgba(255,171,64,0.16)', 'color' => '#FFAB40'],
        'dibawah rata rata' => ['bg' => 'rgba(255,171,64,0.16)', 'color' => '#FFAB40'],
        'di bawah rata rata' => ['bg' => 'rgba(255,171,64,0.16)', 'color' => '#FFAB40'],
        'poor' => ['bg' => 'rgba(255,68,68,0.12)', 'color' => '#FF4444'],
        'rendah' => ['bg' => 'rgba(255,68,68,0.12)', 'color' => '#FF4444'],
        'miskin' => ['bg' => 'rgba(255,68,68,0.12)', 'color' => '#FF4444'],
        'sangat miskin' => ['bg' => 'rgba(255,68,68,0.12)', 'color' => '#FF4444'],
        'tersimpan' => ['bg' => 'rgba(179,157,219,0.15)', 'color' => '#B39DDB'],
    ];
    $key = mb_strtolower(trim((string) $rating));
    $style = $map[$key] ?? ['bg' => 'rgba(120,120,120,0.15)', 'color' => '#aaa'];
@endphp

<span class="inline-block px-3 py-1 rounded-full text-xs font-bold"
    style="background:{{ $style['bg'] }}; color:{{ $style['color'] }}">
    {{ ucfirst($rating) }}
</span>