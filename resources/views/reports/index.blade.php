@extends('layouts.app')
@section('title', 'Laporan Assessment')
@section('page-title', 'Laporan Assessment')

@section('content')

    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Laporan Assessment Mental & Taktik</h1>
        <div class="flex gap-3">
            <a href="{{ route('reports.export.pdf') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all"
                style="background:#00E676; color:#0E1419">
                📄 Export PDF
            </a>
            <a href="{{ route('reports.export.excel') }}" class="px-4 py-2 rounded-lg text-sm font-semibold transition-all"
                style="background:#FFB700; color:#0E1419">
                📊 Export CSV
            </a>
        </div>
    </div>

    {{-- Mental Assessments --}}
    @if ($mentalAssessments->count())
        <div class="panel-card mb-6">
            <h2 class="panel-title mb-4">Mental Assessments</h2>
            <div class="overflow-x-auto rounded-xl" style="border: 1px solid rgba(0,230,118,0.15)">
                <table class="w-full text-sm">
                    <thead style="background:rgba(0,230,118,0.05); border-bottom: 1px solid rgba(0,230,118,0.15)">
                        <tr>
                            <th class="px-4 py-3 text-left">Atlet</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-center">Rata-rata Skor</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($mentalAssessments as $assessment)
                            <tr style="border-bottom: 1px solid rgba(0,230,118,0.1)">
                                <td class="px-4 py-3">
                                    <div class="font-semibold">{{ $assessment->athlete->name }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $assessment->assessment_date->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="score-pill">{{ $assessment->average_score }}/5.0</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                        style="background:rgba(0,230,118,0.1); color:#00E676">
                                        Selesai
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    {{-- Taktik Assessments --}}
    @if ($taktikAssessments->count())
        <div class="panel-card mb-6">
            <h2 class="panel-title mb-4">Taktik Assessments</h2>
            <div class="overflow-x-auto rounded-xl" style="border: 1px solid rgba(0,230,118,0.15)">
                <table class="w-full text-sm">
                    <thead style="background:rgba(0,230,118,0.05); border-bottom: 1px solid rgba(0,230,118,0.15)">
                        <tr>
                            <th class="px-4 py-3 text-left">Atlet</th>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-center">Rata-rata Skor</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($taktikAssessments as $assessment)
                            <tr style="border-bottom: 1px solid rgba(0,230,118,0.1)">
                                <td class="px-4 py-3">
                                    <div class="font-semibold">{{ $assessment->athlete->name }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $assessment->assessment_date->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="score-pill">{{ $assessment->average_score }}/5.0</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-3 py-1 rounded-full text-xs font-semibold"
                                        style="background:rgba(0,230,118,0.1); color:#00E676">
                                        Selesai
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @if (!$mentalAssessments->count() && !$taktikAssessments->count())
        <div class="text-center py-12 rounded-xl"
            style="background:rgba(0,230,118,0.04); border:1px solid rgba(0,230,118,0.15)">
            <p class="text-sm mb-4" style="color:#7A8D9E">Belum ada assessment yang dibuat</p>
            <div class="flex gap-3 justify-center">
                <a href="{{ route('mental.create') }}"
                    class="inline-block px-4 py-2 rounded-lg text-sm font-semibold transition-all"
                    style="background:#00E676; color:#0E1419">
                    Buat Mental Assessment
                </a>
                <a href="{{ route('taktik.create') }}"
                    class="inline-block px-4 py-2 rounded-lg text-sm font-semibold transition-all"
                    style="background:#FFB700; color:#0E1419">
                    Buat Taktik Assessment
                </a>
            </div>
        </div>
    @endif

@endsection