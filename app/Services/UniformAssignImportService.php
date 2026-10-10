<?php

require_once __DIR__ . '/SpreadsheetReader.php';

/**
 * Reads an "Assign from Excel" spreadsheet: which employee gets which item and how many.
 * Every row is checked (employee found and active, item found and not discontinued, enough
 * stock for all rows together) and shown to HR as a preview; the valid rows are then sent
 * for approval. Columns are matched by header name, in any order.
 */
class UniformAssignImportService
{
    public const MAX_ROWS = 500;
    public const CONDITIONS = ['GOOD', 'FAIR', 'USED'];

    public const TEMPLATE_HEADERS = ['Employee Email', 'Employee ID', 'Employee Name', 'Item Type', 'Size', 'Color', 'Quantity', 'Condition', 'Remarks'];

    private const ALIASES = [
        'email'     => ['employee email', 'email', 'corporate email', 'company email', 'username', 'email address'],
        'emp_id'    => ['employee id', 'emp id', 'employee no', 'employee number', 'id number', 'id no'],
        'name'      => ['employee name', 'employee', 'name', 'full name'],
        'type'      => ['item type', 'item', 'type', 'uniform type', 'item name', 'uniform'],
        'size'      => ['size'],
        'color'     => ['color', 'colour'],
        'quantity'  => ['quantity', 'qty', 'count', 'quantity issued'],
        'condition' => ['condition', 'condition upon issue'],
        'remarks'   => ['remarks', 'remark', 'notes', 'note'],
    ];

