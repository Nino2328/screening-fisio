@extends('layouts.app')
@section('title', 'Detail Laporan') @section('page-title', 'Laporan Per Atlet')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-xl font-semibold">{{ $athlete->name }}</h2>
            <div class="text-sm text-[#7A8D9E] mt-1">Posisi: {{ $athlete->position ?? '-' }} • Umur:
                {{ $athlete->age ?? '-' }}
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('report.index') }}"
                class="px-4 py-2 rounded-lg text-sm font-semibold bg-[#141B22] text-white hover:bg-[#1c2330] transition"
                style="border:1px solid rgba(255,255,255,0.1);">
                ← Kembali
            </a>
            <a href="{{ route('report.export.pdf', ['athlete' => $athlete->name]) }}" target="_blank"
                class="px-4 py-2 rounded-lg text-sm font-semibold bg-[#00E676] text-[#0A0F12] hover:bg-[#2fff8c] transition">
                📄 Download PDF
            </a>
        </div>
    </div>

    <div class="rounded-xl p-5" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <h3 class="font-display text-lg tracking-widest mb-4 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full" style="background:#00E676"></span>
            Hasil Tes
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:700px">
                <thead>
                    <tr style="border-bottom:2px solid rgba(0,230,118,0.2)">
                        <th class="text-left py-3 px-3 text-xs uppercase tracking-widest font-mono2" style="color:#00E676">
                            Jenis Tes</th>
                        <th class="text-left py-3 px-3 text-xs uppercase tracking-widest font-mono2" style="color:#00E676">
                            Tanggal</th>
                        <th class="text-left py-3 px-3 text-xs uppercase tracking-widest font-mono2" style="color:#00E676">
                            Rating</th>
                        <th class="text-left py-3 px-3 text-xs uppercase tracking-widest font-mono2" style="color:#00E676">
                            Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $result)
                        <tr style="border-bottom:1px solid rgba(0,230,118,0.08)" class="hover:bg-[#141B22] transition-colors">
                            <td class="py-3 px-3 font-semibold">{{ $result->test_type_label }}</td>
                            <td class="py-3 px-3 whitespace-nowrap text-[#7A8D9E]">
                                {{ $result->test_date?->format('d M Y H:i') ?? '-' }}</td>
                            <td class="py-3 px-3">
                                @forelse($result->display_ratings as $label => $rating)
                                    <div class="mb-1 last:mb-0">
                                        @if($label !== 'Rating')<span class="text-xs text-[#7A8D9E]">{{ $label }} </span>@endif
                                        <x-rating-badge :rating="$rating" />
                                    </div>
                                @empty
                                    <span class="text-[#7A8D9E]">—</span>
                                @endforelse
                            </td>
                            <td class="py-3 px-3 text-xs text-[#7A8D9E]">
                                @forelse($result->display_results as $label => $value)
                                    <span class="inline-block mr-3 mb-1"><span>{{ $label }}:</span> <strong
                                            class="text-white">{{ $value }}</strong></span>
                                @empty
                                    —
                                @endforelse
                                @if($result->conclusion)
                                    <p class="mt-1">{{ $result->conclusion }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-10 text-center text-[#7A8D9E]">Belum ada data tes untuk atlet ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection