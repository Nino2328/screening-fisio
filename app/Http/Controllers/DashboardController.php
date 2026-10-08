<?php
// ============================================================
// app/Http/Controllers/DashboardController.php
// ============================================================
namespace App\Http\Controllers;

use App\Models\Athlete;
use App\Models\MentalAssessment;
use App\Models\TaktikAssessment;
use App\Models\TestResult;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $isAdmin = auth()->user()->role === 'admin';
        $isCoach = auth()->user()->role === 'pelatih';
        $includePhysical = !$isCoach;
        $includeCoachAssessments = $isAdmin || $isCoach;

        // Total Atlet Aktif - REAL-TIME
        $totalAthletes = Athlete::count();

        $physicalTestTypes = [
            'endurance' => ['label' => 'Endurance', 'color' => '#00E676'],
            'speed' => ['label' => 'Speed', 'color' => '#FFD740'],
            'agility' => ['label' => 'Agility', 'color' => '#40C4FF'],
            'strength' => ['label' => 'Strength', 'color' => '#FF6E6E'],
            'power' => ['label' => 'Power', 'color' => '#FFAB40'],
            'accuracy' => ['label' => 'Akurasi', 'color' => '#26C6DA'],
            'reaction' => ['label' => 'Reaksi', 'color' => '#F06292'],
            'coordination' => ['label' => 'Koordinasi', 'color' => '#B39DDB'],
            'flexibility' => ['label' => 'Fleksibilitas', 'color' => '#AED581'],
            'balance' => ['label' => 'Balance', 'color' => '#FF8A65'],
        ];
        $typesOfTests = ($includePhysical ? count($physicalTestTypes) : 0) + ($includeCoachAssessments ? 2 : 0);

        $mentalAssessments = $includeCoachAssessments ? MentalAssessment::all() : collect();
        $taktikAssessments = $includeCoachAssessments ? TaktikAssessment::all() : collect();

        $totalTestsToday = 0;
        $mentalAssessmentsToday = 0;
        $taktikAssessmentsToday = 0;
        if ($includePhysical) {
            $totalTestsToday += TestResult::whereDate('created_at', Carbon::today())->count();
        }
        if ($includeCoachAssessments) {
            $mentalAssessmentsToday = MentalAssessment::whereDate('created_at', Carbon::today())->count();
            $taktikAssessmentsToday = TaktikAssessment::whereDate('created_at', Carbon::today())->count();
            $totalTestsToday += $mentalAssessmentsToday + $taktikAssessmentsToday;
        }

        $allAthletes = Athlete::orderByDesc('updated_at')->get(['id', 'name', 'position', 'updated_at']);
        $athleteIds = $allAthletes->pluck('id');
        $athleteNames = $allAthletes->pluck('name')->map(fn($name) => mb_strtolower(trim($name)));

        $allPhysicalResults = $includePhysical
            ? TestResult::query()
                ->whereIn('athlete_id', $athleteIds)
                ->orWhere(function ($query) use ($athleteNames) {
                    $query->whereNull('athlete_id')->whereIn('name', $athleteNames);
                })
                ->get(['athlete_id', 'name', 'test_type'])
            : collect();
        $physicalById = $allPhysicalResults->whereNotNull('athlete_id')->groupBy('athlete_id');
        $physicalByName = $allPhysicalResults->whereNull('athlete_id')->groupBy(fn($result) => mb_strtolower(trim($result->name)));
        $mentalById = $mentalAssessments->whereNotNull('athlete_id')->groupBy('athlete_id');
        $mentalByName = $mentalAssessments->whereNull('athlete_id')->groupBy(fn($assessment) => mb_strtolower(trim($assessment->name)));
        $taktikById = $taktikAssessments->whereNotNull('athlete_id')->groupBy('athlete_id');
        $taktikByName = $taktikAssessments->whereNull('athlete_id')->groupBy(fn($assessment) => mb_strtolower(trim($assessment->name)));

        $athleteProgress = $allAthletes->map(function ($athlete) use ($physicalById, $physicalByName, $mentalById, $mentalByName, $taktikById, $taktikByName, $isAdmin, $isCoach) {
            $nameKey = mb_strtolower(trim($athlete->name));
            $physicalResults = collect($physicalById->get($athlete->id, []))
                ->concat($physicalByName->get($nameKey, collect()));
            $physicalTypesCollection = $physicalResults->pluck('test_type')->map(fn($type) => mb_strtolower($type))->unique();
            $physicalTypes = $physicalTypesCollection->count();
            $mentalCount = collect($mentalById->get($athlete->id, []))->concat($mentalByName->get($nameKey, collect()))->count();
            $taktikCount = collect($taktikById->get($athlete->id, []))->concat($taktikByName->get($nameKey, collect()))->count();
            if ($isCoach) {
                $completed = ($mentalCount > 0 ? 1 : 0) + ($taktikCount > 0 ? 1 : 0);
                $moduleCount = 2;
            } else {
                $completed = $physicalTypes + ($isAdmin && $mentalCount > 0 ? 1 : 0) + ($isAdmin && $taktikCount > 0 ? 1 : 0);
                $moduleCount = $isAdmin ? 12 : 10;
            }

            $athlete->physical_test_count = $physicalTypes;
            $athlete->physical_test_types_done = $physicalTypesCollection->values()->all();
            $athlete->mental_assessment_count = $mentalCount;
            $athlete->taktik_assessment_count = $taktikCount;
            $athlete->completed_module_count = $completed;
            $athlete->dashboard_progress = min(100, ($completed / $moduleCount) * 100);
            $athlete->module_count = $moduleCount;

            return $athlete;
        });

        $athletesWithTests = $athleteProgress->where('completed_module_count', '>', 0)->count();
        $completionRate = $totalAthletes > 0 ? round(($athletesWithTests / $totalAthletes) * 100) : 0;
        $recentAthletes = $athleteProgress->take(5)->values();

        // Stats nilai tes - menggunakan field yang sesuai per test type
        $avgYoyo = '—';
        $bestSpeed = '—';
        $avgAgility = '—';
        $maxPushup = '—';
        if ($includePhysical) {
            $avgYoyo = TestResult::whereRaw('LOWER(test_type) = ?', ['endurance'])
                ->whereNotNull('distance')
                ->avg('distance');
            $avgYoyo = $avgYoyo ? number_format($avgYoyo, 0) . 'm' : '—';

            $bestSpeed = TestResult::whereRaw('LOWER(test_type) = ?', ['speed'])
                ->whereNotNull('time_seconds')
                ->min('time_seconds');
            $bestSpeed = $bestSpeed ? number_format($bestSpeed, 2) . 's' : '—';

            $avgAgility = TestResult::whereRaw('LOWER(test_type) = ?', ['agility'])
                ->whereNotNull('hex_avg')
                ->avg('hex_avg');
            $avgAgility = $avgAgility ? number_format($avgAgility, 2) . 's' : '—';

            $maxPushup = TestResult::whereRaw('LOWER(test_type) = ?', ['strength'])
                ->whereNotNull('pushup_count')
                ->max('pushup_count');
            $maxPushup = $maxPushup ? $maxPushup : '—';
        }

        $trendStart = Carbon::today()->subDays(13);
        $dailyTestCounts = $includePhysical
            ? TestResult::query()
                ->whereDate('test_date', '>=', $trendStart)
                ->whereDate('test_date', '<=', Carbon::today())
                ->selectRaw('DATE(test_date) as test_day, COUNT(*) as total')
                ->groupBy('test_day')
                ->pluck('total', 'test_day')
            : collect();

        $dailyMentalCounts = $includeCoachAssessments
            ? MentalAssessment::query()
                ->whereDate('assessment_date', '>=', $trendStart)
                ->whereDate('assessment_date', '<=', Carbon::today())
                ->selectRaw('DATE(assessment_date) as test_day, COUNT(*) as total')
                ->groupBy('test_day')
                ->pluck('total', 'test_day')
            : collect();
        $dailyTaktikCounts = $includeCoachAssessments
            ? TaktikAssessment::query()
                ->whereDate('assessment_date', '>=', $trendStart)
                ->whereDate('assessment_date', '<=', Carbon::today())
                ->selectRaw('DATE(assessment_date) as test_day, COUNT(*) as total')
                ->groupBy('test_day')
                ->pluck('total', 'test_day')
            : collect();

        $testTrend = collect(range(0, 13))->map(function ($offset) use ($trendStart, $dailyTestCounts, $dailyMentalCounts, $dailyTaktikCounts, $includePhysical, $includeCoachAssessments) {
            $date = $trendStart->copy()->addDays($offset);
            $dateKey = $date->toDateString();
            $physicalCount = $includePhysical ? (int) $dailyTestCounts->get($dateKey, 0) : 0;
            $mentalCount = $includeCoachAssessments ? (int) $dailyMentalCounts->get($dateKey, 0) : 0;
            $taktikCount = $includeCoachAssessments ? (int) $dailyTaktikCounts->get($dateKey, 0) : 0;

            return [
                'date' => $dateKey,
                'label' => $date->format('d M'),
                'weekday' => $date->translatedFormat('D'),
                'physical' => $physicalCount,
                'mental' => $mentalCount,
                'taktik' => $taktikCount,
                'count' => $physicalCount + $mentalCount + $taktikCount,
            ];
        });

        $testTypeStats = collect();
        if ($includePhysical) {
            $physicalTypeCounts = TestResult::query()
                ->selectRaw('LOWER(test_type) as type_key, COUNT(*) as total')
                ->groupBy('type_key')
                ->pluck('total', 'type_key');
            $testTypeStats = collect($physicalTestTypes)->map(function ($type, $key) use ($physicalTypeCounts) {
                return [
                    'key' => $key,
                    'label' => $type['label'],
                    'color' => $type['color'],
                    'count' => (int) $physicalTypeCounts->get($key, 0),
                ];
            })->values();
        }
        if ($includeCoachAssessments) {
            $testTypeStats->push(
                ['key' => 'mental', 'label' => 'Assessment mental', 'color' => '#F06292', 'count' => $mentalAssessments->count()],
                ['key' => 'taktik', 'label' => 'Assessment taktik', 'color' => '#B39DDB', 'count' => $taktikAssessments->count()]
            );
        }

        $averageAssessmentScore = fn($assessments) => $assessments->filter(fn($assessment) => $assessment->average_score > 0)->avg('average_score');
        $avgMentalAssessment = $includeCoachAssessments && $averageAssessmentScore($mentalAssessments) !== null
            ? number_format($averageAssessmentScore($mentalAssessments), 1) . '/5'
            : '—';
        $avgTaktikAssessment = $includeCoachAssessments && $averageAssessmentScore($taktikAssessments) !== null
            ? number_format($averageAssessmentScore($taktikAssessments), 1) . '/5'
            : '—';

        return view('dashboard.index', compact(
            'totalAthletes',
            'typesOfTests',
            'isAdmin',
            'isCoach',
            'includePhysical',
            'includeCoachAssessments',
            'totalTestsToday',
            'mentalAssessmentsToday',
            'taktikAssessmentsToday',
            'completionRate',
            'avgYoyo',
            'bestSpeed',
            'avgAgility',
            'maxPushup',
            'avgMentalAssessment',
            'avgTaktikAssessment',
            'testTrend',
            'testTypeStats',
            'recentAthletes',
            'physicalTestTypes'
        ));
    }
}
