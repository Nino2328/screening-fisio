@props(['icon', 'title', 'subtitle', 'tag', 'tagColor' => 'green'])

@php
    $tagColors = [
        'green' => 'background:rgba(0,230,118,0.12); color:#00E676',
        'yellow' => 'background:rgba(255,214,0,0.12); color:#FFD600',
        'blue' => 'background:rgba(79,195,247,0.12); color:#4FC3F7',
        'red' => 'background:rgba(255,68,68,0.12); color:#FF4444',
        'orange' => 'background:rgba(255,120,0,0.15); color:#FF9800',
        'purple' => 'background:rgba(156,39,176,0.15); color:#CE93D8',
        'cyan' => 'background:rgba(0,188,212,0.12); color:#00BCD4',
    ];
    $tc = $tagColors[$tagColor] ?? $tagColors['green'];
@endphp

<div class="flex items-start justify-between pb-5 mb-2" style="border-bottom:1px solid rgba(0,230,118,0.15)">
    <div>
        <h2 class="font-display text-2xl md:text-3xl tracking-widest" style="color:#00E676">
            {{ $icon }} {{ $title }}
        </h2>
        <p class="text-sm mt-1" style="color:#7A8D9E">{{ $subtitle }}</p>
    </div>
    <span class="px-4 py-1.5 rounded-full text-xs font-bold font-mono2 flex-shrink-0 ml-4"
        style="{{ $tc }}">{{ strtoupper($tag) }}</span>
</div>