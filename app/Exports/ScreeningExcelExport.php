<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

class ScreeningExcelExport
{
    public function create(Collection $results): string
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Ekstensi PHP ZIP diperlukan untuk membuat file XLSX.');
        }

        $temporaryPath = tempnam(sys_get_temp_dir(), 'footscreen-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Tidak dapat membuat file ekspor sementara.');
        }

        $xlsxPath = $temporaryPath . '.xlsx';
        rename($temporaryPath, $xlsxPath);

        $zip = new ZipArchive();
        if ($zip->open($xlsxPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($xlsxPath);
            throw new RuntimeException('Tidak dapat membuat workbook XLSX.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypesXml());
        $zip->addFromString('_rels/.rels', $this->rootRelationshipsXml());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationshipsXml());
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->worksheetXml($results));
        $zip->close();

        return $xlsxPath;
    }

    private function worksheetXml(Collection $results): string
    {
        $rows = [];
        $rows[] = $this->rowXml(1, [
            ['A1', 'FOOTSCREEN PRO', 1],
            ['B1', '', 1],
            ['C1', '', 1],
            ['D1', '', 1],
            ['E1', '', 1],
        ], 36);
        $rows[] = $this->rowXml(2, [
            ['A2', 'Template Data Screening Fisioterapi Sepak Bola', 2],
            ['B2', '', 2],
            ['C2', '', 2],
            ['D2', '', 2],
            ['E2', '', 2],
        ], 27);
        $rows[] = $this->rowXml(3, [], 8);

        $headings = ['ID', 'Nama Atlet', 'Jenis Tes', 'Rating', 'Tanggal Dibuat'];
        $headingCells = [];
        foreach ($headings as $index => $heading) {
            $headingCells[] = [$this->cellReference($index, 4), $heading, 3];
        }
        $rows[] = $this->rowXml(4, $headingCells, 25);

        $rowNumber = 5;
        foreach ($results as $index => $result) {
            $rowStyle = $index % 2 === 0 ? 4 : 5;
            $rating = $this->ratingFor($result);
            $cells = [
                ['A' . $rowNumber, (string) $result->id, $rowStyle, 'n'],
                ['B' . $rowNumber, (string) $result->name, $rowStyle],
                ['C' . $rowNumber, (string) $result->test_type, $rowStyle],
                ['D' . $rowNumber, $rating, $this->ratingStyle($rating)],
                ['E' . $rowNumber, $result->created_at?->format('d/m/Y H:i') ?? '', $rowStyle],
            ];
            $rows[] = $this->rowXml($rowNumber, $cells, 26);
            $rowNumber++;
        }

        $lastRow = max(4, $rowNumber - 1);
        $rowXml = implode('', $rows);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<dimension ref="A1:E' . $lastRow . '"/>'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="4" topLeftCell="A5" activePane="bottomLeft" state="frozen"/><selection pane="bottomLeft" activeCell="A5" sqref="A5"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="21"/>'
            . '<cols><col min="1" max="1" width="9" customWidth="1"/><col min="2" max="2" width="29" customWidth="1"/><col min="3" max="3" width="24" customWidth="1"/><col min="4" max="4" width="18" customWidth="1"/><col min="5" max="5" width="23" customWidth="1"/></cols>'
            . '<sheetData>' . $rowXml . '</sheetData>'
            . '<autoFilter ref="A4:E' . $lastRow . '"/>'
            . '<mergeCells count="2"><mergeCell ref="A1:E1"/><mergeCell ref="A2:E2"/></mergeCells>'
            . '<pageMargins left="0.25" right="0.25" top="0.5" bottom="0.5" header="0.2" footer="0.2"/>'
            . '</worksheet>';
    }

    private function rowXml(int $rowNumber, array $cells, int $height): string
    {
        $xmlCells = '';
        foreach ($cells as $cell) {
            [$reference, $value, $style, $type] = array_pad($cell, 4, 'inlineStr');
            $styleAttribute = ' s="' . $style . '"';

            if ($type === 'n') {
                $xmlCells .= '<c r="' . $reference . '"' . $styleAttribute . ' t="n"><v>' . (int) $value . '</v></c>';
            } elseif ($value === '') {
                $xmlCells .= '<c r="' . $reference . '"' . $styleAttribute . '/>';
            } else {
                $xmlCells .= '<c r="' . $reference . '"' . $styleAttribute . ' t="inlineStr"><is><t xml:space="preserve">'
                    . $this->xmlEscape((string) $value)
                    . '</t></is></c>';
            }
        }

        return '<row r="' . $rowNumber . '" ht="' . $height . '" customHeight="1">' . $xmlCells . '</row>';
    }

    private function cellReference(int $columnIndex, int $rowNumber): string
    {
        return chr(65 + $columnIndex) . $rowNumber;
    }

    private function ratingFor(object $result): string
    {
        return (string) ($result->rating
            ?? $result->hex_rating
            ?? $result->ill_rating
            ?? $result->pushup_rating
            ?? $result->situp_rating
            ?? '-');
    }

    private function ratingStyle(string $rating): int
    {
        return match (mb_strtolower(trim($rating))) {
            'good', 'excellent' => 6,
            'baik', 'average', 'cukup' => 7,
            'elite', 'optimal', 'baik sekali' => 8,
            'below', 'poor', 'kurang', 'kurang sekali' => 9,
            default => 10,
        };
    }

    private function xmlEscape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT, 'UTF-8');
    }

    private function contentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
    }

    private function rootRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<bookViews><workbookView/></bookViews><sheets><sheet name="Data Screening" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbookRelationshipsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
    }

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="6">'
            . '<font><sz val="11"/><color rgb="FF17231E"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="20"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><sz val="11"/><color rgb="FFBDEBD0"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FF1D4ED8"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FF92400E"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="8">'
            . '<fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF063B2A"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF13B76B"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFE8F0EC"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFDCEBFF"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFFFF3C4"/><bgColor indexed="64"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFD9FBE8"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFD5DED9"/></left><right style="thin"><color rgb="FFD5DED9"/></right><top style="thin"><color rgb="FFD5DED9"/></top><bottom style="thin"><color rgb="FFD5DED9"/></bottom><diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="11">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="5" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="5" fillId="6" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="7" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="4" borderId="1" xfId="0" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }
}
