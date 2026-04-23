<?php

declare(strict_types=1);

namespace App\Infrastructure\Export;

use App\Domain\Ebay\EbayRow;
use PhpOffice\PhpSpreadsheet\IOFactory;

final class CsvExporter
{
    /** @param array<int, EbayRow> $rows */
    public function export(string $templatePath, string $outputPath, array $rows): void
    {
        $spreadsheet = IOFactory::load($templatePath);
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [];
        $highestCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn());
        for ($col = 1; $col <= $highestCol; $col++) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1';
            $headers[] = (string) $sheet->getCell($cell)->getValue();
        }

        $dir = dirname($outputPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $handle = fopen($outputPath, 'wb');
        fputcsv($handle, $headers, ';');
        foreach ($rows as $row) {
            $values = $row->values();
            $line = [];
            foreach ($headers as $header) {
                $line[] = $values[$header] ?? '';
            }
            fputcsv($handle, $line, ';');
        }
        fclose($handle);
    }
}
