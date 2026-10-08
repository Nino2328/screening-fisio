<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Laporan Screening — FootScreen Pro</title>
</head>

<body>
    @include('report.pdf-athlete', [
        'profileResult' => $profileResult,
        'testResults' => $testResults,
        'printedBy' => $printedBy,
        'pageNumber' => 1,
        'pageCount' => 1,
    ])
</body>

</html>