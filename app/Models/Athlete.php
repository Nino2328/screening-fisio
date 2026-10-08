<?php
// app/Models/Athlete.php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Athlete extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'birth_place',
        'birth_date',
        'age',
        'gender',
        'position',
        'height',
        'weight',
        'phone',
        'photo',
        'notes',
        'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function testResults()
    {
        return $this->hasMany(TestResult::class);
    }

    public function mentalAssessments()
    {
        return $this->hasMany(MentalAssessment::class);
    }

    public function taktikAssessments()
    {
        return $this->hasMany(TaktikAssessment::class);
    }

    /**
     * Hitung progress screening (berapa test_type unik sudah diisi)
     */
    public function getProgressAttribute(): int
    {
        $done = $this->testResults()->distinct('test_type')->count('test_type');
        return (int) min(100, ($done / 10) * 100);
    }
}
