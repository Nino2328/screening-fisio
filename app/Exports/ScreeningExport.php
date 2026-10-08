<?php

namespace App\Exports;

use App\Models\TestResult;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ScreeningExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return TestResult::all()->map(function ($result) {
            return [
                'ID' => $result->id,
                'Nama Atlet' => $result->name,
                'Jenis Tes' => $result->test_type,
                'Rating' => $result->rating,
                'Tanggal Dibuat' => $result->created_at->format('Y-m-d H:i:s'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Atlet',
            'Jenis Tes',
            'Rating',
            'Tanggal Dibuat',
        ];
    }
}