    /**
     * @param callable(string $email, string $employeeId, string $name): array{0: ?array, 1: ?string} $findEmployee
     *        returns [employee row, problem message]
     * @param callable(string $type, string $size, string $color): ?array $findItem
     * @return array{rows: array, errors: array<int, string>, total: int}
     */
    public static function parse(string $path, string $originalName, callable $findEmployee, callable $findItem): array
    {
        $sheet = SpreadsheetReader::read($path, $originalName);

        $map = null;
        $headerIndex = null;
        foreach (array_slice($sheet, 0, 10, true) as $i => $row) {
            $candidate = self::mapHeaders($row);
            if (isset($candidate['type'], $candidate['size'], $candidate['quantity'])
                && (isset($candidate['email']) || isset($candidate['emp_id']) || isset($candidate['name']))) {
                $map = $candidate;
                $headerIndex = $i;
                break;
            }
        }
        if ($map === null) {
            throw new RuntimeException('Could not find the header row. It needs: Employee Email (or Employee ID / Employee Name), '
                . 'Item Type, Size and Quantity. Download the template to start.');
        }

        $rows = [];
        $errors = [];
        $seen = [];
        $total = 0;
        foreach ($sheet as $i => $line) {
            if ($i <= $headerIndex) {
                continue;
            }
            if (implode('', array_map('trim', $line)) === '') {
                continue;
            }
            $get = static fn(string $key) => isset($map[$key]) ? trim(preg_replace('/\s+/', ' ', (string) ($line[$map[$key]] ?? ''))) : '';
            $total++;
            $excelRow = $i + 1;
            if ($total > self::MAX_ROWS) {
                $errors[$excelRow] = 'Too many rows; send at most ' . self::MAX_ROWS . ' at a time.';
                break;
            }

            $problems = [];
            $email = strtolower($get('email'));
            $empId = preg_replace('/\.0+$/', '', $get('emp_id'));
            $name = $get('name');
            $type = $get('type');
            $size = $get('size');
            $color = $get('color');
            $qtyRaw = str_replace(',', '', $get('quantity'));
            $condition = strtoupper($get('condition')) ?: 'GOOD';
            $remarks = mb_substr($get('remarks'), 0, 255);

            if ($email === '' && $empId === '' && $name === '') {
                $problems[] = 'No employee (Employee Email, Employee ID or Employee Name)';
            }
            if ($type === '' || $size === '') {
                $problems[] = 'Item Type and Size are required';
            }
            if (!preg_match('/^\d+(\.0+)?$/', $qtyRaw) || (int) $qtyRaw < 1) {
                $problems[] = 'Quantity must be a whole number of 1 or more';
            }
            if (!in_array($condition, self::CONDITIONS, true)) {
                $problems[] = 'Condition must be Good, Fair or Used';
            }

            $employee = null;
            if ($email !== '' || $empId !== '' || $name !== '') {
                [$employee, $employeeProblem] = $findEmployee($email, $empId, $name);
                if ($employeeProblem) {
                    $problems[] = $employeeProblem;
                }
            }
            $item = null;
            if ($type !== '' && $size !== '') {
                $item = $findItem($type, $size, $color);
                if (!$item) {
                    $problems[] = 'Item not found in the inventory: ' . $type . ' (' . $size . ($color !== '' ? ', ' . $color : '') . ')'
                        . ($color === '' ? ' — add the Color if there are several' : '');
                } elseif (strtoupper((string) $item['status']) !== 'ACTIVE') {
                    $problems[] = 'Item is discontinued: ' . $item['uniform_type'] . ' (' . $item['size'] . ')';
                }
            }
            if ($employee && $item) {
                $key = $employee['employee_id'] . '|' . $item['uniform_id'];
                if (isset($seen[$key])) {
                    $problems[] = 'Same employee and item already on row ' . $seen[$key] . '; combine them into one row';
                }
                $seen[$key] = $seen[$key] ?? $excelRow;
            }

            if ($problems) {
                $errors[$excelRow] = implode('; ', array_unique($problems));
                continue;
            }

            $rows[] = [
                'excel_row'     => $excelRow,
                'employee_id'   => (int) $employee['employee_id'],
                'account_id'    => (int) ($employee['account_id'] ?? 0),
                'usertype'      => (string) ($employee['usertype'] ?? ''),
                'employee_name' => (string) $employee['name'],
                'employee_info' => (string) $employee['info'],
                'uniform_id'    => (int) $item['uniform_id'],
                'item_label'    => trim($item['uniform_type'] . ' (' . $item['size'] . ')' . (trim((string) $item['color']) !== '' ? ' ' . $item['color'] : '')),
                'quantity'      => (int) $qtyRaw,
                'condition'     => $condition,
                'remarks'       => $remarks,
                'stock_now'     => (int) $item['quantity_in_stock'],
            ];
        }

        if ($total === 0) {
            throw new RuntimeException('The file has a header row but no rows.');
        }

        // Enough stock for all rows of the same item together.
        $need = [];
        foreach ($rows as $row) {
            $need[$row['uniform_id']] = ($need[$row['uniform_id']] ?? 0) + $row['quantity'];
        }
        $valid = [];
        foreach ($rows as $row) {
            if ($need[$row['uniform_id']] > $row['stock_now']) {
                $errors[$row['excel_row']] = 'Not enough stock for ' . $row['item_label'] . ': the file needs '
                    . $need[$row['uniform_id']] . ', in stock ' . $row['stock_now'];
                continue;
            }
            $valid[] = $row;
        }
        ksort($errors);
        return ['rows' => $valid, 'errors' => $errors, 'total' => $total];
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
                    break;
                }
            }
        }
        return $map;
    }

    public static function templateCsv(): string
    {
        $lines = [
            self::TEMPLATE_HEADERS,
            ['storagemart.juandelacruz@gmail.com', '', 'Juan Dela Cruz', 'New Polo Shirt', 'M', 'Blue', '2', 'Good', 'New hire kit'],
            ['', '2026261', 'Maria Santos', 'ID Badge', 'One Size', '', '1', '', ''],
        ];
        $out = fopen('php://temp', 'r+');
        foreach ($lines as $line) {
            fputcsv($out, $line, ',', '"', '\\');
        }
        rewind($out);
        return "\xEF\xBB\xBF" . stream_get_contents($out);
    }
}
