<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MentalAssessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'athlete_id',
        'name',
        'assessment_date',
        'confidence',
        'confidence_note',
        'focus',
        'focus_note',
        'anxiety',
        'anxiety_note',
        'motivation',
        'motivation_note',
        'team_spirit',
        'team_spirit_note',
        'pressure_handling',
        'pressure_handling_note',
        'overall_assessment',
        'recommendations',
    ];

    protected $casts = [
        'assessment_date' => 'datetime',
    ];

    public function athlete()
    {
        return $this->belongsTo(Athlete::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Hitung rata-rata skor mental
     */
    public function getAverageScoreAttribute(): float
    {
        $scores = [
            $this->confidence,
            $this->focus,
            $this->anxiety,
            $this->motivation,
            $this->team_spirit,
            $this->pressure_handling,
        ];

        $validScores = array_filter($scores, fn($score) => $score !== null);
        return count($validScores) > 0 ? round(array_sum($validScores) / count($validScores), 2) : 0;
    }

    /**
     * Kategori mental
     */
    public static function getCategories(): array
    {
        return [
            'confidence' => 'Kepercayaan Diri',
            'focus' => 'Konsentrasi',
            'anxiety' => 'Kecemasan',
            'motivation' => 'Motivasi',
            'team_spirit' => 'Semangat Tim',
            'pressure_handling' => 'Penanganan Tekanan',
        ];
    }

    public static function getScoreLabels(): array
    {
        $labels = array_fill_keys(array_keys(self::getCategories()), [
            1 => 'Sangat kurang',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Baik',
            5 => 'Sangat baik',
        ]);

        $labels['anxiety'] = [
            1 => 'Sangat cemas',
            2 => 'Cemas',
            3 => 'Cukup tenang',
            4 => 'Tenang',
            5 => 'Sangat tenang',
        ];

        return $labels;
    }
}
