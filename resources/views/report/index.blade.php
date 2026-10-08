@extends('layouts.app')
@section('title', 'Laporan') @section('page-title', 'Laporan')

@section('content')

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
        @foreach($summaryStats as $stat)
            <div class="rounded-xl p-5 text-center" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
                <div class="font-display text-3xl" style="color:{{ $stat['color'] }}">{{ $stat['value'] }}</div>
                <div class="text-xs mt-1" style="color:#7A8D9E">{{ $stat['label'] }}</div>
            </div>
        @endforeach

    </div>




    {{-- Controls --}}
    <div class="flex flex-wrap items-center justify-between gap-2 mb-4">

        <div class="flex flex-wrap gap-2">
            <form method="GET" action="{{ route('report.index') }}" id="filterForm" class="flex flex-wrap gap-2">
                <select name="position" class="text-sm px-3 py-2 rounded-lg outline-none"
                    style="background:#141B22; border:1px solid rgba(0,230,118,0.2); color:#E8F0F7"
                    onchange="this.form.submit()">
                    <option value="" {{ request('position') === null || request('position') === '' ? 'selected' : '' }}>Semua
                        Posisi</option>
                    <option value="Striker" {{ request('position') === 'Striker' ? 'selected' : '' }}>Striker</option>
                    <option value="Gelandang" {{ request('position') === 'Gelandang' ? 'selected' : '' }}>Gelandang</option>
                    <option value="Bek" {{ in_array(request('position'), ['Bek', 'Defender'], true) ? 'selected' : '' }}>Bek
                    </option>
                    <option value="Kiper" {{ request('position') === 'Kiper' ? 'selected' : '' }}>Kiper</option>
                </select>
                <input name="month" type="month" value="{{ request('month') }}"
                    class="text-sm px-3 py-2 rounded-lg outline-none"
                    style="background:#141B22; border:1px solid rgba(0,230,118,0.2); color:#E8F0F7"
                    onchange="this.form.submit()">
            </form>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('report.export.pdf') }}" target="_blank"
                class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition hover:-translate-y-px"
                style="background:rgba(255,68,68,0.15); color:#FF4444; border:1px solid rgba(255,68,68,0.3)">
                📄 Export Semua PDF
            </a>
            <a href="{{ route('report.export.excel') }}"
                class="px-4 py-2 rounded-lg text-sm font-semibold flex items-center gap-2 transition hover:-translate-y-px"
                style="background:rgba(0,230,118,0.12); color:#00E676; border:1px solid rgba(0,230,118,0.3)">
                📊 Export Excel
            </a>
        </div>
    </div>

    {{-- Report Table --}}
    <div class="rounded-xl p-5" style="background:#0E1419; border:1px solid rgba(0,230,118,0.15)">
        <h3 class="font-display text-lg tracking-widest mb-4 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full" style="background:#00E676"></span>
            Rekap Hasil per Atlet
        </h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" style="min-width:1450px">
                <thead>
                    <tr style="border-bottom:2px solid rgba(0,230,118,0.2)">
                        @foreach(['Atlet', 'YoYo', 'Speed', 'Agility', 'Strength', 'Power', 'Akurasi', 'Reaksi', 'Koordinasi', 'Fleksibilitas', 'Balance', 'Tes', 'Aksi'] as $h)
                            <th class="text-left py-3 px-3 text-xs uppercase tracking-widest font-mono2" style="color:#00E676">
                                {{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports ?? [] as $report)
                        <tr style="border-bottom:1px solid rgba(0,230,118,0.08)" class="hover:bg-[#141B22] transition-colors"
                            data-athlete-id="{{ $report->athlete_id }}" data-athlete-name="{{ $report->athlete_name }}">
                            <td class="py-3 px-3 font-semibold">{{ $report->athlete_name }}</td>
                            <td class="py-3 px-3">@if($report->yoyo_rating)<x-rating-badge
                            :rating="$report->yoyo_rating" />@else<span class="text-[#7A8D9E]">—</span>@endif</td>
                            <td class="py-3 px-3">@if($report->speed_rating)<x-rating-badge
                            :rating="$report->speed_rating" />@else<span class="text-[#7A8D9E]">—</span>@endif</td>
                            <td class="py-3 px-3">@if($report->agility_rating)<x-rating-badge
                            :rating="$report->agility_rating" />@else<span class="text-[#7A8D9E]">—</span>@endif</td>
                            <td class="py-3 px-3">@if($report->strength_rating)<x-rating-badge
                            :rating="$report->strength_rating" />@else<span class="text-[#7A8D9E]">—</span>@endif</td>
                            <td class="py-3 px-3">@if($report->power_rating)<x-rating-badge
                            :rating="$report->power_rating" />@else<span class="text-[#7A8D9E]">—</span>@endif</td>
                            <td class="py-3 px-3">@if($report->accuracy_rating)<x-rating-badge
                            :rating="$report->accuracy_rating" />@else<span class="text-[#7A8D9E]">—</span>@endif</td>
                            <td class="py-3 px-3">@if($report->reaction_rating)<x-rating-badge
                            :rating="$report->reaction_rating" />@else<span class="text-[#7A8D9E]">—</span>@endif</td>
                            <td class="py-3 px-3">@if($report->coordination_rating)<x-rating-badge
                            :rating="$report->coordination_rating" />@else<span class="text-[#7A8D9E]">—</span>@endif
                            </td>
                            <td class="py-3 px-3">@if($report->flexibility_rating)<x-rating-badge
                            :rating="$report->flexibility_rating" />@else<span class="text-[#7A8D9E]">—</span>@endif
                            </td>
                            <td class="py-3 px-3">@if($report->balance_rating)<x-rating-badge
                            :rating="$report->balance_rating" />@else<span class="text-[#7A8D9E]">—</span>@endif</td>
                            <td class="py-3 px-3 text-center">{{ $report->test_count }}/10</td>
                            <td class="py-3 px-3">
                                <a href="/report/athlete/{{ $report->athlete_id }}"
                                    class="px-3 py-1 rounded text-xs font-semibold transition hover:-translate-y-px inline-block"
                                    style="background:rgba(0,230,118,0.12); color:#00E676; border:1px solid rgba(0,230,118,0.2);"
                                    onclick="event.stopPropagation();">
                                    📄 Detail
                                </a>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="13" class="py-10 text-center text-[#7A8D9E]">
                                Belum ada data atlet dengan hasil tes. Silakan lakukan tes terlebih dahulu.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Make table rows clickable using data attributes
            const tableRows = document.querySelectorAll('tbody tr[data-athlete-id]');
            tableRows.forEach(row => {
                row.style.cursor = 'pointer';
                row.addEventListener('click', function (e) {
                    // Don't trigger if clicking on links/buttons
                    if (e.target.tagName === 'A' || e.target.closest('a')) {
                        e.stopPropagation();
                        return;
                    }

                    // Get athlete ID from data attribute
                    const athleteId = this.getAttribute('data-athlete-id');
                    if (athleteId) {
                        window.location.href = '/report/athlete/' + athleteId;
                    }
                });
            });

            // Filter report by position / month
            // Now handled by form submission with onchange
        });
    </script>
@endsection