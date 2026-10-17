<?php

require_once __DIR__ . '/SpreadsheetReader.php';

/**
 * Import of the IT asset inventory sheet (e.g. the IT Inventory Google Sheet saved as .xlsx / .csv).
 * Rows are matched to existing assets by Asset Number, then by Serial Number:
 *  - match found: Item Info, Serial, Year Purchased and (for unassigned items) Branch are updated;
 *  - no match:    a new UNASSIGNED item is created in the Model (group), with a new asset number.
 * Assignment / status changes are not imported; those go through Assign, Transfer and Return.
 */
class AssetImportService
{
    public const MAX_ROWS = 1000;

    public const TEMPLATE_HEADERS = ['Category', 'Model', 'Item Info', 'Serial Number', 'Year Purchased', 'Branch', 'Asset Number'];

    private const ALIASES = [
        'category' => ['category', 'asset category', 'type', 'asset type'],
        'model'    => ['model', 'group', 'asset group', 'item group', 'model name', 'unit'],
        'info'     => ['item info', 'item', 'description', 'specs', 'item description', 'details', 'brand model', 'brand'],
        'serial'   => ['serial number', 'serial', 'serial no', 'sn', 's n'],
        'year'     => ['year purchased', 'year', 'purchase year', 'date purchased', 'purchased'],
        'branch'   => ['branch', 'branch code', 'location', 'site'],
        'asset'    => ['asset number', 'asset no', 'asset', 'asset code', 'tag', 'asset tag'],
    ];

    /**
     * @param array $lookups ['groups' => name => row, 'categories' => name => row, 'branches' => key => id,
     *                        'byAsset' => assetNumber => row, 'bySerial' => serial => row]
     * @return array{rows: array, errors: array<int, string>, total: int}
     */
    public static function parse(string $path, string $originalName, array $lookups): array
    {
        $sheet = SpreadsheetReader::read($path, $originalName);

        $map = null;
        $headerIndex = null;
        foreach (array_slice($sheet, 0, 10, true) as $i => $row) {
            $candidate = self::mapHeaders($row);
            if (isset($candidate['model'], $candidate['info'])) {
                $map = $candidate;
                $headerIndex = $i;
                break;
            }
        }
        if ($map === null) {
            throw new RuntimeException('Could not find the header row. It needs at least the columns Model and Item Info ('
                . implode(', ', self::TEMPLATE_HEADERS) . '). Download the template to start.');
        }

        $rows = [];
        $errors = [];
        $total = 0;
        $seenSerial = [];
        foreach ($sheet as $i => $line) {
            if ($i <= $headerIndex || implode('', array_map('trim', $line)) === '') {
                continue;
            }
            $total++;
            $excelRow = $i + 1;
            if ($total > self::MAX_ROWS) {
                $errors[$excelRow] = 'Too many rows; import at most ' . self::MAX_ROWS . ' at a time.';
                break;
            }
            $get = static fn(string $key) => isset($map[$key]) ? trim(preg_replace('/\s+/', ' ', (string) ($line[$map[$key]] ?? ''))) : '';

            $category = $get('category');
            $model = $get('model');
            $info = $get('info');
            $serial = $get('serial');
            $assetNo = strtoupper($get('asset'));
            $branchRaw = $get('branch');
            $yearRaw = $get('year');
            $problems = [];

            $year = '';
            if ($yearRaw !== '') {
                if (preg_match('/^\d{5}$/', $yearRaw)) {
                    $year = (new DateTime('1899-12-30'))->modify('+' . (int) $yearRaw . ' days')->format('Y'); // Excel date
                } elseif (preg_match('/\b(19[89]\d|20\d\d)\b/', $yearRaw, $m)) {
                    $year = $m[1];
                } else {
                    $problems[] = 'Year Purchased "' . $yearRaw . '" is not a year';
                }
                if ($year !== '' && (int) $year > (int) date('Y')) {
                    $problems[] = 'Year Purchased is in the future';
                }
            }

            $branchId = null;
            if ($branchRaw !== '') {
                $branchId = $lookups['branches'][mb_strtolower($branchRaw)] ?? null;
                if ($branchId === null) {
                    $problems[] = 'Branch "' . $branchRaw . '" not found (use the branch code or name)';
                }
            }

            $serialKey = self::serialKey($serial);
            $existing = null;
            if ($assetNo !== '') {
                $existing = $lookups['byAsset'][$assetNo] ?? null;
            }
            if ($existing === null && $serialKey !== '') {
                $existing = $lookups['bySerial'][$serialKey] ?? null;
            }
            if ($serialKey !== '') {
                if (isset($seenSerial[$serialKey])) {
                    $problems[] = 'Same serial number as row ' . $seenSerial[$serialKey];
                }
                $seenSerial[$serialKey] = $seenSerial[$serialKey] ?? $excelRow;
            }

            $groupId = null;
            $createGroup = false;
            if ($existing === null) {
                if ($info === '') {
                    $problems[] = 'Item Info is empty';
                }
                if ($model === '') {
                    $problems[] = 'Model is empty';
                } else {
                    $group = $lookups['groups'][mb_strtolower($model)] ?? null;
                    if ($group) {
                        $groupId = (int) $group['group_id'];
                    } elseif ($category !== '' && isset($lookups['categories'][mb_strtolower($category)])) {
                        $createGroup = true; // new Model under an existing Category
                    } else {
                        $problems[] = 'Model "' . $model . '" not found' . ($category !== ''
                            ? ' and Category "' . $category . '" not found' : '; add a Category column to create it');
                    }
                }
                if ($assetNo !== '') {
                    $problems[] = 'Asset Number ' . $assetNo . ' not found (leave it empty for new items; a number is assigned)';
                }
            }
            if (mb_strlen($info) > 150 || mb_strlen($serial) > 100 || mb_strlen($model) > 150) {
                $problems[] = 'Item Info (150), Serial (100) or Model (150) is too long';
            }

            if ($problems) {
                $errors[$excelRow] = implode('; ', $problems);
                continue;
            }

            $changes = [];
            if ($existing) {
                if ($info !== '' && $info !== (string) $existing['itemInfo']) {
                    $changes['itemInfo'] = $info;
                }
                if ($serial !== '' && $serial !== (string) $existing['serialNumber']) {
                    $changes['serialNumber'] = $serial;
                }
                if ($year !== '' && $year !== trim((string) $existing['year_purchased'])) {
                    $changes['year_purchased'] = $year;
                }
                if ($branchId !== null && (int) $existing['branch_id'] !== $branchId) {
                    if (strtoupper((string) $existing['status']) === 'ASSIGNED') {
                        $errors[$excelRow] = 'Branch differs but ' . $existing['assetNumber'] . ' is assigned; use Transfer to move it'
                            . ($changes ? ' (other changes on this row were skipped)' : '');
                        continue;
                    }
                    $changes['branch_id'] = $branchId;
                }
            }

            $rows[] = [
                'excel_row'    => $excelRow,
                'action'       => $existing ? ($changes ? 'update' : 'same') : 'new',
                'inventory_id' => $existing ? (int) $existing['inventory_id'] : null,
                'asset_number' => $existing ? (string) $existing['assetNumber'] : '',
                'category'     => $category,
                'model'        => $model !== '' ? $model : ($existing['groupName'] ?? ''),
                'group_id'     => $groupId,
                'create_group' => $createGroup,
                'info'         => $info !== '' ? $info : (string) ($existing['itemInfo'] ?? ''),
                'serial'       => $serial,
                'year'         => $year,
                'branch_id'    => $branchId,
                'branch'       => $branchRaw,
                'changes'      => $changes,
            ];
        }

        if ($total === 0) {
            throw new RuntimeException('The file has a header row but no rows.');
        }
        return ['rows' => $rows, 'errors' => $errors, 'total' => $total];
    }

