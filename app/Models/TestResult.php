<?php
// app/Models/TestResult.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TestResult extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'athlete_id',
        'test_type',
        'name',
        'age',
        'gender',
        'position',
        'height',
        'weight',
        'phone',
        'test_date',
        'vital_td',
        'vital_suhu',
        'vital_nadi',
        'vital_respirasi',
        'complaint',
        // Endurance
        'distance',
        'level',
        // Speed
        'time_seconds',
        'score',
        // Agility
        'hex_time1',
        'hex_time2',
        'hex_avg',
        'hex_rating',
        'ill_time',
        'ill_rating',
        // Strength
        'pushup_count',
        'pushup_score',
        'pushup_rating',
        'pushup_note',
        'situp_count',
        'situp_score',
        'situp_rating',
        'situp_note',
        // Power & Flexibility (share columns)
        'try1',
        'try2',
        'try3',
        'best',
        // Accuracy
        'shot1_score',
        'shot1_time',
        'shot1_note',
        'shot2_score',
        'shot2_time',
        'shot2_note',
        'shot3_score',
        'shot3_time',
        'shot3_note',
        'total_score',
        // Reaction
        'value_cm',
        // Coordination
        'steps50',
        'steps50_result',
        'steps100',
        'steps100_result',
        'rotation_direction',
        // Balance
        'leg_length',
        'left_a',
        'left_pm',
        'left_pl',
        'right_a',
        'right_pm',
        'right_pl',
        'res_left_anterior',
        'res_right_anterior',
        'res_diff_anterior',
        'res_left_pm',
        'res_right_pm',
        'res_diff_pm',
        'res_left_pl',
        'res_right_pl',
        'res_diff_pl',
        'composite_left',
        'composite_right',
        'std_anterior',
        'std_pm',
        'std_pl',
        'std_composite',
        // Shared
        'rating',
        'conclusion',
    ];

    protected $casts = ['test_date' => 'datetime'];

    public function athlete()
    {
        return $this->belongsTo(Athlete::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope: filter by test type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('test_type', $type);
    }

    public function getTestTypeLabelAttribute(): string
    {
        return [
            'endurance' => 'Endurance (YoYo)',
            'speed' => 'Speed 50 Meter',
            'agility' => 'Agility',
            'strength' => 'Strength',
            'power' => 'Power Jump',
            'accuracy' => 'Akurasi Tendangan',
            'reaction' => 'Kecepatan Reaksi',
            'coordination' => 'Koordinasi',
            'flexibility' => 'Fleksibilitas Ankle',
            'balance' => 'Y Balance',
        ][$this->test_type] ?? ucfirst((string) $this->test_type);
    }

    public function getDisplayResultsAttribute(): array
    {
        $values = match ($this->test_type) {
            'endurance' => ['Jarak' => $this->distance !== null ? $this->distance . ' m' : null, 'Level' => $this->level],
            'speed' => ['Waktu' => $this->time_seconds !== null ? $this->time_seconds . ' detik' : null, 'Skor' => $this->score !== null ? $this->score . '/5' : null, 'Kriteria' => $this->rating],
            'agility' => ['Hexagonal' => $this->hex_avg !== null ? $this->hex_avg . ' detik' : null, 'Illinois' => $this->ill_time !== null ? $this->ill_time . ' detik' : null],
            'strength' => ['Push-up' => $this->pushup_count !== null ? $this->pushup_count . ' kali' : null, 'Sit-up' => $this->situp_count !== null ? $this->situp_count . ' kali' : null],
            'power', 'flexibility' => ['Hasil terbaik' => $this->best !== null ? $this->best . ' cm' : null],
            'accuracy' => ['Total poin' => $this->total_score],
            'reaction' => ['Jarak reaksi' => $this->value_cm !== null ? $this->value_cm . ' cm' : null],
            'coordination' => ['50 langkah' => $this->steps50 !== null ? $this->steps50 . '°' : null, '100 langkah' => $this->steps100 !== null ? $this->steps100 . '°' : null, 'Arah rotasi' => $this->rotation_direction],
            'balance' => ['Composite kiri' => $this->composite_left, 'Composite kanan' => $this->composite_right],
            default => [],
        };

        return array_filter($values, fn($value) => $value !== null && $value !== '');
    }

    public function getDisplayRatingsAttribute(): array
    {
        $ratings = match ($this->test_type) {
            'agility' => ['Hexagonal' => $this->hex_rating, 'Illinois' => $this->ill_rating],
            'strength' => ['Push-up' => $this->pushup_rating, 'Sit-up' => $this->situp_rating],
            default => ['Rating' => $this->rating],
        };

        return array_filter($ratings, fn($rating) => $rating !== null && $rating !== '');
    }
}
