@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    <div class="dashboard-page {{ ($isAdmin || $isCoach) ? 'admin-dashboard' : '' }}">
        <section class="dashboard-welcome" aria-labelledby="dashboard-heading">
            <div class="dashboard-welcome-copy">
                <span class="dashboard-eyebrow"><span></span> RINGKASAN OPERASIONAL</span>
                <h1 id="dashboard-heading">{{ $isCoach ? 'Assessment Pelatih Sepak Bola' : 'Screening Fisioterapi Sepak Bola' }}</h1>
                <p>{{ $isCoach ? 'Pantau hasil assessment mental dan taktik atlet dalam satu tampilan.' : 'Pantau kondisi dan progres screening atlet dalam satu tampilan.' }}</p>
            </div>
            <div class="dashboard-welcome-mark" aria-hidden="true">⚽</div>
        </section>

        <section class="dashboard-overview {{ ($isAdmin || $isCoach) ? 'admin-overview' : '' }}" aria-label="Ringkasan sistem">
            <article class="overview-card overview-green">
                <span class="overview-label">{{ $isCoach ? 'Jenis assessment' : ($isAdmin ? 'Modul screening' : 'Jenis tes fisik') }}</span>
                <strong>{{ $typesOfTests }}</strong>
                <span class="overview-note">{{ $isCoach ? 'mental + taktik' : ($isAdmin ? '10 fisik + mental + taktik' : 'tersedia di sistem') }}</span>
            </article>
            <article class="overview-card overview-blue">
                <span class="overview-label">Atlet aktif</span>
                <strong>{{ $totalAthletes }}</strong>
                <span class="overview-note">terdaftar</span>
            </article>
            <article class="overview-card overview-yellow">
                <span class="overview-label">Tes hari ini</span>
                <strong>{{ $totalTestsToday }}</strong>
                <span class="overview-note">input hari ini</span>
            </article>
            <article class="overview-card overview-cyan">
                <span class="overview-label">Kelengkapan</span>
                <strong>{{ $completionRate }}<small>%</small></strong>
                <span class="overview-note">atlet dengan aktivitas</span>
            </article>
            @if($isAdmin || $isCoach)
                <article class="overview-card overview-pink">
                    <span class="overview-label">Mental hari ini</span>
                    <strong>{{ $mentalAssessmentsToday }}</strong>
                    <span class="overview-note">assessment tersimpan</span>
                </article>
                <article class="overview-card overview-purple">
                    <span class="overview-label">Taktik hari ini</span>
                    <strong>{{ $taktikAssessmentsToday }}</strong>
                    <span class="overview-note">assessment tersimpan</span>
                </article>
            @endif
        </section>

        <section class="dashboard-metrics {{ $isCoach ? 'coach-metrics' : ($isAdmin ? 'admin-metrics' : '') }}" aria-label="Ringkasan hasil tes">
            @unless($isCoach)
                <x-stat-card icon="🏃" value="{{ $avgYoyo }}" label="Rata-rata YoYo" delta="Real-time" color="green" />
                <x-stat-card icon="⚡" value="{{ $bestSpeed }}" label="Speed 50M terbaik" delta="Real-time" color="yellow" />
                <x-stat-card icon="🔄" value="{{ $avgAgility }}" label="Agility rata-rata" delta="Real-time" color="blue" />
                <x-stat-card icon="💪" value="{{ $maxPushup }}" label="Push-up terbanyak" delta="Real-time" color="red" />
            @endunless
            @if($isAdmin || $isCoach)
                <x-stat-card icon="🧠" value="{{ $avgMentalAssessment }}" label="Rata-rata mental" delta="Assessment"
                    color="blue" />
                <x-stat-card icon="🎯" value="{{ $avgTaktikAssessment }}" label="Rata-rata taktik" delta="Assessment"
                    color="orange" />
            @endif
        </section>

        @php
            $plotLeft = 42;
            $plotRight = 704;
            $plotTop = 20;
            $plotBottom = 170;
            $plotMax = max(1, (int) $testTrend->max('count'));
            $plotStep = ($plotRight - $plotLeft) / max($testTrend->count() - 1, 1);
            $trendCoordinates = $testTrend->values()->map(function ($day, $index) use ($plotLeft, $plotTop, $plotBottom, $plotMax, $plotStep) {
                return [
                    'x' => round($plotLeft + ($index * $plotStep), 1),
                    'y' => round($plotBottom - (($day['count'] / $plotMax) * ($plotBottom - $plotTop)), 1),
                    'day' => $day,
                ];
            });
            $trendLine = $trendCoordinates->map(fn($point) => $point['x'] . ',' . $point['y'])->implode(' ');
            $trendArea = $plotLeft . ',' . $plotBottom . ' ' . $trendLine . ' ' . $plotRight . ',' . $plotBottom;
            $typeMax = max(1, (int) $testTypeStats->max('count'));
            $trendSeries = [];
            if (!$isCoach) {
                $trendSeries[] = ['key' => 'physical', 'label' => 'Tes fisik', 'color' => '#00E676', 'dash' => '', 'markerOffset' => 0];
            }
            if ($isAdmin || $isCoach) {
                $trendSeries[] = ['key' => 'mental', 'label' => 'Mental', 'color' => '#F06292', 'dash' => '6 4', 'markerOffset' => -5];
                $trendSeries[] = ['key' => 'taktik', 'label' => 'Taktik', 'color' => '#B39DDB', 'dash' => '2 4', 'markerOffset' => 5];
            }
        @endphp

        <section class="dashboard-charts" aria-label="Grafik aktivitas screening">
            <article class="dashboard-chart-panel trend-chart-panel">
                <div class="dashboard-panel-heading chart-heading">
                    <div>
                        <span class="dashboard-eyebrow"><span></span> AKTIVITAS</span>
                        <h2>Tren tes harian</h2>
                        <p>{{ $isCoach ? 'Assessment mental dan taktik berdasarkan tanggal, 14 hari terakhir.' : ($isAdmin ? 'Tes fisik dan assessment berdasarkan tanggal, 14 hari terakhir.' : 'Jumlah tes berdasarkan tanggal pemeriksaan, 14 hari terakhir.') }}
                        </p>
                    </div>
                    <div class="chart-total"><strong>{{ $testTrend->sum('count') }}</strong><span>total tes</span></div>
                </div>
                <div class="chart-canvas trend-canvas">
                    <svg viewBox="0 0 736 220" role="img" aria-label="Grafik tren tes harian selama 14 hari">
                        @foreach([0, 0.33, 0.66, 1] as $gridLevel)
                            @php
                                $gridY = round($plotBottom - ($gridLevel * ($plotBottom - $plotTop)), 1);
                                $gridValue = (int) round($plotMax * $gridLevel);
                            @endphp
                            <line x1="{{ $plotLeft }}" y1="{{ $gridY }}" x2="{{ $plotRight }}" y2="{{ $gridY }}"
                                class="chart-grid-line" />
                            <text x="32" y="{{ $gridY + 3 }}" text-anchor="end" class="chart-axis-label">{{ $gridValue }}</text>
                        @endforeach
                        @if(!$isAdmin && !$isCoach)
                            <polygon points="{{ $trendArea }}" fill="url(#trendFill)" />
                        @endif
                        @foreach($trendSeries as $series)
                            @php
                                $seriesPoints = $testTrend->values()->map(function ($day, $index) use ($series, $plotLeft, $plotTop, $plotBottom, $plotMax, $plotStep) {
                                    $value = $day[$series['key']];
                                    $y = $plotBottom - (($value / $plotMax) * ($plotBottom - $plotTop));
                                    return [
                                        'x' => round($plotLeft + ($index * $plotStep), 1),
                                        'y' => round($y, 1),
                                        'value' => $value,
                                        'label' => $day['label'],
                                    ];
                                });
                                $seriesLine = $seriesPoints->map(fn($point) => $point['x'] . ',' . $point['y'])->implode(' ');
                            @endphp
                            <polyline points="{{ $seriesLine }}" fill="none" stroke="{{ $series['color'] }}"
                                stroke-width="{{ ($isAdmin || $isCoach) ? 2.7 : 3 }}" stroke-linecap="round" stroke-linejoin="round"
                                @if($series['dash']) stroke-dasharray="{{ $series['dash'] }}" @endif />
                            @foreach($seriesPoints as $seriesPoint)
                                @if($seriesPoint['value'] > 0)
                                    <circle cx="{{ $seriesPoint['x'] + $series['markerOffset'] }}" cy="{{ $seriesPoint['y'] }}"
                                        r="{{ $series['key'] === 'mental' ? 5 : 3.5 }}" fill="#0E1419" stroke="{{ $series['color'] }}"
                                        stroke-width="2">
                                        <title>{{ $seriesPoint['label'] }} · {{ $series['label'] }}: {{ $seriesPoint['value'] }}</title>
                                    </circle>
                                @endif
                            @endforeach
                        @endforeach
                        @foreach($trendCoordinates as $index => $point)
                            @if($index % 2 === 0 || $index === $testTrend->count() - 1)
                                <text x="{{ $point['x'] }}" y="197" text-anchor="middle"
                                    class="chart-axis-label">{{ $point['day']['label'] }}</text>
                            @endif
                        @endforeach
                        <defs>
                            <linearGradient id="trendFill" x1="0" x2="0" y1="0" y2="1">
                                <stop offset="0%" stop-color="#00E676" stop-opacity=".23" />
                                <stop offset="100%" stop-color="#00E676" stop-opacity="0" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div class="trend-legend" aria-label="Legenda grafik tren">
                    @foreach($trendSeries as $series)
                        <span><i class="trend-legend-{{ $series['key'] }}"
                                style="--legend-color:{{ $series['color'] }}"></i>{{ $series['label'] }}</span>
                    @endforeach
                </div>
                @if($testTrend->sum('count') === 0)
                    <p class="chart-empty-note">Belum ada tes pada 14 hari terakhir.</p>
                @endif
            </article>

            <article class="dashboard-chart-panel type-chart-panel">
                <div class="dashboard-panel-heading chart-heading">
                    <div>
                        <span class="dashboard-eyebrow"><span></span> DISTRIBUSI</span>
                        <h2>Jenis tes dilakukan</h2>
                        <p>Total catatan per kategori tes.</p>
                    </div>
                    <div class="chart-total"><strong>{{ $testTypeStats->sum('count') }}</strong><span>catatan</span></div>
                </div>
                <div class="type-chart-list">
                    @foreach($testTypeStats as $type)
                        <div class="type-chart-row">
                            <div class="type-chart-meta">
                                <span><i style="--type-color:{{ $type['color'] }}"></i>{{ $type['label'] }}</span>
                                <strong>{{ $type['count'] }}</strong>
                            </div>
                            <div class="type-chart-track">
                                <span
                                    style="width:{{ $type['count'] ? max(5, ($type['count'] / $typeMax) * 100) : 0 }}%; background:{{ $type['color'] }}"></span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </article>
        </section>

        <section class="dashboard-workspace">
            <article class="dashboard-panel athlete-panel">
                <div class="dashboard-panel-heading">
                    <div>
                        <span class="dashboard-eyebrow"><span></span> MONITORING</span>
                        <h2>Status tes atlet</h2>
                    </div>
                    <a href="{{ route('athletes.index') }}" class="dashboard-text-link">Semua atlet <span
                            aria-hidden="true">→</span></a>
                </div>

                <div class="dashboard-table-scroll">
                    <table class="dashboard-table monitoring-table">
                        <thead>
                            <tr>
                                <th scope="col" class="th-sticky">Atlet</th>
                                <th scope="col">Posisi</th>
                                @if($isAdmin || !$isCoach)
                                    @foreach($physicalTestTypes as $typeKey => $typeInfo)
                                        <th scope="col" class="th-test-type" style="--th-color:{{ $typeInfo['color'] }}">
                                            <span class="th-test-dot" style="background:{{ $typeInfo['color'] }}"></span>
                                            {{ $typeInfo['label'] }}
                                        </th>
                                    @endforeach
                                @endif
                                @if($isAdmin || $isCoach)
                                    <th scope="col" class="th-test-type" style="--th-color:#F06292">
                                        <span class="th-test-dot" style="background:#F06292"></span>
                                        Mental
                                    </th>
                                    <th scope="col" class="th-test-type" style="--th-color:#B39DDB">
                                        <span class="th-test-dot" style="background:#B39DDB"></span>
                                        Taktik
                                    </th>
                                @endif
                                <th scope="col">Progres</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAthletes as $athlete)
                                @php
                                    $progress = $athlete->dashboard_progress ?? 0;
                                    $status = $progress >= 100 ? 'Selesai' : ($progress > 30 ? 'Proses' : 'Pending');
                                    $statusColor = $progress >= 100 ? '#00E676' : ($progress > 30 ? '#FFD740' : '#90A0AE');
                                    $donePhy = $athlete->physical_test_types_done ?? [];
                                @endphp
                                <tr>
                                    <td class="td-sticky">
                                        <a class="athlete-name"
                                            href="{{ route('athletes.show', $athlete->id) }}">{{ $athlete->name }}</a>
                                    </td>
                                    <td class="muted-cell">{{ $athlete->position ?? 'Belum diatur' }}</td>
                                    @if($isAdmin || !$isCoach)
                                        @foreach($physicalTestTypes as $typeKey => $typeInfo)
                                            <td>
                                                @if(in_array($typeKey, $donePhy))
                                                    <span class="module-status module-complete" title="{{ $typeInfo['label'] }}: Sudah">✓</span>
                                                @else
                                                    <span class="module-status module-missing" title="{{ $typeInfo['label'] }}: Belum">—</span>
                                                @endif
                                            </td>
                                        @endforeach
                                    @endif
                                    @if($isAdmin || $isCoach)
                                        <td>
                                            <span
                                                class="module-status {{ $athlete->mental_assessment_count ? 'module-complete' : 'module-missing' }}"
                                                title="Mental: {{ $athlete->mental_assessment_count ? 'Ada' : 'Belum' }}">
                                                {{ $athlete->mental_assessment_count ? '✓' : '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="module-status {{ $athlete->taktik_assessment_count ? 'module-complete' : 'module-missing' }}"
                                                title="Taktik: {{ $athlete->taktik_assessment_count ? 'Ada' : 'Belum' }}">
                                                {{ $athlete->taktik_assessment_count ? '✓' : '—' }}
                                            </span>
                                        </td>
                                    @endif
                                    <td>
                                        <div class="progress-cell">
                                            <div class="progress-track" role="progressbar"
                                                aria-label="Progres screening {{ $athlete->name }}" aria-valuemin="0"
                                                aria-valuemax="100" aria-valuenow="{{ $progress }}">
                                                <span style="width:{{ $progress }}%; background:{{ $statusColor }}"></span>
                                            </div>
                                            <span>{{ $athlete->completed_module_count }}/{{ $athlete->module_count }} ·
                                                {{ number_format($progress, 0) }}%</span>
                                        </div>
                                    </td>
                                    <td><span class="status-label"
                                            style="--status-color:{{ $statusColor }}"><i></i>{{ $status }}</span></td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ ($isAdmin ? 14 : ($isCoach ? 5 : 14)) }}" class="empty-state">
                                        <strong>Belum ada data atlet</strong>
                                        <span>Tambahkan atlet untuk mulai memantau progres screening.</span>
                                        <a href="{{ route('athletes.index') }}">Buka data atlet <span
                                                aria-hidden="true">→</span></a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </article>

            <article class="dashboard-panel quick-panel">
                <div class="dashboard-panel-heading">
                    <div>
                        <span class="dashboard-eyebrow"><span></span> AKSES LANGSUNG</span>
                        <h2>{{ $isCoach ? 'Mulai assessment' : ($isAdmin ? 'Akses cepat' : 'Mulai tes') }}</h2>
                    </div>
                    @if($isCoach)
                        <a href="{{ route('reports.index') }}" class="dashboard-text-link">Laporan assessment <span
                                aria-hidden="true">→</span></a>
                    @else
                        <a href="{{ route('tests.index') }}" class="dashboard-text-link">Semua tes <span
                                aria-hidden="true">→</span></a>
                    @endif
                </div>
                <div class="quick-test-grid">
                    @unless($isCoach)
                        @foreach([
                                ['forms.endurance', '🏃', 'Endurance', 'YoYo test', '#00E676'],
                                ['forms.speed', '⚡', 'Speed', 'Sprint 50 meter', '#FFD740'],
                                ['forms.agility', '🔄', 'Agility', 'Hexagonal & Illinois', '#40C4FF'],
                                ['forms.strength', '💪', 'Strength', 'Push-up & sit-up', '#FF6E6E'],
                                ['forms.power', '🦵', 'Power', 'Standing long jump', '#FFAB40'],
                                ['forms.balance', '⚖️', 'Balance', 'Y Balance Test', '#B39DDB'],
                            ] as [$route, $icon, $name, $description, $color])
                            <a href="{{ route($route) }}" class="quick-test" style="--test-accent:{{ $color }}">
                                <span class="quick-test-icon" aria-hidden="true">{{ $icon }}</span>
                                <span class="quick-test-copy">
                                    <strong>{{ $name }}</strong>
                                    <small>{{ $description }}</small>
                                </span>
                                <span class="quick-test-arrow" aria-hidden="true">↗</span>
                            </a>
                        @endforeach
                    @endunless
                    @if($isAdmin || $isCoach)
                        <a href="{{ route('mental.create') }}" class="quick-test" style="--test-accent:#F06292">
                            <span class="quick-test-icon" aria-hidden="true">🧠</span>
                            <span class="quick-test-copy"><strong>Mental</strong><small>Assessment atlet</small></span>
                            <span class="quick-test-arrow" aria-hidden="true">↗</span>
                        </a>
                        <a href="{{ route('taktik.create') }}" class="quick-test" style="--test-accent:#B39DDB">
                            <span class="quick-test-icon" aria-hidden="true">🎯</span>
                            <span class="quick-test-copy"><strong>Taktik</strong><small>Assessment atlet</small></span>
                            <span class="quick-test-arrow" aria-hidden="true">↗</span>
                        </a>
                    @endif
                </div>
            </article>
        </section>
    </div>