    /** Serial numbers compared without case / spaces; placeholders such as N/A do not count. */
    public static function serialKey(string $serial): string
    {
        $key = strtoupper(preg_replace('/[\s\-]+/', '', $serial));
        return in_array($key, ['', 'NA', 'N/A', 'NONE', 'NOSERIAL', '0', 'TBA', '-'], true) ? '' : $key;
    }

    public static function templateCsv(): string
    {
        $lines = [
            self::TEMPLATE_HEADERS,
            ['IT Assets', 'Laptop', 'Lenovo ThinkPad E14 Gen 5, i5, 16GB RAM, 512GB SSD', 'PF4ABC12', '2024', 'HO', ''],
            ['CCTV', 'CCTV Camera', 'Hikvision 4MP dome camera, front gate', 'K12345678', '2019', 'BKL', ''],
        ];
        $out = fopen('php://temp', 'r+');
        foreach ($lines as $line) {
            fputcsv($out, $line, ',', '"', '\\');
        }
        rewind($out);
        return "\xEF\xBB\xBF" . stream_get_contents($out);
    }

    private static function mapHeaders(array $row): array
    {
        $map = [];
        foreach ($row as $index => $label) {
            $normalized = strtolower(trim(preg_replace('/[^a-z]+/i', ' ', (string) $label)));
            foreach (self::ALIASES as $field => $aliases) {
                if (!isset($map[$field]) && in_array($normalized, $aliases, true)) {
                    $map[$field] = $index;
                }
            }
        }
        return $map;
    }
}
