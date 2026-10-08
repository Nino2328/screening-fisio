@props(['icon', 'value', 'label', 'delta' => null, 'deltaUp' => true, 'color' => 'green'])

@php
    $colors = [
        'green' => 'rgba(0,230,118,0.15)',
        'blue' => 'rgba(79,195,247,0.15)',
        'red' => 'rgba(255,68,68,0.15)',
        'yellow' => 'rgba(255,214,0,0.15)',
        'orange' => 'rgba(255,120,0,0.15)',
    ];
    $bg = $colors[$color] ?? $colors['green'];
@endphp

<div class="rounded-xl p-5 transition-all hover:-translate-y-0.5 hover:border-[rgba(0,230,118,0.35)]"
    style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
    <div class="flex items-center justify-between mb-3">
        <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl" style="background:{{ $bg }}">
            {{ $icon }}</div>
        @if($delta)
            <span class="text-xs font-semibold {{ $deltaUp ? 'text-[#00E676]' : 'text-red-400' }}">
                {{ $deltaUp ? '↑' : '↓' }} {{ $delta }}
            </span>
        @endif
    </div>
    <div class="font-display text-4xl leading-none">{{ $value }}</div>
    <div class="text-xs mt-1" style="color:#7A8D9E">{{ $label }}</div>
</div>