@endsection

@push('styles')
    <style>
        .dashboard-page {
            --dash-border: rgba(151, 177, 164, .13);
            --dash-muted: #9AA9B5;
            --dash-surface: #0E1419;
            display: grid;
            gap: 20px;
            max-width: 1600px;
            margin: 0 auto;
        }

        .dashboard-welcome {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            min-height: 142px;
            overflow: hidden;
            padding: 25px 30px;
            border: 1px solid rgba(0, 230, 118, .18);
            border-radius: 14px;
            background: linear-gradient(112deg, #10251C 0%, #0D1C19 58%, #101820 100%);
        }

        .dashboard-welcome-copy {
            position: relative;
            z-index: 1;
            max-width: 680px;
        }

        .dashboard-eyebrow {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #8FA399;
            font: 700 10px/1.4 'Space Mono', monospace;
            letter-spacing: .13em;
        }

        .dashboard-eyebrow>span {
            width: 7px;
            height: 7px;
            flex: 0 0 7px;
            border-radius: 50%;
            background: #00E676;
            box-shadow: 0 0 12px rgba(0, 230, 118, .35);
        }

        .dashboard-welcome h1 {
            margin: 11px 0 5px;
            color: #F4F8F6;
            font: 400 31px/1.08 'Bebas Neue', sans-serif;
            letter-spacing: .035em;
        }

        .dashboard-welcome p {
            margin: 0;
            color: #A4B2BB;
            font-size: 13px;
            line-height: 1.55;
        }

        .dashboard-welcome-mark {
            position: absolute;
            right: 26px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(111, 173, 144, .1);
            font-size: 96px;
            line-height: 1;
        }

        .dashboard-overview {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .overview-card {
            position: relative;
            min-height: 105px;
            padding: 15px 18px 13px;
            overflow: hidden;
            border: 1px solid var(--dash-border);
            border-radius: 11px;
            background: var(--dash-surface);
        }

        .overview-card::before {
            position: absolute;
            inset: 0 auto 0 0;
            width: 3px;
            background: var(--metric-color);
            content: '';
        }

        .overview-label {
            display: block;
            color: #9AA9B5;
            font-size: 11px;
            font-weight: 500;
        }

        .overview-card strong {
            display: block;
            margin-top: 4px;
            color: var(--metric-color);
            font: 400 30px/1 'Bebas Neue', sans-serif;
        }

        .overview-card strong small {
            margin-left: 2px;
            font-size: 19px;
        }

        .overview-note {
            display: block;
            margin-top: 3px;
            color: #74838E;
            font-size: 10px;
        }

        .overview-green {
            --metric-color: #00E676;
        }

        .overview-blue {
            --metric-color: #6DB8E8;
        }

        .overview-yellow {
            --metric-color: #FFD740;
        }

        .overview-cyan {
            --metric-color: #42D5C5;
        }

        .overview-pink {
            --metric-color: #F06292;
        }

        .overview-purple {
            --metric-color: #B39DDB;
        }

        .admin-overview {
            grid-template-columns: repeat(6, minmax(0, 1fr));
        }

        .dashboard-metrics {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
        }

        .admin-metrics {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }

        .coach-metrics {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .dashboard-metrics>div {
            min-width: 0;
            padding: 16px 18px;
            border-color: var(--dash-border) !important;
            border-radius: 11px !important;
            transition: border-color .2s, transform .2s;
        }

        .dashboard-metrics>div:hover {
            transform: translateY(-2px);
            border-color: rgba(0, 230, 118, .35) !important;
        }

        .dashboard-metrics>div>div:first-child {
            margin-bottom: 11px !important;
        }

        .dashboard-metrics>div>div:first-child>div {
            width: 36px !important;
            height: 36px !important;
            border-radius: 9px !important;
            font-size: 17px !important;
        }

        .dashboard-metrics>div>div:first-child>span {
            color: #82938A !important;
            font-size: 10px !important;
        }

        .dashboard-metrics>div>div:nth-child(2) {
            color: #F3F7F5;
            font-size: 30px !important;
            line-height: 1.05 !important;
        }

        .dashboard-metrics>div>div:nth-child(3) {
            margin-top: 5px !important;
            color: #A4B2BB !important;
            font-size: 11px !important;
        }

        .dashboard-charts {
            display: grid;
            grid-template-columns: minmax(0, 1.3fr) minmax(310px, .7fr);
            gap: 14px;
        }

        .dashboard-chart-panel {
            min-width: 0;
            padding: 19px;
            border: 1px solid var(--dash-border);
            border-radius: 12px;
            background: var(--dash-surface);
        }

        .chart-heading {
            align-items: flex-start;
            margin-bottom: 8px;
        }

        .chart-heading p {
            margin: 5px 0 0;
            color: #82919B;
            font-size: 10px;
            line-height: 1.4;
        }

        .chart-total {
            display: flex;
            align-items: baseline;
            gap: 6px;
            flex: 0 0 auto;
            padding: 6px 9px;
            border: 1px solid rgba(0, 230, 118, .14);
            border-radius: 7px;
            background: rgba(0, 230, 118, .045);
        }

        .chart-total strong {
            color: #00E676;
            font: 400 20px/1 'Bebas Neue', sans-serif;
        }

        .chart-total span {
            color: #82938A;
            font-size: 9px;
        }

        .chart-canvas {
            width: 100%;
        }

        .trend-canvas svg {
            display: block;
            width: 100%;
            height: auto;
            overflow: visible;
        }

        .chart-grid-line {
            stroke: rgba(151, 177, 164, .12);
            stroke-width: 1;
            stroke-dasharray: 3 5;
        }

        .chart-axis-label {
            fill: #71817A;
            font: 9px 'DM Sans', sans-serif;
        }

        .chart-empty-note {
            margin: -2px 0 0;
            color: #71817A;
            font-size: 10px;
            text-align: center;
        }

        .trend-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            padding: 0 4px 2px 42px;
        }

        .trend-legend span {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #9AA9B5;
            font-size: 9px;
        }

        .trend-legend i {
            width: 15px;
            height: 0;
            border-top: 3px solid var(--legend-color);
            border-radius: 4px;
        }

        .trend-legend .trend-legend-mental {
            border-top-style: dashed;
        }

        .trend-legend .trend-legend-taktik {
            border-top-style: dotted;
        }

        .type-chart-list {
            display: grid;
            gap: 10px;
            padding-top: 4px;
        }

        .type-chart-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 4px;
            color: #ABB8B1;
            font-size: 10px;
        }

        .type-chart-meta>span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .type-chart-meta i {
            display: inline-block;
            width: 7px;
            height: 7px;
            flex: 0 0 7px;
            border-radius: 50%;
            background: var(--type-color);
        }

        .type-chart-meta strong {
            color: #E7EFEB;
            font: 700 10px 'Space Mono', monospace;
        }

        .type-chart-track {
            height: 5px;
            overflow: hidden;
            border-radius: 99px;
            background: #202A30;
        }

        .type-chart-track span {
            display: block;
            height: 100%;
            min-width: 0;
            border-radius: inherit;
        }

        .dashboard-workspace {
            display: grid;
            grid-template-columns: minmax(0, 1.08fr) minmax(0, .92fr);
            gap: 14px;
            align-items: stretch;
        }

        .dashboard-panel {
            min-width: 0;
            padding: 19px;
            border: 1px solid var(--dash-border);
            border-radius: 12px;
            background: var(--dash-surface);
        }

        .dashboard-panel-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 17px;
        }

        .dashboard-panel-heading h2 {
            margin: 5px 0 0;
            color: #F1F5F3;
            font: 400 20px/1.1 'Bebas Neue', sans-serif;
            letter-spacing: .045em;
        }

        .dashboard-text-link {
            flex: 0 0 auto;
            color: #86CFA9;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
        }

        .dashboard-text-link:hover {
            color: #00E676;
        }

        .dashboard-table-scroll {
            overflow-x: auto;
        }

        .dashboard-table {
            width: 100%;
            min-width: 510px;
            border-collapse: collapse;
            text-align: left;
        }

        .dashboard-table th {
            padding: 10px 9px;
            border-bottom: 1px solid rgba(0, 230, 118, .22);
            color: #8FA399;
            font: 700 9px/1.3 'Space Mono', monospace;
            letter-spacing: .08em;
            text-transform: uppercase;
        }

        .dashboard-table td {
            padding: 12px 9px;
            border-bottom: 1px solid rgba(151, 177, 164, .09);
            color: #E8F0F7;
            font-size: 12px;
            white-space: nowrap;
        }

        .dashboard-table tbody tr:last-child td {
            border-bottom: 0;
        }

        .dashboard-table tbody tr:hover td {
            background: rgba(255, 255, 255, .018);
        }

        .athlete-name {
            color: #EAF1ED;
            font-weight: 600;
            text-decoration: none;
        }

        .athlete-name:hover {
            color: #00E676;
        }

        .muted-cell {
            color: #9AA9B5 !important;
        }

        .progress-cell {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #AAB7C0;
            font-size: 10px;
        }

        .progress-track {
            width: 74px;
            height: 6px;
            flex: 0 0 74px;
            overflow: hidden;
            border-radius: 99px;
            background: #202B32;
        }

        .progress-track span {
            display: block;
            height: 100%;
            border-radius: inherit;
        }

        .status-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #CAD4D0;
            font-size: 10px;
        }

        .status-label i {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--status-color);
        }

        .module-status {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 48px;
            padding: 4px 7px;
            border-radius: 999px;
            font-size: 9px;
            font-weight: 600;
        }

        .module-complete {
            color: #75E3AB;
            background: rgba(0, 230, 118, .1);
        }

        .module-missing {
            color: #AAB5BD;
            background: rgba(144, 160, 174, .1);
        }

        .admin-dashboard .dashboard-table {
            min-width: 650px;
        }

        /* === MONITORING TABLE (expanded with all test types) === */
        .monitoring-table {
            min-width: 1100px !important;
        }

        .monitoring-table th,
        .monitoring-table td {
            text-align: center;
            padding: 10px 6px;
        }

        .monitoring-table th:first-child,
        .monitoring-table td:first-child {
            text-align: left;
            padding-left: 12px;
        }

        .monitoring-table th:nth-child(2),
        .monitoring-table td:nth-child(2) {
            text-align: left;
        }

        .th-test-type {
            position: relative;
            font-size: 7.5px !important;
            letter-spacing: .04em !important;
            white-space: nowrap;
            padding: 8px 4px !important;
            min-width: 62px;
        }

        .th-test-dot {
            display: inline-block;
            width: 5px;
            height: 5px;
            border-radius: 50%;
            margin-right: 3px;
            vertical-align: middle;
            box-shadow: 0 0 6px rgba(255,255,255,.15);
        }

        .monitoring-table .module-status {
            min-width: 32px;
            padding: 3px 5px;
            font-size: 10px;
        }

        .monitoring-table .module-complete {
            color: #75E3AB;
            background: rgba(0, 230, 118, .12);
        }

        .monitoring-table .module-missing {
            color: #6b7880;
            background: rgba(144, 160, 174, .08);
        }

        .th-sticky,
        .td-sticky {
            position: sticky;
            left: 0;
            z-index: 2;
            background: #0E1419;
        }

        .th-sticky {
            z-index: 3;
        }

        .td-sticky::after,
        .th-sticky::after {
            content: '';
            position: absolute;
            top: 0;
            right: -8px;
            bottom: 0;
            width: 8px;
            background: linear-gradient(90deg, rgba(14,20,25,.6) 0%, transparent 100%);
            pointer-events: none;
        }

        .dashboard-table-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(0,230,118,.25) rgba(14,20,25,.4);
        }

        .dashboard-table-scroll::-webkit-scrollbar {
            height: 6px;
        }

        .dashboard-table-scroll::-webkit-scrollbar-track {
            background: rgba(14,20,25,.4);
            border-radius: 3px;
        }

        .dashboard-table-scroll::-webkit-scrollbar-thumb {
            background: rgba(0,230,118,.25);
            border-radius: 3px;
        }

        .dashboard-table-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(0,230,118,.4);
        }

        /* Wider athlete panel for admin to accommodate all columns */
        .admin-dashboard .dashboard-workspace {
            grid-template-columns: 1fr;
        }

        .admin-dashboard .athlete-panel {
            order: 1;
        }

        .admin-dashboard .quick-panel {
            order: 2;
        }

        .empty-state {
            padding: 30px 12px !important;
            text-align: center;
            white-space: normal !important;
        }

        .empty-state strong,
        .empty-state span,
        .empty-state a {
            display: block;
        }

        .empty-state strong {
            color: #E8F0F7;
            font-size: 13px;
        }

        .empty-state span {
            margin: 5px 0 10px;
            color: #9AA9B5;
            font-size: 11px;
        }

        .empty-state a {
            color: #00E676;
            font-size: 11px;
            font-weight: 600;
            text-decoration: none;
        }

        .quick-test-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 9px;
        }

        .quick-test {
            display: flex;
            align-items: center;
            gap: 10px;
            min-width: 0;
            min-height: 62px;
            padding: 10px 11px;
            border: 1px solid rgba(151, 177, 164, .12);
            border-radius: 9px;
            background: #121A20;
            text-decoration: none;
            transition: background .18s, border-color .18s, transform .18s;
        }

        .quick-test:hover {
            transform: translateY(-1px);
            border-color: color-mix(in srgb, var(--test-accent) 42%, transparent);
            background: #172129;
        }

        .quick-test-icon {
            display: grid;
            width: 34px;
            height: 34px;
            flex: 0 0 34px;
            place-items: center;
            border-radius: 9px;
            background: color-mix(in srgb, var(--test-accent) 14%, transparent);
            font-size: 17px;
        }

        .quick-test-copy {
            display: block;
            min-width: 0;
        }

        .quick-test-copy strong {
            display: block;
            overflow: hidden;
            color: var(--test-accent);
            font: 700 9px/1.35 'Space Mono', monospace;
            letter-spacing: .035em;
            text-overflow: ellipsis;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .quick-test-copy small {
            display: block;
            overflow: hidden;
            margin-top: 3px;
            color: #A4B2BB;
            font-size: 10px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .quick-test-arrow {
            margin-left: auto;
            color: #60716A;
            font-size: 14px;
        }

        .quick-test:hover .quick-test-arrow {
            color: var(--test-accent);
        }

        @media (max-width: 1100px) {
            .dashboard-charts {
                grid-template-columns: 1fr;
            }

            .admin-overview {
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .dashboard-workspace {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 767px) {
            .dashboard-page {
                gap: 14px;
            }

            .dashboard-welcome {
                min-height: 132px;
                padding: 20px;
            }

            .dashboard-welcome h1 {
                max-width: 75%;
                font-size: 26px;
                line-height: 1.1;
            }

            .dashboard-welcome p {
                max-width: 78%;
                font-size: 12px;
            }

            .dashboard-welcome-mark {
                right: 12px;
                font-size: 66px;
            }

            .dashboard-overview {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 9px;
            }

            .admin-overview {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .overview-card {
                min-height: 93px;
                padding: 12px 14px;
            }

            .overview-card strong {
                font-size: 27px;
            }

            .dashboard-metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 9px;
            }

            .admin-metrics,
            .coach-metrics {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .dashboard-metrics>div {
                padding: 13px !important;
            }

            .dashboard-charts {
                gap: 10px;
            }

            .dashboard-chart-panel {
                padding: 15px 13px;
            }

            .chart-heading {
                align-items: flex-start;
            }

            .chart-heading p {
                max-width: 230px;
            }

            .chart-total {
                gap: 4px;
                padding: 5px 7px;
            }

            .chart-axis-label {
                font-size: 8px;
            }

            .type-chart-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px 14px;
            }

            .dashboard-panel {
                padding: 15px 13px;
            }

            .dashboard-panel-heading {
                align-items: flex-end;
            }

            .dashboard-table {
                min-width: 500px;
            }

            .quick-test-grid {
                gap: 7px;
            }

            .quick-test {
                gap: 7px;
                padding: 9px 8px;
            }

            .quick-test-icon {
                width: 30px;
                height: 30px;
                flex-basis: 30px;
                font-size: 15px;
            }

            .quick-test-copy small {
                font-size: 9px;
            }
        }
    </style>
@endpush