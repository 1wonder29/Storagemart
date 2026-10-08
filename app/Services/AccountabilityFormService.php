<?php

require_once __DIR__ . '/../../public/assets/vendor/autoload.php';

/**
 * Employee accountability form: one data source, three outputs (HTML preview, PDF, Word).
 *
 * HR picks what goes on the form before downloading: a preset (full form, items + quantity,
 * name + items) or individual columns / sections. The letterhead and footer images are read
 * from the Word template so the look stays the same.
 */
class AccountabilityFormService
{
    public const COLUMNS = [
        'date_issued'   => 'Date Issued',
        'item'          => 'Items / Supplies / Equipment',
        'quantity'      => 'Qty',
        'issued_by'     => 'Issued By',
        'date_returned' => 'Date Returned',
        'remarks'       => 'Remarks',
    ];

    public const PARTS = [
        'details'   => 'Employee details (department, position, ID)',
        'agreement' => 'Agreement text',
        'signature' => 'Signature line',
    ];

    public const PRESETS = [
        'full'       => ['label' => 'Full form',
                         'columns' => ['date_issued', 'item', 'quantity', 'issued_by', 'date_returned', 'remarks'],
                         'parts' => ['details', 'agreement', 'signature'], 'scope' => 'all'],
        'items_qty'  => ['label' => 'Items + quantity only',
                         'columns' => ['item', 'quantity'], 'parts' => [], 'scope' => 'active'],
        'name_items' => ['label' => 'Name + items only',
                         'columns' => ['item'], 'parts' => [], 'scope' => 'active'],
    ];

    private const AGREEMENT = [
        'By signing this form, I agree to the following: I am responsible for the equipment or property issued to me; I will use it/them in the manner intended; I will be responsible for any damage done (excluding normal wear and tear); upon separation from the Company, I will return the item(s) issued to me in proper working order (excluding normal wear and tear); I will replace any items issued to me that are damaged or lost at my expense; I authorize a payroll deduction to cover the replacement cost of any item issued to me that is not returned for whatever reason, or is not returned in good working order.',
        'I agree to notify the company if any of the items are damaged, destroyed or lost.',
        'If I quit without notice, I agree to still return all company property within 24 hours of my last day. If the property is not returned within the given time period, I understand that the company properties will be considered as stolen properties and will be turned over to the police.',
    ];

    private string $templateFile;

    public function __construct()
    {
        $this->templateFile = __DIR__ . '/../../public/assets/generatePDF/template_accountability.docx';
    }

    /**
     * Normalise request options. A preset fills in columns/parts/scope; explicit
     * choices (preset=custom) are kept as sent, limited to known keys.
     */
    public static function options(array $input): array
    {
        $preset = (string) ($input['preset'] ?? 'full');
        if (isset(self::PRESETS[$preset])) {
            $p = self::PRESETS[$preset];
            return [
                'preset'   => $preset,
                'columns'  => $p['columns'],
                'parts'    => $p['parts'],
                'scope'    => $p['scope'],
                'sections' => ['assets', 'items'],
            ];
        }

        $pick = static fn($values, array $allowed) => array_values(array_intersect($allowed, (array) $values));
        $columns = $pick($input['cols'] ?? [], array_keys(self::COLUMNS));
        $sections = $pick($input['sections'] ?? [], ['assets', 'items']);

        return [
            'preset'   => 'custom',
            'columns'  => $columns ?: ['item'],
            'parts'    => $pick($input['parts'] ?? [], array_keys(self::PARTS)),
            'scope'    => ($input['scope'] ?? 'all') === 'active' ? 'active' : 'all',
            'sections' => $sections ?: ['assets', 'items'],
        ];
    }

    /** Query-string form of options, for links between preview and download. */
    public static function query(array $options): string
    {
        if ($options['preset'] !== 'custom') {
            return http_build_query(['preset' => $options['preset']]);
        }
        return http_build_query([
            'preset' => 'custom',
            'cols' => $options['columns'],
            'parts' => $options['parts'],
            'scope' => $options['scope'],
            'sections' => $options['sections'],
        ]);
    }

