<?php

require_once __DIR__ . '/SpreadsheetReader.php';

/**
 * Turns an uploaded inventory spreadsheet into validated rows HR can preview before importing.
 * Columns are matched by header name (any order, a few common spellings accepted).
 */
class UniformImportService
{
    public const MAX_ROWS = 500;

    /** Template / header names, in the order the template uses. */
    public const TEMPLATE_HEADERS = ['Item Type', 'Size', 'Color', 'Quantity', 'Reorder Level', 'Cost per Unit', 'Supplier'];

    private const ALIASES = [
        'type'     => ['item type', 'item', 'type', 'uniform type', 'item name', 'name', 'uniform'],
        'size'     => ['size'],
        'color'    => ['color', 'colour'],
        'quantity' => ['quantity', 'qty', 'in stock', 'stock', 'quantity in stock', 'count'],
        'reorder'  => ['reorder level', 'reorder at', 'reorder', 'reorder point'],
        'cost'     => ['cost per unit', 'cost', 'unit cost', 'price', 'unit price'],
        'supplier' => ['supplier', 'vendor'],
    ];

    /**
     * @param callable(string, string, string): ?array $findExisting looks up an item by type/size/color
     * @return array{rows: array, errors: array<int, string>, total: int}
     */
    public static function parse(string $path, string $originalName, callable $findExisting): array
    {
        $sheet = SpreadsheetReader::read($path, $originalName);

        // The header row is the first row naming at least the item type, size and quantity.
        $map = null;
        $headerIndex = null;
        foreach (array_slice($sheet, 0, 10, true) as $i => $row) {
            $candidate = self::mapHeaders($row);
            if (isset($candidate['type'], $candidate['size'], $candidate['quantity'])) {
                $map = $candidate;
                $headerIndex = $i;
                break;
            }
        }
        if ($map === null) {
            throw new RuntimeException('Could not find the header row. The first row must have the columns: '
                . implode(', ', self::TEMPLATE_HEADERS) . ' (Item Type, Size and Quantity are required). Download the template to start.');
        }

        $rows = [];
        $errors = [];
        $seen = [];
        $total = 0;
        foreach ($sheet as $i => $line) {
            if ($i <= $headerIndex) {
                continue;
            }
            $get = static fn(string $key) => isset($map[$key]) ? trim((string) ($line[$map[$key]] ?? '')) : '';
            if (implode('', array_map('trim', $line)) === '') {
                continue; // blank line
            }
            $total++;
            $excelRow = $i + 1;
            if ($total > self::MAX_ROWS) {
                $errors[$excelRow] = 'Too many rows; import at most ' . self::MAX_ROWS . ' at a time.';
                break;
            }

            $type = preg_replace('/\s+/', ' ', $get('type'));
            $size = preg_replace('/\s+/', ' ', $get('size'));
            $color = preg_replace('/\s+/', ' ', $get('color'));
            $problems = [];
            if ($type === '') {
                $problems[] = 'Item Type is empty';
            }
            if ($size === '') {
                $problems[] = 'Size is empty';
            }
            $qtyRaw = str_replace(',', '', $get('quantity'));
            if ($qtyRaw === '' || !preg_match('/^\d+(\.0+)?$/', $qtyRaw)) {
                $problems[] = 'Quantity must be a whole number (0 or more)';
            }
            $reorderRaw = str_replace(',', '', $get('reorder'));
            if ($reorderRaw !== '' && !preg_match('/^\d+(\.0+)?$/', $reorderRaw)) {
                $problems[] = 'Reorder Level must be a whole number';
            }
            $costRaw = str_replace([',', '₱', 'PHP', 'php', ' '], '', $get('cost'));
            if ($costRaw !== '' && (!is_numeric($costRaw) || (float) $costRaw < 0)) {
                $problems[] = 'Cost per Unit must be a number';
            }
            if (mb_strlen($type) > 100 || mb_strlen($size) > 20 || mb_strlen($color) > 50) {
                $problems[] = 'Item Type (100), Size (20) or Color (50) is too long';
            }

            $key = mb_strtolower($type . '|' . $size . '|' . $color);
            if (isset($seen[$key]) && $type !== '' && $size !== '') {
                $problems[] = 'Same item already on row ' . $seen[$key] . '; combine them into one row';
            }
            $seen[$key] = $seen[$key] ?? $excelRow;

            if ($problems) {
                $errors[$excelRow] = implode('; ', $problems);
                continue;
            }

            $existing = $findExisting($type, $size, $color);
            $supplier = $get('supplier');
            $rows[] = [
                'excel_row' => $excelRow,
                'type'      => $type,
                'size'      => $size,
                'color'     => $color,
                'quantity'  => (int) $qtyRaw,
                'reorder'   => $reorderRaw === '' ? null : (int) $reorderRaw,
                'cost'      => $costRaw === '' ? null : round((float) $costRaw, 2),
                'supplier'  => $supplier === '' ? null : mb_substr($supplier, 0, 150),
                'existing_id'     => $existing ? (int) $existing['uniform_id'] : null,
                'existing_stock'  => $existing ? (int) $existing['quantity_in_stock'] : null,
                'existing_status' => $existing ? (string) $existing['status'] : null,
            ];
        }

        if ($total === 0) {
            throw new RuntimeException('The file has a header row but no item rows.');
        }

        return ['rows' => $rows, 'errors' => $errors, 'total' => $total];
    }

    /** @return array<string, int> field => column index */
    private static function mapHeaders(array $row): array
    {
        $map = [];
        foreach ($row as $index => $label) {
            $normalized = strtolower(trim(preg_replace('/[^a-z ]+/i', ' ', (string) $label)));
            $normalized = preg_replace('/\s+/', ' ', $normalized);
            foreach (self::ALIASES as $field => $aliases) {
                if (!isset($map[$field]) && in_array($normalized, $aliases, true)) {
                    $map[$field] = $index;
                }
            }
        }
        return $map;
    }

    /** CSV template with two example rows (opens directly in Excel). */
    public static function templateCsv(): string
    {
        $lines = [
            self::TEMPLATE_HEADERS,
            ['New Polo Shirt', 'M', 'Blue', '20', '10', '350.00', 'ABC Garments'],
            ['ID Badge', 'One Size', 'White/Blue', '50', '20', '', ''],
        ];
        $out = fopen('php://temp', 'r+');
        foreach ($lines as $line) {
            fputcsv($out, $line, ',', '"', '\\');
        }
        rewind($out);
        return "\xEF\xBB\xBF" . stream_get_contents($out);
    }
}
