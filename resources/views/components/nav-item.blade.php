@props(['route', 'icon', 'label', 'badge' => null])

@php
    $isActive = request()->routeIs($route);
@endphp

<a href="{{ route($route) }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-lg text-sm font-medium mb-0.5 transition-all duration-200
          {{ $isActive
    ? 'text-[#00E676]'
    : 'text-[#7A8D9E] hover:text-white hover:bg-[#141B22]' }}"
    style="{{ $isActive ? 'background:rgba(0,230,118,0.1); border:1px solid rgba(0,230,118,0.15)' : 'border:1px solid transparent' }}">
    <span class="w-5 text-center flex-shrink-0 text-base">{{ $icon }}</span>
    <span class="flex-1 truncate">{{ $label }}</span>
    @if($badge)
        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full font-mono2"
            style="background:#00E676; color:#080C10">{{ $badge }}</span>
    @endif
</a>