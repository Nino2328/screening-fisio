<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Athlete;
use App\Models\TaktikAssessment;
use Illuminate\Support\Facades\Auth;

class TaktikController extends Controller
{
    /**
     * Index page - Daftar taktik assessments
     */
    public function index()
    {
        $assessments = TaktikAssessment::with('athlete', 'user')
            ->where('user_id', Auth::id())
            ->latest('assessment_date')
            ->paginate(10);

        return view('taktik.index', [
            'assessments' => $assessments,
        ]);
    }

    /**
     * Show form untuk tambah taktik assessment
     */
    public function create()
    {
        return view('taktik.create', [
            'athletes' => Athlete::orderBy('name')->get(),
            'categories' => TaktikAssessment::getCategories(),
            'scoreLabels' => TaktikAssessment::getScoreLabels(),
        ]);
    }

    /**
     * Store taktik assessment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'athlete_id' => 'required|exists:athletes,id',
            'assessment_date' => 'required|date',
            'dribbling' => 'nullable|integer|min:1|max:5',
            'dribbling_note' => 'nullable|string|max:255',
            'passing' => 'nullable|integer|min:1|max:5',
            'passing_note' => 'nullable|string|max:255',
            'shooting' => 'nullable|integer|min:1|max:5',
            'shooting_note' => 'nullable|string|max:255',
            'positioning' => 'nullable|integer|min:1|max:5',
            'positioning_note' => 'nullable|string|max:255',
            'game_reading' => 'nullable|integer|min:1|max:5',
            'game_reading_note' => 'nullable|string|max:255',
            'decision_making' => 'nullable|integer|min:1|max:5',
            'decision_making_note' => 'nullable|string|max:255',
            'defensive_skill' => 'nullable|integer|min:1|max:5',
            'defensive_skill_note' => 'nullable|string|max:255',
            'set_pieces' => 'nullable|integer|min:1|max:5',
            'set_pieces_note' => 'nullable|string|max:255',
            'overall_assessment' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'formation_notes' => 'nullable|string',
        ]);

        $athlete = Athlete::findOrFail($validated['athlete_id']);

        TaktikAssessment::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'name' => $athlete->name,
        ]));

        return redirect()->route('taktik.index')
            ->with('success', 'Taktik assessment berhasil disimpan!');
    }

    /**
     * Show detail taktik assessment
     */
    public function show($id)
    {
        $assessment = TaktikAssessment::with('athlete', 'user')
            ->findOrFail($id);

        // Pastikan user hanya bisa lihat assessment miliknya sendiri (kecuali admin)
        if (Auth::user()->role !== 'admin' && $assessment->user_id !== Auth::id()) {
            abort(403);
        }

        return view('taktik.show', [
            'assessment' => $assessment,
            'categories' => TaktikAssessment::getCategories(),
            'scoreLabels' => TaktikAssessment::getScoreLabels(),
        ]);
    }

    /**
     * Show form edit
     */
    public function edit($id)
    {
        $assessment = TaktikAssessment::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $assessment->user_id !== Auth::id()) {
            abort(403);
        }

        return view('taktik.edit', [
            'assessment' => $assessment,
            'athletes' => Athlete::orderBy('name')->get(),
            'categories' => TaktikAssessment::getCategories(),
            'scoreLabels' => TaktikAssessment::getScoreLabels(),
        ]);
    }

    /**
     * Update taktik assessment
     */
    public function update(Request $request, $id)
    {
        $assessment = TaktikAssessment::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $assessment->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'athlete_id' => 'required|exists:athletes,id',
            'assessment_date' => 'required|date',
            'dribbling' => 'nullable|integer|min:1|max:5',
            'dribbling_note' => 'nullable|string|max:255',
            'passing' => 'nullable|integer|min:1|max:5',
            'passing_note' => 'nullable|string|max:255',
            'shooting' => 'nullable|integer|min:1|max:5',
            'shooting_note' => 'nullable|string|max:255',
            'positioning' => 'nullable|integer|min:1|max:5',
            'positioning_note' => 'nullable|string|max:255',
            'game_reading' => 'nullable|integer|min:1|max:5',
            'game_reading_note' => 'nullable|string|max:255',
            'decision_making' => 'nullable|integer|min:1|max:5',
            'decision_making_note' => 'nullable|string|max:255',
            'defensive_skill' => 'nullable|integer|min:1|max:5',
            'defensive_skill_note' => 'nullable|string|max:255',
            'set_pieces' => 'nullable|integer|min:1|max:5',
            'set_pieces_note' => 'nullable|string|max:255',
            'overall_assessment' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'formation_notes' => 'nullable|string',
        ]);

        $athlete = Athlete::findOrFail($validated['athlete_id']);

        $assessment->update(array_merge($validated, [
            'name' => $athlete->name,
        ]));

        return redirect()->route('taktik.show', $id)
            ->with('success', 'Taktik assessment berhasil diperbarui!');
    }

    /**
     * Delete taktik assessment
     */
    public function destroy($id)
    {
        $assessment = TaktikAssessment::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $assessment->user_id !== Auth::id()) {
            abort(403);
        }

        $assessment->delete();

        return redirect()->route('taktik.index')
            ->with('success', 'Taktik assessment berhasil dihapus!');
    }
}
