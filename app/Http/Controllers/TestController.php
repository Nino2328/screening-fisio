<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Athlete;
use App\Models\TestResult;

class TestController extends Controller
{
    /**
     * Semua Tes — index page
     */
    public function index()
    {
        return view('tests.index');
    }

    /**
     * Helper: ambil daftar atlet untuk dropdown form
     */
    private function athletes()
    {
        return Athlete::orderBy('name')->get();
    }

    private function saveTestResult(Request $request, array $validated, string $testType): TestResult
    {
        $athleteId = $request->validate([
            'athlete_id' => 'nullable|exists:athletes,id',
        ])['athlete_id'] ?? null;

        return TestResult::create(array_merge($validated, [
            'athlete_id' => $athleteId,
            'user_id' => auth()->id(),
            'test_type' => $testType,
        ]));
    }

    // ================================================================
    //  1. ENDURANCE — YoYo Test
    // ================================================================
    public function form_endurance()
    {
        return view('forms.endurance', ['athletes' => $this->athletes()]);
    }

    public function store_endurance(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            'gender' => 'required|in:putra,putri',
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'vital_td' => 'nullable|string',
            'vital_suhu' => 'nullable|string',
            'vital_nadi' => 'nullable|string',
            'vital_respirasi' => 'nullable|string',
            'complaint' => 'nullable|string',
            'distance' => 'required|numeric|min:0',
            'level' => 'nullable|string',
            'rating' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        $this->saveTestResult($request, $validated, 'endurance');

        return redirect()->route('forms.endurance')
            ->with('success', 'Data YoYo Test berhasil disimpan!');
    }

    // ================================================================
    //  2. SPEED — 50 Meter
    // ================================================================
    public function form_speed()
    {
        return view('forms.speed', ['athletes' => $this->athletes()]);
    }

    public function store_speed(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            'gender' => 'required|in:putra,putri',
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'vital_td' => 'nullable|string',
            'vital_suhu' => 'nullable|string',
            'vital_nadi' => 'nullable|string',
            'vital_respirasi' => 'nullable|string',
            'complaint' => 'nullable|string',
            'time_seconds' => 'required|numeric|min:0',
            'score' => 'nullable|integer|min:1|max:5',
            'rating' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        $this->saveTestResult($request, $validated, 'speed');

        return redirect()->route('forms.speed')
            ->with('success', 'Data Speed Test berhasil disimpan!');
    }

    // ================================================================
    //  3. AGILITY — Hexagonal + Illinois
    // ================================================================
    public function form_agility()
    {
        return view('forms.agility', ['athletes' => $this->athletes()]);
    }

    public function store_agility(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            'gender' => 'required|in:putra,putri,pria,wanita',
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'vital_td' => 'nullable|string',
            'vital_suhu' => 'nullable|string',
            'vital_nadi' => 'nullable|string',
            'vital_respirasi' => 'nullable|string',
            'complaint' => 'nullable|string',
            'hex_time1' => 'nullable|numeric',
            'hex_time2' => 'nullable|numeric',
            'hex_avg' => 'nullable|numeric',
            'hex_rating' => 'nullable|string',
            'ill_time' => 'nullable|numeric',
            'ill_rating' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        $this->saveTestResult($request, $validated, 'agility');

        return redirect()->route('forms.agility')
            ->with('success', 'Data Agility Test berhasil disimpan!');
    }

    // ================================================================
    //  4. STRENGTH — Push Up & Sit Up
    // ================================================================
    public function form_strength()
    {
        return view('forms.strength', ['athletes' => $this->athletes()]);
    }

    public function store_strength(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            'gender' => 'required|in:putra,putri',
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'complaint' => 'nullable|string',
            'pushup_count' => 'nullable|integer|min:0',
            'pushup_score' => 'nullable|integer|min:1|max:5',
            'pushup_rating' => 'nullable|string',
            'pushup_note' => 'nullable|string',
            'situp_count' => 'nullable|integer|min:0',
            'situp_score' => 'nullable|integer|min:1|max:5',
            'situp_rating' => 'nullable|string',
            'situp_note' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        $this->saveTestResult($request, $validated, 'strength');

        return redirect()->route('forms.strength')
            ->with('success', 'Data Strength Test berhasil disimpan!');
    }

    // ================================================================
    //  5. POWER — Standing Long Jump
    // ================================================================
    public function form_power()
    {
        return view('forms.power', ['athletes' => $this->athletes()]);
    }

