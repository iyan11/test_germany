<?php

declare(strict_types=1);

namespace App\Infrastructure\Export;

use App\Domain\Ebay\EbayRow;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class XlsxTemplateExporter
{
    /** @param array<int, EbayRow> $rows */
    public function export(string $templatePath, string $outputPath, array $rows): void
    {
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $headerToIndex = [];
        $maxColumn = Coordinate::columnIndexFromString($sheet->getHighestColumn());
        for ($col = 1; $col <= $maxColumn; $col++) {
            $headerCell = Coordinate::stringFromColumnIndex($col) . '1';
            $header = trim((string) $sheet->getCell($headerCell)->getValue());
            if ($header !== '') {
                $headerToIndex[$header] = $col;
            }
        }

        $rowIndex = 2;
        foreach ($rows as $row) {
            foreach ($row->values() as $field => $value) {
                if (!isset($headerToIndex[$field])) {
                    continue;
                }
                $cell = Coordinate::stringFromColumnIndex($headerToIndex[$field]) . $rowIndex;
                $sheet->setCellValue($cell, $value);
            }
            $rowIndex++;
        }

        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save($outputPath);
    }
}
