<?php

/**
 * Excel export using SpreadsheetML (opens in Microsoft Excel without extra PHP extensions).
 */
class ExcelExportService
{
    public function download(array $headers, array $rows, string $filename, string $sheetName = 'Report'): void
    {
        if (!preg_match('/\.xls(x)?$/i', $filename)) {
            $filename .= '.xls';
        }

        $content = $this->buildSpreadsheet($headers, $rows, $sheetName);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
        header('Content-Length: ' . strlen($content));
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        echo $content;
        exit;
    }

    private function buildSpreadsheet(array $headers, array $rows, string $sheetName): string
    {
        // Excel sheet names: max 31 chars, no : \ / ? * [ ]
        $sheetName = substr(preg_replace('#[:\\\\/?*\[\]]#', ' ', $sheetName) ?: 'Report', 0, 31);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
            . 'xmlns:o="urn:schemas-microsoft-com:office:office" '
            . 'xmlns:x="urn:schemas-microsoft-com:office:excel" '
            . 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $xml .= '<Styles><Style ss:ID="hdr"><Font ss:Bold="1"/><Interior ss:Color="#EEF1FB" ss:Pattern="Solid"/></Style></Styles>' . "\n";
        $xml .= '<Worksheet ss:Name="' . htmlspecialchars($sheetName, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '"><Table>' . "\n";

        $xml .= '<Row>';
        foreach ($headers as $header) {
            $xml .= '<Cell ss:StyleID="hdr"><Data ss:Type="String">'
                . htmlspecialchars((string) $header, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</Data></Cell>';
        }
        $xml .= '</Row>' . "\n";

        foreach ($rows as $row) {
            $xml .= '<Row>';
            foreach ((is_array($row) ? array_values($row) : [$row]) as $cell) {
                // Real numbers (int/float) are written as numbers so Excel can sum and sort them.
                if (is_int($cell) || is_float($cell)) {
                    $xml .= '<Cell><Data ss:Type="Number">' . $cell . '</Data></Cell>';
                    continue;
                }
                $value = htmlspecialchars((string) $cell, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml .= '<Cell><Data ss:Type="String">' . $value . '</Data></Cell>';
            }
            $xml .= '</Row>' . "\n";
        }

        $xml .= '</Table></Worksheet></Workbook>';

        return "\xEF\xBB\xBF" . $xml;
    }
}
