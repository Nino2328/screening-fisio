<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Laporan Assessment Pelatih - {{ auth()->user()->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: #00E676;
            margin: 0;
        }

        .header p {
            color: #7A8D9E;
            margin: 5px 0;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            color: #00E676;
            border-bottom: 2px solid #00E676;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            color: #00E676;
        }

        .score {
            text-align: center;
            font-weight: bold;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            color: #7A8D9E;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>Laporan Assessment Pelatih</h1>
        <p>Dibuat oleh: {{ auth()->user()->name }}</p>
        <p>Tanggal: {{ now()->format('d M Y H:i') }}</p>
    </div>

    @if ($mentalAssessments->count())
        <div class="section">
            <h2>🧠 Mental Assessments</h2>
            <table>
                <thead>
                    <tr>
                        <th>Atlet</th>
                        <th>Tanggal Assessment</th>
                        <th>Rata-rata Skor</th>
                        <th>Penilaian Keseluruhan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($mentalAssessments as $assessment)
                        <tr>
                            <td>{{ $assessment->athlete?->name ?? '-' }}</td>
                            <td>{{ $assessment->assessment_date->format('d M Y H:i') }}</td>
                            <td class="score">{{ $assessment->average_score }}/5.0</td>
                            <td>{{ Str::limit($assessment->overall_assessment, 100) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($taktikAssessments->count())
        <div class="section">
            <h2>⚽ Taktik Assessments</h2>
            <table>
                <thead>
                    <tr>
                        <th>Atlet</th>
                        <th>Tanggal Assessment</th>
                        <th>Rata-rata Skor</th>
                        <th>Penilaian Keseluruhan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($taktikAssessments as $assessment)
                        <tr>
                            <td>{{ $assessment->athlete?->name ?? '-' }}</td>
                            <td>{{ $assessment->assessment_date->format('d M Y H:i') }}</td>
                            <td class="score">{{ $assessment->average_score }}/5.0</td>
                            <td>{{ Str::limit($assessment->overall_assessment, 100) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="footer">
        <p>Dokumen ini dibuat secara otomatis oleh sistem Screening Fisio</p>
        <p>© {{ now()->year }} - FootScreen Pro</p>
    </div>
</body>

</html>