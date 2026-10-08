@php
    $profile = $profileResult ?? $testResults->first();
    $palette = [
        'endurance' => ['accent' => '#17864B', 'pale' => '#EAF5EE', 'name' => 'ENDURANCE', 'subtitle' => 'YoYo Intermittent Recovery Test'],
        'speed' => ['accent' => '#D89A00', 'pale' => '#FFF7DF', 'name' => 'SPEED', 'subtitle' => '50 Meter Sprint'],
        'agility' => ['accent' => '#2789B8', 'pale' => '#EAF5FB', 'name' => 'AGILITY', 'subtitle' => 'Hexagonal & Illinois Test'],
        'strength' => ['accent' => '#C94F4F', 'pale' => '#FCEEEE', 'name' => 'STRENGTH', 'subtitle' => 'Push Up & Sit Up'],
        'power' => ['accent' => '#D67520', 'pale' => '#FFF1E5', 'name' => 'POWER', 'subtitle' => 'Standing Long Jump'],
        'accuracy' => ['accent' => '#247B70', 'pale' => '#E9F5F2', 'name' => 'AKURASI', 'subtitle' => 'Shooting Accuracy'],
        'reaction' => ['accent' => '#B64343', 'pale' => '#FBECEC', 'name' => 'REAKSI', 'subtitle' => 'Ruler Drop Test'],
        'coordination' => ['accent' => '#7059A6', 'pale' => '#F1EDFA', 'name' => 'KOORDINASI', 'subtitle' => 'Fukuda Step Test'],
        'flexibility' => ['accent' => '#BD741A', 'pale' => '#FBF2E6', 'name' => 'FLEKSIBILITAS', 'subtitle' => 'Ankle Flexibility Test'],
        'balance' => ['accent' => '#17864B', 'pale' => '#EAF5EE', 'name' => 'BALANCE', 'subtitle' => 'Y Balance Test'],
    ];
@endphp

