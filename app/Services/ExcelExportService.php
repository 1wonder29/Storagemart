<?php

/**
 * Excel export using SpreadsheetML (opens in Microsoft Excel without extra PHP extensions).
 */
class ExcelExportService
{
    public function download(array $headers, array $rows, string $filename): void
    {
        if (!preg_match('/\.xls(x)?$/i', $filename)) {
            $filename .= '.xls';
        }

        $content = $this->buildSpreadsheet($headers, $rows);

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

    private function buildSpreadsheet(array $headers, array $rows): string
    {
        $sheetRows = [$headers];
        foreach ($rows as $row) {
            $sheetRows[] = is_array($row) ? array_values($row) : [$row];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" '
            . 'xmlns:o="urn:schemas-microsoft-com:office:office" '
            . 'xmlns:x="urn:schemas-microsoft-com:office:excel" '
            . 'xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet">' . "\n";
        $xml .= '<Worksheet ss:Name="Tickets"><Table>' . "\n";

        foreach ($sheetRows as $row) {
            $xml .= '<Row>';
            foreach ($row as $cell) {
                $value = htmlspecialchars((string) $cell, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $xml .= '<Cell><Data ss:Type="String">' . $value . '</Data></Cell>';
            }
            $xml .= '</Row>' . "\n";
        }

        $xml .= '</Table></Worksheet></Workbook>';

        return "\xEF\xBB\xBF" . $xml;
    }
}