    /** IT assets and HR-issued items as one list of form rows. */
    public function rows(array $assets, array $uniforms, array $options): array
    {
        $fmt = static fn($d) => !empty($d) && strtotime((string) $d) ? date('M j, Y', strtotime((string) $d)) : '';
        $rows = [];

        if (in_array('assets', $options['sections'], true)) {
            foreach ($assets as $a) {
                $returned = ($a['accountability_status'] ?? '') === 'RETURNED';
                if ($returned && $options['scope'] === 'active') {
                    continue;
                }
                $name = trim((string) ($a['itemInfo'] ?? '')) ?: trim((string) ($a['assetNumber'] ?? 'Asset'));
                if (!empty($a['serialNumber'])) {
                    $name .= ' (SN: ' . $a['serialNumber'] . ')';
                }
                $rows[] = [
                    'date_issued'   => $fmt($a['dateIssued'] ?? ''),
                    'item'          => $name,
                    'quantity'      => '1',
                    'issued_by'     => (string) ($a['issued_by_display'] ?? 'N/A'),
                    'date_returned' => $fmt($a['dateReturned'] ?? ''),
                    'remarks'       => trim((string) ($a['remarks'] ?? '')),
                ];
            }
        }

        if (in_array('items', $options['sections'], true)) {
            foreach ($uniforms as $u) {
                $returned = !empty($u['date_returned']);
                if ($returned && $options['scope'] === 'active') {
                    continue;
                }
                $remarks = trim((string) ($u['remarks'] ?? ''));
                if (!empty($u['condition_upon_return']) && stripos($remarks, (string) $u['condition_upon_return']) === false) {
                    $remarks = trim('Returned: ' . $u['condition_upon_return'] . '. ' . $remarks);
                }
                $rows[] = [
                    'date_issued'   => $fmt($u['date_issued'] ?? ''),
                    // uniform_type is NULL when the item was later deleted from the inventory list.
                    'item'          => trim((string) ($u['uniform_type'] ?? '')) === ''
                        ? 'Item (no longer in inventory)'
                        : trim(implode(' ', array_filter([
                            $u['uniform_type'], $u['size'] ?? '', $u['color'] ?? '',
                        ], static fn($v) => trim((string) $v) !== ''))),
                    'quantity'      => (string) max(0, (int) ($u['quantity_issued'] ?? 1)),
                    'issued_by'     => (string) ($u['issued_by_display'] ?? 'N/A'),
                    'date_returned' => $fmt($u['date_returned'] ?? ''),
                    'remarks'       => $remarks,
                ];
            }
        }

        return $rows;
    }

    public static function employeeName(array $employee): string
    {
        return trim(preg_replace('/\s+/', ' ', ($employee['firstname'] ?? '') . ' '
            . ($employee['middlename'] ?? '') . ' ' . ($employee['lastname'] ?? '')));
    }

    /** Letterhead (header) and address banner (footer) from the Word template. */
    private function templateImages(): array
    {
        $images = ['header' => null, 'footer' => null];
        if (!class_exists('ZipArchive') || !is_file($this->templateFile)) {
            return $images;
        }
        $zip = new \ZipArchive();
        if ($zip->open($this->templateFile) === true) {
            $images['header'] = $zip->getFromName('word/media/image1.png') ?: null;
            $images['footer'] = $zip->getFromName('word/media/image2.png') ?: null;
            $zip->close();
        }
        return $images;
    }

