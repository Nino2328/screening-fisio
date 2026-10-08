<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TaktikAssessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'athlete_id',
        'name',
        'assessment_date',
        'dribbling',
        'dribbling_note',
        'passing',
        'passing_note',
        'shooting',
        'shooting_note',
        'positioning',
        'positioning_note',
        'game_reading',
        'game_reading_note',
        'decision_making',
        'decision_making_note',
        'defensive_skill',
        'defensive_skill_note',
        'set_pieces',
        'set_pieces_note',
        'overall_assessment',
        'recommendations',
        'formation_notes',
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
     * Hitung rata-rata skor taktik
     */
    public function getAverageScoreAttribute(): float
    {
        $scores = [
            $this->dribbling,
            $this->passing,
            $this->shooting,
            $this->positioning,
            $this->game_reading,
            $this->decision_making,
            $this->defensive_skill,
            $this->set_pieces,
        ];

        $validScores = array_filter($scores, fn($score) => $score !== null);
        return count($validScores) > 0 ? round(array_sum($validScores) / count($validScores), 2) : 0;
    }

    /**
     * Kategori taktik
     */
    public static function getCategories(): array
    {
        return [
            'dribbling' => 'Dribbling',
            'passing' => 'Passing',
            'shooting' => 'Shooting',
            'positioning' => 'Positioning',
            'game_reading' => 'Pembacaan Permainan',
            'decision_making' => 'Pengambilan Keputusan',
            'defensive_skill' => 'Pertahanan',
            'set_pieces' => 'Set Pieces',
        ];
    }

    public static function getScoreLabels(): array
    {
        return array_fill_keys(array_keys(self::getCategories()), [
            1 => 'Sangat kurang',
            2 => 'Kurang',
            3 => 'Cukup',
            4 => 'Baik',
            5 => 'Sangat baik',
        ]);
    }
}