<style>
    @page {
        margin: 22px 24px 35px;
    }

    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        color: #17231e;
        font-family: DejaVu Sans, sans-serif;
        font-size: 9px;
        line-height: 1.35;
    }

    .report-header {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 12px;
        background: #06271d;
        border: 1px solid #0d3e2c;
        border-bottom: 4px solid #00a85a;
        border-radius: 9px;
    }

    .report-header td {
        padding: 13px 15px;
        border: 0;
        vertical-align: middle;
        background: transparent;
    }

    .brand-title {
        color: #fff;
        font-size: 21px;
        font-weight: bold;
        letter-spacing: 1px;
    }

    .brand-subtitle {
        color: #46d98b;
        font-size: 9px;
        font-weight: bold;
        margin-top: 3px;
    }

    .report-caption {
        color: #fff;
        font-size: 12px;
        font-weight: bold;
        margin-top: 9px;
    }

    .report-meta {
        color: #d0e5da;
        font-size: 8px;
        text-align: right;
        line-height: 1.8;
    }

    .meta-strong {
        color: #fff;
        font-weight: bold;
    }

    .identity {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin-bottom: 11px;
        border: 1px solid #d4e4da;
        border-radius: 8px;
    }

    .identity-heading {
        padding: 8px 11px;
        color: #145d39;
        background: #f1f7f3;
        border-bottom: 1px solid #d4e4da;
        font-size: 11px;
        font-weight: bold;
    }

    .identity-grid {
        width: 100%;
        border-collapse: collapse;
    }

    .identity-grid td {
        width: 25%;
        padding: 7px 9px;
        border-right: 1px solid #e3ece6;
        border-bottom: 1px solid #e3ece6;
        vertical-align: top;
    }

    .identity-grid tr:last-child td {
        border-bottom: 0;
    }

    .identity-grid td:last-child {
        border-right: 0;
    }

    .info-label {
        color: #74847b;
        display: block;
        font-size: 7px;
        text-transform: uppercase;
        letter-spacing: .4px;
    }

    .info-value {
        color: #1b2d24;
        display: block;
        font-size: 9px;
        font-weight: bold;
        margin-top: 2px;
    }

    .summary {
        width: 100%;
        border-collapse: separate;
        border-spacing: 5px 0;
        margin: 0 -5px 11px;
    }

    .summary td {
        width: 33.33%;
        padding: 7px 9px;
        background: #f2f7f4;
        border: 1px solid #e0ebe4;
        border-radius: 5px;
        text-align: center;
    }

    .summary-value {
        color: #12643b;
        font-size: 13px;
        font-weight: bold;
    }

    .summary-label {
        color: #718078;
        display: block;
        font-size: 7px;
        margin-top: 2px;
        text-transform: uppercase;
    }

    .section-title {
        color: #174a32;
        font-size: 10px;
        font-weight: bold;
        margin: 12px 0 7px;
        text-transform: uppercase;
        letter-spacing: .7px;
    }

    .test-card {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        margin: 0 0 9px;
        page-break-inside: avoid;
        border: 1px solid #d7e4dc;
        border-radius: 7px;
    }

    .test-heading td {
        padding: 8px 10px;
        background: #f4f7f5;
        border-bottom: 1px solid #dce7e0;
        vertical-align: middle;
    }

    .test-accent {
        width: 5px;
        padding: 0 !important;
        border: 0 !important;
    }

    .test-name {
        color: #1b2c23;
        font-size: 10px;
        font-weight: bold;
    }

    .test-subtitle {
        color: #6f7f76;
        font-size: 8px;
    }

    .test-date {
        color: #66776e;
        font-size: 8px;
        text-align: right;
        white-space: nowrap;
    }

    .metric-row td {
        padding: 8px 5px 7px;
        border-right: 1px solid #e4ebe7;
        text-align: center;
        vertical-align: top;
    }

    .metric-row td:last-child {
        border-right: 0;
    }

    .metric-label {
        color: #65766c;
        display: block;
        font-size: 7px;
        text-transform: uppercase;
    }

    .metric-value {
        color: #182720;
        display: block;
        font-size: 12px;
        font-weight: bold;
        margin-top: 4px;
    }

    .metric-unit {
        color: #6e7d74;
        display: block;
        font-size: 7px;
        margin-top: 1px;
    }

    .rating-row td {
        padding: 0 9px 7px;
        text-align: center;
    }

    .rating-chip {
        display: inline-block;
        padding: 3px 9px;
        border-radius: 10px;
        background: #e8f4ec;
        color: #12643b;
        font-size: 8px;
        font-weight: bold;
        text-transform: uppercase;
    }

    .rating-chip.good {
        background: #e6f2fa;
        color: #23658e;
    }

    .rating-chip.average {
        background: #fff5d9;
        color: #8a6500;
    }

    .rating-chip.below,
    .rating-chip.poor {
        background: #fbe9e7;
        color: #a43c30;
    }

    .conclusion {
        padding: 7px 10px;
        border-top: 1px solid #e4ebe7;
        background: #fbfcfb;
        color: #34463b;
        font-size: 8px;
    }

    .conclusion strong {
        color: #597165;
    }

    .no-results {
        padding: 22px;
        border: 1px dashed #c9d8cf;
        border-radius: 7px;
        color: #68786f;
        text-align: center;
    }

    .footer {
        width: 100%;
        margin-top: 13px;
        padding-top: 7px;
        border-top: 1px solid #d7e2db;
        border-collapse: collapse;
        color: #748078;
        font-size: 7px;
    }

    .footer td {
        padding: 0;
        border: 0;
    }

    .footer td:last-child {
        text-align: right;
    }
</style>

<table class="report-header">
    <tr>
        <td>
            <div class="brand-title">FOOTSCREEN PRO</div>
            <div class="brand-subtitle">Sistem Screening Fisioterapi Sepak Bola</div>
            <div class="report-caption">Laporan Hasil Screening</div>
        </td>
        <td class="report-meta">
            <span class="meta-strong">Dicetak</span> {{ now()->format('d F Y, H:i') }}<br>
            <span class="meta-strong">Oleh</span> {{ $printedBy ?? (auth()->user()->name ?? 'Fisioterapis') }}<br>
            <span class="meta-strong">Jenis tes tercatat</span> {{ $testResults->count() }}
        </td>
    </tr>
</table>