    /** The form as a standalone HTML page (used for the on-screen preview and the PDF). */
    public function html(array $employee, array $rows, array $options): string
    {
        $e = static fn($v) => htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8');
        $img = $this->templateImages();
        $parts = $options['parts'];
        $columns = $options['columns'];

        $html = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>
            @page { margin: 24px 40px 70px; }
            body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 10.5px; color: #1f2a5a; line-height: 1.45; margin: 0; }
            .sheet { background: #fff; padding: 18px 26px 26px; }
            .letterhead { width: 100%; margin-bottom: 10px; }
            h1 { text-align: center; font-size: 15px; letter-spacing: 1px; margin: 6px 0 12px; }
            .name-line { margin: 0 0 10px; font-size: 11.5px; }
            table { width: 100%; border-collapse: collapse; }
            .details td { border: 1px solid #1f3a93; padding: 5px 7px; }
            .details td.k { font-weight: bold; width: 18%; }
            .agreement p { text-align: justify; margin: 9px 0; }
            .items { margin-top: 12px; }
            .items th, .items td { border: 1px solid #1f3a93; padding: 5px 6px; text-align: center; vertical-align: middle; }
            .items th { font-weight: bold; background: #eef1fb; }
            .items td.item { text-align: left; }
            .items td.qty, .items th.qty { width: 7%; }
            .empty { text-align: center; color: #777; padding: 10px; border: 1px dashed #aab; margin-top: 12px; }
            .signature { margin-top: 34px; width: 46%; }
            .signature .line { border-top: 1px solid #1f2a5a; margin-top: 30px; padding-top: 3px; font-size: 10px; }
            .footer { width: 100%; margin-top: 26px; }
        </style></head><body><div class="sheet">';

        if ($img['header']) {
            $html .= '<img class="letterhead" src="data:image/png;base64,' . base64_encode($img['header']) . '">';
        }
        $html .= '<h1>ACCOUNTABILITY FORM</h1>';

        if (in_array('details', $parts, true)) {
            $html .= '<table class="details"><tr><td class="k">Employee Name</td><td>' . $e(self::employeeName($employee))
                . '</td><td class="k">Department</td><td>' . $e($employee['department'] ?? 'N/A') . '</td></tr>'
                . '<tr><td class="k">Position</td><td>' . $e($employee['position'] ?? 'N/A')
                . '</td><td class="k">Employee ID</td><td>' . $e($employee['employee_id'] ?? '') . '</td></tr></table>';
        } else {
            $html .= '<p class="name-line"><strong>Employee:</strong> ' . $e(self::employeeName($employee)) . '</p>';
        }

        if (in_array('agreement', $parts, true)) {
            $html .= '<div class="agreement">';
            foreach (self::AGREEMENT as $paragraph) {
                $html .= '<p>' . $e($paragraph) . '</p>';
            }
            $html .= '</div>';
        }

        if (empty($rows)) {
            $html .= '<div class="empty">No items to list for the selected options.</div>';
        } else {
            $html .= '<table class="items"><thead><tr>';
            foreach ($columns as $col) {
                $html .= '<th' . ($col === 'quantity' ? ' class="qty"' : '') . '>' . $e(self::COLUMNS[$col]) . '</th>';
            }
            $html .= '</tr></thead><tbody>';
            foreach ($rows as $row) {
                $html .= '<tr>';
                foreach ($columns as $col) {
                    $class = $col === 'item' ? ' class="item"' : ($col === 'quantity' ? ' class="qty"' : '');
                    $html .= '<td' . $class . '>' . $e($row[$col]) . '</td>';
                }
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
        }

        if (in_array('signature', $parts, true)) {
            $html .= '<div class="signature"><strong>Received By:</strong><div class="line">Signature Over Printed Name</div></div>';
        }
        if ($img['footer']) {
            $html .= '<img class="footer" src="data:image/png;base64,' . base64_encode($img['footer']) . '">';
        }

        return $html . '</div></body></html>';
    }

    public function pdf(array $employee, array $rows, array $options): string
    {
        require_once __DIR__ . '/../Libraries/dompdf/autoload.php';
        $dompdfOptions = new \Dompdf\Options();
        $dompdfOptions->set('isRemoteEnabled', false);
        $dompdfOptions->set('defaultFont', 'DejaVu Sans');
        $dompdf = new \Dompdf\Dompdf($dompdfOptions);
        $dompdf->loadHtml($this->html($employee, $rows, $options), 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        return $dompdf->output();
    }

    /** Word version built from the same rows and options. Returns the saved file path. */
    public function docx(array $employee, array $rows, array $options, string $outputDir): string
    {
        $word = new \PhpOffice\PhpWord\PhpWord();
        $word->setDefaultFontName('Arial');
        $word->setDefaultFontSize(10);
        $blue = '1F2A5A';
        $section = $word->addSection(['marginTop' => 700, 'marginBottom' => 900, 'marginLeft' => 900, 'marginRight' => 900]);

        $img = $this->templateImages();
        $tmpFiles = [];
        foreach (['header', 'footer'] as $key) {
            if ($img[$key]) {
                $path = rtrim($outputDir, '/') . '/acc_' . $key . '_' . bin2hex(random_bytes(4)) . '.png';
                file_put_contents($path, $img[$key]);
                $tmpFiles[$key] = $path;
            }
        }
        if (isset($tmpFiles['header'])) {
            $section->addHeader()->addImage($tmpFiles['header'], ['width' => 480, 'alignment' => 'center']);
        }
        if (isset($tmpFiles['footer'])) {
            $section->addFooter()->addImage($tmpFiles['footer'], ['width' => 470, 'alignment' => 'center']);
        }

        $section->addText('ACCOUNTABILITY FORM', ['bold' => true, 'size' => 14, 'color' => $blue], ['alignment' => 'center', 'spaceAfter' => 200]);

        $cellBorder = ['borderSize' => 6, 'borderColor' => '1F3A93', 'cellMargin' => 80];
        $parts = $options['parts'];
        if (in_array('details', $parts, true)) {
            $t = $section->addTable($cellBorder);
            $t->addRow();
            $t->addCell(1700)->addText('Employee Name', ['bold' => true]);
            $t->addCell(3200)->addText(self::employeeName($employee));
            $t->addCell(1500)->addText('Department', ['bold' => true]);
            $t->addCell(2600)->addText((string) ($employee['department'] ?? 'N/A'));
            $t->addRow();
            $t->addCell(1700)->addText('Position', ['bold' => true]);
            $t->addCell(3200)->addText((string) ($employee['position'] ?? 'N/A'));
            $t->addCell(1500)->addText('Employee ID', ['bold' => true]);
            $t->addCell(2600)->addText((string) ($employee['employee_id'] ?? ''));
        } else {
            $run = $section->addTextRun(['spaceAfter' => 120]);
            $run->addText('Employee: ', ['bold' => true]);
            $run->addText(self::employeeName($employee));
        }

        if (in_array('agreement', $parts, true)) {
            foreach (self::AGREEMENT as $paragraph) {
                $section->addText($paragraph, ['color' => $blue], ['alignment' => 'both', 'spaceBefore' => 120, 'spaceAfter' => 60]);
            }
        }

        $section->addTextBreak(1);
        if (empty($rows)) {
            $section->addText('No items to list for the selected options.', ['italic' => true], ['alignment' => 'center']);
        } else {
            $widths = ['date_issued' => 1500, 'item' => 3600, 'quantity' => 700, 'issued_by' => 1600, 'date_returned' => 1500, 'remarks' => 1900];
            $total = array_sum(array_intersect_key($widths, array_flip($options['columns'])));
            $scale = $total > 0 ? 9000 / $total : 1;
            $t = $section->addTable($cellBorder);
            $t->addRow(null, ['tblHeader' => true]);
            foreach ($options['columns'] as $col) {
                $t->addCell((int) ($widths[$col] * $scale), ['bgColor' => 'EEF1FB'])
                    ->addText(self::COLUMNS[$col], ['bold' => true], ['alignment' => 'center']);
            }
            foreach ($rows as $row) {
                $t->addRow();
                foreach ($options['columns'] as $col) {
                    $t->addCell((int) ($widths[$col] * $scale))
                        ->addText($row[$col], [], ['alignment' => $col === 'item' ? 'left' : 'center']);
                }
            }
        }

        if (in_array('signature', $parts, true)) {
            $section->addTextBreak(2);
            $section->addText('Received By:', ['bold' => true]);
            $section->addTextBreak(1);
            $section->addText('____________________________________________');
            $section->addText('Signature Over Printed Name', ['size' => 9]);
        }

        $file = rtrim($outputDir, '/') . '/accountability_form_' . preg_replace('/\D/', '', (string) ($employee['employee_id'] ?? ''))
            . '_' . date('YmdHis') . '.docx';
        \PhpOffice\PhpWord\IOFactory::createWriter($word, 'Word2007')->save($file);
        foreach ($tmpFiles as $path) {
            @unlink($path);
        }
        return $file;
    }
}
