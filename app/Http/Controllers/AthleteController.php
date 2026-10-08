<?php
// app/Http/Controllers/AthleteController.php
namespace App\Http\Controllers;
use App\Models\Athlete;
use App\Models\MentalAssessment;
use App\Models\TaktikAssessment;
use App\Models\TestResult;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AthleteController extends Controller
{
    public function index()
    {
        $athletes = Athlete::orderBy('name')->paginate(15);
        return view('athletes.index', compact('athletes'));
    }
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:100',
                'birth_place' => 'nullable|string|max:100',
                'birth_date' => 'nullable|date',
                'age' => 'nullable|integer',
                'gender' => 'nullable|string',
                'position' => 'nullable|string',
                'height' => 'nullable|numeric',
                'weight' => 'nullable|numeric',
                'phone' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $photoName = Str::uuid() . '.' . $photo->extension();
                $photo->move(public_path('uploads/athletes'), $photoName);
                $data['photo'] = 'uploads/athletes/' . $photoName;
            }

            $athlete = Athlete::create($data);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'athlete' => $athlete]);
            }

            return redirect()->route('athletes.index')->with('success', 'Atlet berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        }
    }
    public function show($id)
    {
        $athlete = Athlete::findOrFail($id);
        $matchAthlete = function ($query) use ($athlete) {
            $query->where('athlete_id', $athlete->id)
                ->orWhere(function ($legacyQuery) use ($athlete) {
                    $legacyQuery->whereNull('athlete_id')
                        ->where('name', $athlete->name);
                });
        };
        $testResults = TestResult::where($matchAthlete)->latest('test_date')->get();
        $mentalAssessments = MentalAssessment::where($matchAthlete)->latest('assessment_date')->get();
        $taktikAssessments = TaktikAssessment::where($matchAthlete)->latest('assessment_date')->get();

        $latestByType = $testResults->groupBy('test_type')->map(fn($results) => $results->first());
        $ratingScore = function ($rating): ?float {
            if (!$rating) {
                return null;
            }

            $rating = mb_strtolower(trim($rating));
            return match (true) {
                str_contains($rating, 'elite'),
                str_contains($rating, 'excellent'),
                str_contains($rating, 'luar biasa'),
                str_contains($rating, 'baik sekali'),
                str_contains($rating, 'optimal') => 5.0,
                str_contains($rating, 'good'),
                str_contains($rating, 'above average'),
                str_contains($rating, 'diatas rata'),
                $rating === 'baik' => 4.0,
                str_contains($rating, 'below'),
                str_contains($rating, 'di bawah'),
                str_contains($rating, 'dibawah'),
                str_contains($rating, 'kurang') => 2.0,
                str_contains($rating, 'average'),
                str_contains($rating, 'rata-rata'),
                str_contains($rating, 'rata rata'),
                str_contains($rating, 'cukup') => 3.0,
                str_contains($rating, 'poor'),
                str_contains($rating, 'miskin'),
                str_contains($rating, 'rendah'),
                str_contains($rating, 'abnormal') => 1.0,
                default => null,
            };
        };
        $averageScore = function (array $scores): ?float {
            $scores = array_values(array_filter($scores, fn($score) => $score !== null));
            return count($scores) ? round(array_sum($scores) / count($scores), 1) : null;
        };
        $coordination = $latestByType->get('coordination');
        $balance = $latestByType->get('balance');
        $coordinationScores = collect([$coordination?->steps50_result, $coordination?->steps100_result])
            ->map(fn($result) => match (mb_strtolower((string) $result)) {
                'normal' => 4.0,
                'abnormal' => 2.0,
                default => null,
            })->all();
        $radarDefinitions = [
            'endurance' => ['label' => 'YoYo', 'color' => '#00E676', 'score' => $ratingScore($latestByType->get('endurance')?->rating)],
            'speed' => ['label' => 'Speed 50m', 'color' => '#FFD740', 'score' => $ratingScore($latestByType->get('speed')?->rating)],
            'agility' => [
                'label' => 'Agility',
                'color' => '#40C4FF',
                'score' => $averageScore([
                    $ratingScore($latestByType->get('agility')?->hex_rating),
                    $ratingScore($latestByType->get('agility')?->ill_rating),
                    $ratingScore($latestByType->get('agility')?->rating),
                ])
            ],
            'strength' => [
                'label' => 'Strength',
                'color' => '#FF6E6E',
                'score' => $averageScore([
                    $ratingScore($latestByType->get('strength')?->pushup_rating),
                    $ratingScore($latestByType->get('strength')?->situp_rating),
                    $ratingScore($latestByType->get('strength')?->rating),
                ])
            ],
            'power' => ['label' => 'Power Jump', 'color' => '#FFAB40', 'score' => $ratingScore($latestByType->get('power')?->rating)],
            'accuracy' => ['label' => 'Akurasi', 'color' => '#26C6DA', 'score' => $ratingScore($latestByType->get('accuracy')?->rating)],
            'reaction' => ['label' => 'Reaksi', 'color' => '#F06292', 'score' => $ratingScore($latestByType->get('reaction')?->rating)],
            'coordination' => ['label' => 'Koordinasi', 'color' => '#B39DDB', 'score' => $averageScore($coordinationScores)],
            'flexibility' => ['label' => 'Fleksibilitas', 'color' => '#AED581', 'score' => $ratingScore($latestByType->get('flexibility')?->rating)],
            'balance' => ['label' => 'Balance', 'color' => '#FF8A65', 'score' => $ratingScore($balance?->std_composite ?? $balance?->rating)],
        ];
        $radarStats = collect($radarDefinitions)->values();
        $ratedTestCount = $radarStats->whereNotNull('score')->count();

        $testGroups = [
            'Daya tahan' => ['endurance'],
            'Kecepatan' => ['speed', 'reaction'],
            'Kelincahan' => ['agility'],
            'Kekuatan' => ['strength'],
            'Daya ledak' => ['power'],
            'Fungsional' => ['accuracy', 'coordination', 'flexibility', 'balance'],
        ];
        $completedTypes = $testResults->pluck('test_type')->unique();
        $screeningStats = collect($testGroups)->map(function ($types, $label) use ($completedTypes) {
            $completed = $completedTypes->intersect($types)->count();

            return [
                'label' => $label,
                'completed' => $completed,
                'total' => count($types),
                'percentage' => (int) round(($completed / count($types)) * 100),
            ];
        })->values();
        $completedTestTypes = $completedTypes->count();
        $latestTestDate = $testResults->first()?->test_date;

        // Return JSON if requested via AJAX
        if (request()->wantsJson()) {
            return response()->json($athlete);
        }

        return view('athletes.show', compact(
            'athlete',
            'testResults',
            'mentalAssessments',
            'taktikAssessments',
            'radarStats',
            'ratedTestCount',
            'screeningStats',
            'completedTestTypes',
            'latestTestDate'
        ));
    }
    public function update(Request $request, $id)
    {
        try {
            $athlete = Athlete::findOrFail($id);
            $data = $request->validate([
                'name' => 'required|string|max:100',
                'birth_place' => 'nullable|string|max:100',
                'birth_date' => 'nullable|date',
                'age' => 'nullable|integer',
                'gender' => 'nullable|string',
                'position' => 'nullable|string',
                'height' => 'nullable|numeric',
                'weight' => 'nullable|numeric',
                'phone' => 'nullable|string',
                'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // Handle photo upload
            if ($request->hasFile('photo')) {
                // Delete old photo if exists
                if ($athlete->photo && str_starts_with($athlete->photo, 'uploads/athletes/') && file_exists(public_path($athlete->photo))) {
                    unlink(public_path($athlete->photo));
                }

                $photo = $request->file('photo');
                $photoName = Str::uuid() . '.' . $photo->extension();
                $photo->move(public_path('uploads/athletes'), $photoName);
                $data['photo'] = 'uploads/athletes/' . $photoName;
            }

            $athlete->update($data);

            if ($request->wantsJson()) {
                return response()->json(['success' => true, 'athlete' => $athlete]);
            }

            return redirect()->route('athletes.index')->with('success', 'Data atlet diperbarui!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'errors' => $e->errors()], 422);
            }
            return back()->withErrors($e->errors())->withInput();
        }
    }
    public function destroy($id)
    {
        $athlete = Athlete::findOrFail($id);
        $athlete->delete();

        return redirect()->route('athletes.index')->with('success', 'Atlet dihapus!');
    }
}
