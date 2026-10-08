<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MentalAssessment;
use App\Models\TaktikAssessment;
use Barryvdh\Dompdf\Facade\Pdf;

class ReportsController extends Controller
{
    public function index()
    {
        $mentalAssessments = MentalAssessment::with('athlete', 'user')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $taktikAssessments = TaktikAssessment::with('athlete', 'user')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        return view('reports.index', compact('mentalAssessments', 'taktikAssessments'));
    }

    public function exportPdf()
    {
        $mentalAssessments = MentalAssessment::with('athlete', 'user')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $taktikAssessments = TaktikAssessment::with('athlete', 'user')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $pdf = Pdf::loadView('reports.pdf', compact('mentalAssessments', 'taktikAssessments'));

        return $pdf->download('laporan_assessment_pelatih_' . auth()->user()->name . '_' . now()->format('Y-m-d') . '.pdf');
    }

    public function exportExcel()
    {
        $mentalAssessments = MentalAssessment::with('athlete')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $taktikAssessments = TaktikAssessment::with('athlete')
            ->where('user_id', auth()->id())
            ->latest()
            ->get();

        $filename = 'laporan_assessment_pelatih_' . auth()->user()->name . '_' . now()->format('Y-m-d') . '.csv';

        $callback = function () use ($mentalAssessments, $taktikAssessments) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, ['Mental Assessments']);
            fputcsv($handle, [
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
            ]);

            foreach ($mentalAssessments as $assessment) {
                fputcsv($handle, [
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
                    $assessment->recommendations,
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Taktik Assessments']);
            fputcsv($handle, [
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
            ]);

            foreach ($taktikAssessments as $assessment) {
                fputcsv($handle, [
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
                    $assessment->formation_notes,
                ]);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}