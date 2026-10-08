<?php

namespace App\Exports;

use App\Models\MentalAssessment;
use App\Models\TaktikAssessment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MentalTaktikExport implements WithMultipleSheets
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function sheets(): array
    {
        return [
            'Mental Assessments' => new MentalExport($this->userId),
            'Taktik Assessments' => new TaktikExport($this->userId),
        ];
    }
}

class MentalExport implements FromCollection, WithHeadings
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function headings(): array
    {
        return [
            'Atlet',
            'Tanggal Assessment',
            'Fokus',
            'Motivasi',
            'Konsentrasi',
            'Tekanan',
            'Keyakinan',
            'Kepemimpinan',
            'Rata-rata Skor',
            'Penilaian Keseluruhan',
            'Rekomendasi'
        ];
    }

    public function collection()
    {
        return MentalAssessment::with('athlete')
            ->where('user_id', $this->userId)
            ->get()
            ->map(function ($assessment) {
                return [
                    $assessment->athlete->name,
                    $assessment->assessment_date->format('d M Y H:i'),
                    $assessment->fokus,
                    $assessment->motivasi,
                    $assessment->konsentrasi,
                    $assessment->tekanan,
                    $assessment->keyakinan,
                    $assessment->kepemimpinan,
                    $assessment->average_score,
                    $assessment->overall_assessment,
                    $assessment->recommendations
                ];
            });
    }
}

class TaktikExport implements FromCollection, WithHeadings
{
    protected $userId;

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function headings(): array
    {
        return [
            'Atlet',
            'Tanggal Assessment',
            'Pengambilan Keputusan',
            'Pemahaman Taktik',
            'Adaptasi',
            'Komunikasi',
            'Visi Bermain',
            'Antisipasi',
            'Rata-rata Skor',
            'Penilaian Keseluruhan',
            'Rekomendasi',
            'Catatan Formasi'
        ];
    }

    public function collection()
    {
        return TaktikAssessment::with('athlete')
            ->where('user_id', $this->userId)
            ->get()
            ->map(function ($assessment) {
                return [
                    $assessment->athlete->name,
                    $assessment->assessment_date->format('d M Y H:i'),
                    $assessment->pengambilan_keputusan,
                    $assessment->pemahaman_taktik,
                    $assessment->adaptasi,
                    $assessment->komunikasi,
                    $assessment->visi_bermain,
                    $assessment->antisipasi,
                    $assessment->average_score,
                    $assessment->overall_assessment,
                    $assessment->recommendations,
                    $assessment->formation_notes
                ];
            });
    }
}