<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Screening Semua Atlet — FootScreen Pro</title>
</head>

<body>
    @foreach($athletes as $athleteData)
        @if(!$loop->first)
            <div style="page-break-before: always"></div>
        @endif
        @include('report.pdf-athlete', [
            'profileResult' => $athleteData['profileResult'],
            'testResults' => $athleteData['testResults'],
            'printedBy' => $printedBy,
            'pageNumber' => $loop->iteration,
            'pageCount' => $loop->count,
        ])
    @endforeach
</body>

</html>