<table class="identity">
    <tr>
        <td class="identity-heading" colspan="4">IDENTITAS ATLET</td>
    </tr>
    <tr>
        <td><span class="info-label">Nama</span><span class="info-value">{{ $profile->name ?? '-' }}</span></td>
        <td><span class="info-label">Jenis Kelamin</span><span class="info-value">{{ $profile->gender ?? '-' }}</span>
        </td>
        <td><span class="info-label">Usia</span><span
                class="info-value">{{ $profile->age ? $profile->age . ' tahun' : '-' }}</span></td>
        <td><span class="info-label">Tinggi / Berat</span><span
                class="info-value">{{ $profile->height ? $profile->height . ' cm' : '-' }} /
                {{ $profile->weight ? $profile->weight . ' kg' : '-' }}</span></td>
    </tr>
    <tr>
        <td><span class="info-label">Posisi</span><span class="info-value">{{ $profile->position ?? '-' }}</span></td>
        <td><span class="info-label">Nomor HP</span><span class="info-value">{{ $profile->phone ?? '-' }}</span></td>
        <td colspan="2"><span class="info-label">Rentang Tanggal Tes</span><span
                class="info-value">{{ $testResults->min('test_date') ? \Carbon\Carbon::parse($testResults->min('test_date'))->format('d/m/Y') : '-' }}
                –
                {{ $testResults->max('test_date') ? \Carbon\Carbon::parse($testResults->max('test_date'))->format('d/m/Y') : '-' }}</span>
        </td>
    </tr>
</table>

@if($testResults->isNotEmpty())
    <table class="summary">
        <tr>
            <td><span class="summary-value">{{ $testResults->count() }}</span><span class="summary-label">Total catatan
                    tes</span></td>
            <td><span class="summary-value">{{ $testResults->pluck('test_type')->unique()->count() }}/10</span><span
                    class="summary-label">Jenis tes terisi</span></td>
            <td><span class="summary-value">{{ $testResults->first()->test_date?->format('d M Y') ?? '-' }}</span><span
                    class="summary-label">Tes terbaru</span></td>
        </tr>
    </table>
@endif

<div class="section-title">Rincian Hasil Tes</div>

@forelse($testResults as $testResult)
    @php
        $testStyle = $palette[$testResult->test_type] ?? ['accent' => '#17864B', 'pale' => '#EAF5EE', 'name' => $testResult->test_type_label, 'subtitle' => 'Hasil pemeriksaan'];
        $metrics = $testResult->display_results;
        $ratings = $testResult->display_ratings;
        $ratingClass = strtolower((string) ($testResult->rating ?? ''));
    @endphp
    <table class="test-card">
        <tr class="test-heading">
            <td class="test-accent" style="background:{{ $testStyle['accent'] }}"></td>
            <td>
                <span class="test-name" style="color:{{ $testStyle['accent'] }}">{{ $testStyle['name'] }}</span>
                <span class="test-subtitle"> — {{ $testStyle['subtitle'] }}</span>
            </td>
            <td class="test-date">{{ $testResult->test_date?->format('d M Y, H:i') ?? '-' }}</td>
        </tr>
        @if(count($metrics))
            <tr class="metric-row">
                @foreach($metrics as $label => $value)
                    <td style="width:33.33%">
                        <span class="metric-label">{{ $label }}</span>
                        <span class="metric-value">{{ $value }}</span>
                    </td>
                @endforeach
                @if(count($metrics) < 3)
                    @for($i = count($metrics); $i < 3; $i++)
                    <td style="width:33.33%"></td>@endfor
                @endif
            </tr>
        @else
            <tr class="metric-row">
                <td colspan="3"><span class="metric-value">Nilai detail belum tersedia</span></td>
            </tr>
        @endif
        @if(count($ratings))
            <tr class="rating-row">
                <td colspan="{{ max(count($metrics), 3) }}">
                    @foreach($ratings as $label => $rating)
                        <span
                            class="rating-chip {{ $ratingClass }}">{{ $label !== 'Rating' ? $label . ': ' : '' }}{{ $rating }}</span>
                    @endforeach
                </td>
            </tr>
        @endif
        @if($testResult->conclusion)
            <tr>
                <td class="conclusion" colspan="{{ max(count($metrics), 3) }}"><strong>KESIMPULAN</strong> &nbsp;
                    {{ $testResult->conclusion }}</td>
            </tr>
        @endif
    </table>
@empty
    <div class="no-results">Belum ada hasil tes yang tercatat untuk atlet ini.</div>
@endforelse

<table class="footer">
    <tr>
        <td>FootScreen Pro — Sistem Screening Fisioterapi Sepak Bola</td>
        <td>Atlet {{ $pageNumber ?? 1 }} dari {{ $pageCount ?? 1 }} &nbsp; · &nbsp; Dibuat
            {{ now()->format('d/m/Y H:i') }}</td>
    </tr>
</table>