    public function store_power(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            'gender' => 'required|in:putra,putri,pria,wanita',
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'complaint' => 'nullable|string',
            'try1' => 'nullable|numeric',
            'try2' => 'nullable|numeric',
            'try3' => 'nullable|numeric',
            'best' => 'required|numeric|min:0',
            'rating' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        $this->saveTestResult($request, $validated, 'power');

        return redirect()->route('forms.power')
            ->with('success', 'Data Power Jump berhasil disimpan!');
    }

    // ================================================================
    //  6. ACCURACY — Shooting
    // ================================================================
    public function form_accuracy()
    {
        return view('forms.accuracy', ['athletes' => $this->athletes()]);
    }

    public function store_accuracy(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'shot1_score' => 'nullable|numeric',
            'shot1_time' => 'nullable|numeric',
            'shot1_note' => 'nullable|string',
            'shot2_score' => 'nullable|numeric',
            'shot2_time' => 'nullable|numeric',
            'shot2_note' => 'nullable|string',
            'shot3_score' => 'nullable|numeric',
            'shot3_time' => 'nullable|numeric',
            'shot3_note' => 'nullable|string',
            'total_score' => 'nullable|numeric',
            'rating' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        $this->saveTestResult($request, $validated, 'accuracy');

        return redirect()->route('forms.accuracy')
            ->with('success', 'Data Akurasi Tendangan berhasil disimpan!');
    }

    // ================================================================
    //  7. REACTION — Ruler Drop Test
    // ================================================================
    public function form_reaction()
    {
        return view('forms.reaction', ['athletes' => $this->athletes()]);
    }

    public function store_reaction(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'value_cm' => 'required|numeric|min:0',
            'rating' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        $this->saveTestResult($request, $validated, 'reaction');

        return redirect()->route('forms.reaction')
            ->with('success', 'Data Kecepatan Reaksi berhasil disimpan!');
    }

    // ================================================================
    //  8. COORDINATION — Fukuda Step Test
    // ================================================================
    public function form_coordination()
    {
        return view('forms.coordination', ['athletes' => $this->athletes()]);
    }

    public function store_coordination(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'steps50' => 'nullable|numeric',
            'steps50_result' => 'nullable|in:normal,abnormal',
            'steps100' => 'nullable|numeric',
            'steps100_result' => 'nullable|in:normal,abnormal',
            'rotation_direction' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        $this->saveTestResult($request, $validated, 'coordination');

        return redirect()->route('forms.coordination')
            ->with('success', 'Data Koordinasi berhasil disimpan!');
    }

    // ================================================================
    //  9. FLEXIBILITY — Ankle Test
    // ================================================================
    public function form_flexibility()
    {
        return view('forms.flexibility', ['athletes' => $this->athletes()]);
    }

    public function store_flexibility(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'try1' => 'nullable|numeric',
            'try2' => 'nullable|numeric',
            'try3' => 'nullable|numeric',
            'best' => 'required|numeric|min:0',
            'rating' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        $this->saveTestResult($request, $validated, 'flexibility');

        return redirect()->route('forms.flexibility')
            ->with('success', 'Data Fleksibilitas Ankle berhasil disimpan!');
    }

    // ================================================================
    //  10. BALANCE — Y Balance Test
    // ================================================================
    public function form_balance()
    {
        return view('forms.balance', ['athletes' => $this->athletes()]);
    }

    public function store_balance(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'age' => 'nullable|integer',
            'position' => 'nullable|string',
            'test_date' => 'required|date',
            'phone' => 'nullable|string',
            'leg_length' => 'required|numeric|min:1',
            'left_a' => 'nullable|numeric',
            'left_pm' => 'nullable|numeric',
            'left_pl' => 'nullable|numeric',
            'right_a' => 'nullable|numeric',
            'right_pm' => 'nullable|numeric',
            'right_pl' => 'nullable|numeric',
            'res_left_anterior' => 'nullable|string',
            'res_right_anterior' => 'nullable|string',
            'res_diff_anterior' => 'nullable|string',
            'res_left_pm' => 'nullable|string',
            'res_right_pm' => 'nullable|string',
            'res_diff_pm' => 'nullable|string',
            'res_left_pl' => 'nullable|string',
            'res_right_pl' => 'nullable|string',
            'res_diff_pl' => 'nullable|string',
            'composite_left' => 'nullable|string',
            'composite_right' => 'nullable|string',
            'std_anterior' => 'nullable|string',
            'std_pm' => 'nullable|string',
            'std_pl' => 'nullable|string',
            'std_composite' => 'nullable|string',
            'conclusion' => 'nullable|string',
        ]);

        $this->saveTestResult($request, $validated, 'balance');

        return redirect()->route('forms.balance')
            ->with('success', 'Data Y Balance Test berhasil disimpan!');
    }
}
