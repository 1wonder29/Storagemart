<?php

/**
 * Reads the first sheet of an uploaded spreadsheet into rows of strings.
 * Supports .xlsx (Office Open XML), .csv, and the XML-Spreadsheet .xls the system itself exports.
 * Old binary .xls files are rejected with a clear message (save as .xlsx or .csv instead).
 */
class SpreadsheetReader
{
    /** @return array<int, array<int, string>> */
    public static function read(string $path, string $originalName): array
    {
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
        $head = (string) file_get_contents($path, false, null, 0, 512);

        if ($ext === 'csv' || $ext === 'txt') {
            return self::readCsv($path);
        }
        if (substr($head, 0, 2) === 'PK') {
            return self::readXlsx($path);
        }
        if (stripos(ltrim($head, "\xEF\xBB\xBF \r\n\t"), '<?xml') === 0) {
            return self::readXmlSpreadsheet($path);
        }
        throw new RuntimeException('Unsupported file. Please upload an .xlsx or .csv file (in Excel: File > Save As > Excel Workbook).');
    }

    private static function readCsv(string $path): array
    {
        $content = (string) file_get_contents($path);
        $content = preg_replace('/^\xEF\xBB\xBF/', '', $content);
        // Excel in some locales saves with semicolons.
        $firstLine = strtok($content, "\r\n") ?: '';
        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

        $rows = [];
        $handle = fopen('php://temp', 'r+');
        fwrite($handle, $content);
        rewind($handle);
        while (($row = fgetcsv($handle, 0, $delimiter, '"', '\\')) !== false) {
            $rows[] = array_map(static fn($v) => trim((string) $v), $row);
        }
        fclose($handle);
        return $rows;
    }

    private static function readXlsx(string $path): array
    {
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('This server cannot read .xlsx files (ZipArchive missing). Please upload a .csv file.');
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            throw new RuntimeException('The file could not be opened as an Excel workbook.');
        }

        $sheetPath = self::firstSheetPath($zip);
        $sharedStrings = [];
        $ssXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($ssXml !== false) {
            $ss = self::loadXml($ssXml);
            foreach ($ss->si as $si) {
                // Rich text is split into runs (<r><t>); plain strings have a single <t>.
                $text = isset($si->t) ? (string) $si->t : '';
                if (isset($si->r)) {
                    foreach ($si->r as $run) {
                        $text .= (string) $run->t;
                    }
                }
                $sharedStrings[] = $text;
            }
        }

        $sheetXml = $zip->getFromName($sheetPath);
        $zip->close();
        if ($sheetXml === false) {
            throw new RuntimeException('The workbook has no readable sheet.');
        }

        $sheet = self::loadXml($sheetXml);
        $rows = [];
        foreach ($sheet->sheetData->row as $row) {
            $cells = [];
            foreach ($row->c as $c) {
                $col = self::columnIndex((string) $c['r']);
                $type = (string) $c['t'];
                if ($type === 's') {
                    $value = $sharedStrings[(int) $c->v] ?? '';
                } elseif ($type === 'inlineStr') {
                    $value = isset($c->is->t) ? (string) $c->is->t : '';
                    if (isset($c->is->r)) {
                        foreach ($c->is->r as $run) {
                            $value .= (string) $run->t;
                        }
                    }
                } elseif ($type === 'b') {
                    $value = ((string) $c->v) === '1' ? 'TRUE' : 'FALSE';
                } else {
                    $value = (string) $c->v;
                    // Excel stores 12.5 as 12.5 but sometimes as 12.499999999; tidy plain numbers.
                    if (is_numeric($value) && strpos($value, '.') !== false) {
                        $value = rtrim(rtrim(sprintf('%.6F', (float) $value), '0'), '.');
                    }
                }
                $cells[$col] = trim($value);
            }
            if ($cells) {
                $max = max(array_keys($cells));
                $line = array_fill(0, $max + 1, '');
                foreach ($cells as $i => $v) {
                    $line[$i] = $v;
                }
                $rows[] = $line;
            } else {
                $rows[] = [];
            }
        }
        return $rows;
    }

    /** Path of the first worksheet listed in the workbook (not always sheet1.xml). */
    private static function firstSheetPath(ZipArchive $zip): string
    {
        $workbook = $zip->getFromName('xl/workbook.xml');
        $rels = $zip->getFromName('xl/_rels/workbook.xml.rels');
        if ($workbook !== false && $rels !== false) {
            $wb = self::loadXml($workbook);
            $first = $wb->sheets->sheet[0] ?? null;
            if ($first !== null) {
                $rid = (string) $first->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
                foreach (self::loadXml($rels)->Relationship as $rel) {
                    if ((string) $rel['Id'] === $rid) {
                        $target = ltrim((string) $rel['Target'], '/');
                        return strpos($target, 'xl/') === 0 ? $target : 'xl/' . $target;
                    }
                }
            }
        }
        return 'xl/worksheets/sheet1.xml';
    }

    private static function readXmlSpreadsheet(string $path): array
    {
        $xml = self::loadXml((string) file_get_contents($path));
        $xml->registerXPathNamespace('ss', 'urn:schemas-microsoft-com:office:spreadsheet');
        $rows = [];
        foreach ($xml->xpath('//ss:Worksheet[1]/ss:Table/ss:Row') ?: [] as $row) {
            $line = [];
            $i = 0;
            foreach ($row->children('urn:schemas-microsoft-com:office:spreadsheet')->Cell as $cell) {
                $index = (int) $cell->attributes('urn:schemas-microsoft-com:office:spreadsheet')['Index'];
                if ($index > 0) {
                    $i = $index - 1;
                }
                $line[$i] = trim((string) $cell->children('urn:schemas-microsoft-com:office:spreadsheet')->Data);
                $i++;
            }
            $rows[] = $line ? array_replace(array_fill(0, max(array_keys($line)) + 1, ''), $line) : [];
        }
        return $rows;
    }

    private static function loadXml(string $xml): SimpleXMLElement
    {
        $previous = libxml_use_internal_errors(true);
        // LIBXML_NONET: never fetch external resources while parsing an uploaded file.
        $doc = simplexml_load_string($xml, SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        if ($doc === false) {
            throw new RuntimeException('The spreadsheet file is damaged or not a valid Excel file.');
        }
        return $doc;
    }

    /** "C12" -> 2 */
    private static function columnIndex(string $ref): int
    {
        $letters = preg_replace('/\d+/', '', strtoupper($ref));
        $n = 0;
        for ($i = 0, $len = strlen($letters); $i < $len; $i++) {
            $n = $n * 26 + (ord($letters[$i]) - 64);
        }
        return max(0, $n - 1);
    }
}
