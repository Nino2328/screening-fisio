<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Athlete;
use App\Models\MentalAssessment;
use Illuminate\Support\Facades\Auth;

class MentalController extends Controller
{
    /**
     * Index page - Daftar mental assessments
     */
    public function index()
    {
        $assessments = MentalAssessment::with('athlete', 'user')
            ->where('user_id', Auth::id())
            ->latest('assessment_date')
            ->paginate(10);

        return view('mental.index', [
            'assessments' => $assessments,
        ]);
    }

    /**
     * Show form untuk tambah mental assessment
     */
    public function create()
    {
        return view('mental.create', [
            'athletes' => Athlete::orderBy('name')->get(),
            'categories' => MentalAssessment::getCategories(),
            'scoreLabels' => MentalAssessment::getScoreLabels(),
        ]);
    }

    /**
     * Store mental assessment
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'athlete_id' => 'required|exists:athletes,id',
            'assessment_date' => 'required|date',
            'confidence' => 'nullable|integer|min:1|max:5',
            'confidence_note' => 'nullable|string|max:255',
            'focus' => 'nullable|integer|min:1|max:5',
            'focus_note' => 'nullable|string|max:255',
            'anxiety' => 'nullable|integer|min:1|max:5',
            'anxiety_note' => 'nullable|string|max:255',
            'motivation' => 'nullable|integer|min:1|max:5',
            'motivation_note' => 'nullable|string|max:255',
            'team_spirit' => 'nullable|integer|min:1|max:5',
            'team_spirit_note' => 'nullable|string|max:255',
            'pressure_handling' => 'nullable|integer|min:1|max:5',
            'pressure_handling_note' => 'nullable|string|max:255',
            'overall_assessment' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        $athlete = Athlete::findOrFail($validated['athlete_id']);

        MentalAssessment::create(array_merge($validated, [
            'user_id' => Auth::id(),
            'name' => $athlete->name,
        ]));

        return redirect()->route('mental.index')
            ->with('success', 'Mental assessment berhasil disimpan!');
    }

    /**
     * Show detail mental assessment
     */
    public function show($id)
    {
        $assessment = MentalAssessment::with('athlete', 'user')
            ->findOrFail($id);

        // Pastikan user hanya bisa lihat assessment miliknya sendiri (kecuali admin)
        if (Auth::user()->role !== 'admin' && $assessment->user_id !== Auth::id()) {
            abort(403);
        }

        return view('mental.show', [
            'assessment' => $assessment,
            'categories' => MentalAssessment::getCategories(),
            'scoreLabels' => MentalAssessment::getScoreLabels(),
        ]);
    }

    /**
     * Show form edit
     */
    public function edit($id)
    {
        $assessment = MentalAssessment::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $assessment->user_id !== Auth::id()) {
            abort(403);
        }

        return view('mental.edit', [
            'assessment' => $assessment,
            'athletes' => Athlete::orderBy('name')->get(),
            'categories' => MentalAssessment::getCategories(),
            'scoreLabels' => MentalAssessment::getScoreLabels(),
        ]);
    }

    /**
     * Update mental assessment
     */
    public function update(Request $request, $id)
    {
        $assessment = MentalAssessment::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $assessment->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'athlete_id' => 'required|exists:athletes,id',
            'assessment_date' => 'required|date',
            'confidence' => 'nullable|integer|min:1|max:5',
            'confidence_note' => 'nullable|string|max:255',
            'focus' => 'nullable|integer|min:1|max:5',
            'focus_note' => 'nullable|string|max:255',
            'anxiety' => 'nullable|integer|min:1|max:5',
            'anxiety_note' => 'nullable|string|max:255',
            'motivation' => 'nullable|integer|min:1|max:5',
            'motivation_note' => 'nullable|string|max:255',
            'team_spirit' => 'nullable|integer|min:1|max:5',
            'team_spirit_note' => 'nullable|string|max:255',
            'pressure_handling' => 'nullable|integer|min:1|max:5',
            'pressure_handling_note' => 'nullable|string|max:255',
            'overall_assessment' => 'nullable|string',
            'recommendations' => 'nullable|string',
        ]);

        $athlete = Athlete::findOrFail($validated['athlete_id']);

        $assessment->update(array_merge($validated, [
            'name' => $athlete->name,
        ]));

        return redirect()->route('mental.show', $id)
            ->with('success', 'Mental assessment berhasil diperbarui!');
    }

    /**
     * Delete mental assessment
     */
    public function destroy($id)
    {
        $assessment = MentalAssessment::findOrFail($id);

        if (Auth::user()->role !== 'admin' && $assessment->user_id !== Auth::id()) {
            abort(403);
        }

        $assessment->delete();

        return redirect()->route('mental.index')
            ->with('success', 'Mental assessment berhasil dihapus!');
    }
}
