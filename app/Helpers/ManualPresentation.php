<?php

/**
 * Troubleshooting guide as slides in the design of the IT Department's Canva manuals
 * ("IT DEPARTMENT MANUALS": StorageMart logo, orange headings, navy text, page numbers,
 * Introduction / Main Issue / Factors / Action / Solution / STEP pages / Note / Thank You).
 *
 * The same slide list is drawn in the browser (/manuals/view) and written as a PowerPoint
 * file (/manuals/pptx). Positions are in pixels of a 1280 x 720 slide (1 px = 9525 EMU).
 * The .pptx is built with a small ZIP writer, so the server needs no extra PHP extension.
 *
 * The steps field of a guide is one entry per line:
 *   plain line             a STEP page (numbering starts again after each section)
 *   # Section title        a section page, e.g. "# 1. Jam Tray1 (Paper Tray)"
 *   ## Heading :: text     an information page with its own heading
 * In any text, " || " starts a new line and lines starting with "- " are bullets.
 */
class ManualPresentation
{
    public const ACTION_DEFAULT = 'Report Immediately to IT Personnel or Report it to the Specified Viber Group (Ops-IT Related Concerns). Troubleshooting can be done by the Facilitators.';

    public const NAVY = '1F3A93';
    public const ORANGE = 'F26522';
    private const EMU = 9525; // per pixel of the 1280 x 720 slide
    private const FONT = 'Century Gothic';

    /**
     * @param callable|null $photoPath maps a stored step photo name to its file (null when missing);
     *                                 without it the photos are listed as saved
     * @return array<int, array> slides in order; each has kind, page and the fields for that kind
     */
    public static function slides(array $manual, ?callable $photoPath = null): array
    {
        $title = trim((string) ($manual['title'] ?? '')) ?: 'IT Troubleshooting Guide';
        $slides = [['kind' => 'cover', 'title' => $title]];

        $intro = trim((string) ($manual['intro'] ?? ''));
        if ($intro === '') {
            $intro = trim((string) ($manual['description'] ?? ''));
        }
        if ($intro !== '') {
            $slides[] = ['kind' => 'text', 'heading' => 'Introduction', 'blocks' => self::blocks($intro)];
        }
        $issue = trim((string) ($manual['main_issue'] ?? ''));
        if ($issue !== '') {
            $slides[] = ['kind' => 'issue', 'heading' => 'Main Issue', 'blocks' => self::blocks($issue)];
        }
        $factors = self::lines((string) ($manual['factors'] ?? ''));
        if ($factors) {
            $slides[] = ['kind' => 'text', 'heading' => 'Factors', 'blocks' => array_map(static function ($line) {
                return ['type' => 'li', 'text' => preg_replace('/^[-•]\s*/u', '', $line)];
            }, $factors)];
        }
        $action = trim((string) ($manual['action_text'] ?? ''));
        if ($action !== '') {
            $slides[] = ['kind' => 'text', 'heading' => 'Action', 'blocks' => self::blocks($action)];
        }
        $solution = trim((string) ($manual['solution'] ?? ''));
        if ($solution !== '') {
            $slides[] = ['kind' => 'text', 'heading' => 'Solution', 'blocks' => self::blocks($solution)];
        }

        $photos = self::stepPhotos($manual);
        if ($photoPath !== null) {
            $photos = array_filter($photos, static function ($file) use ($photoPath) {
                return $photoPath($file) !== null;
            });
        }
        foreach (self::bodyItems((string) ($manual['steps'] ?? '')) as $item) {
            if ($item['type'] === 'section') {
                $slides[] = ['kind' => 'section', 'title' => $item['text']];
            } elseif ($item['type'] === 'page') {
                $slides[] = ['kind' => 'text', 'heading' => $item['heading'], 'blocks' => self::blocks($item['text'])];
            } else {
                $slides[] = [
                    'kind' => 'step',
                    'number' => $item['number'],
                    'index' => $item['index'],
                    'blocks' => self::blocks($item['text']),
                    'photo' => $photos[$item['index']] ?? null,
                ];
            }
        }

        $note = trim((string) ($manual['note'] ?? ''));
        if ($note !== '') {
            $slides[] = ['kind' => 'text', 'heading' => 'Note', 'blocks' => self::blocks($note)];
        }
        $slides[] = ['kind' => 'thanks'];

        foreach ($slides as $i => &$slide) {
            $slide['page'] = sprintf('Page %02d', $i + 1);
            $slide['size'] = self::fontSize($slide);
        }
        unset($slide);
        return $slides;
    }

    /**
     * Steps field as items.
     * @return array<int, array{type: string, text: string, heading?: string, number?: int, index?: int}>
     */
    public static function bodyItems(string $value): array
    {
        $items = [];
        $number = 0;
        $index = 0;
        foreach (preg_split('/\r\n|\r|\n/', $value) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (strpos($line, '##') === 0) {
                $rest = trim(substr($line, 2));
                $parts = explode('::', $rest, 2);
                $items[] = ['type' => 'page', 'heading' => trim($parts[0]), 'text' => trim($parts[1] ?? '')];
            } elseif ($line[0] === '#') {
                $items[] = ['type' => 'section', 'text' => trim(substr($line, 1))];
                $number = 0;
            } else {
                $number++;
                $index++;
                $items[] = ['type' => 'step', 'text' => $line, 'number' => $number, 'index' => $index];
            }
        }
        return $items;
    }

    /** @return string[] the STEP texts only (sections and information pages left out) */
    public static function stepTexts(string $value): array
    {
        $out = [];
        foreach (self::bodyItems($value) as $item) {
            if ($item['type'] === 'step') {
                $out[] = self::plain($item['text']);
            }
        }
        return $out;
    }

    /** Text with " || " line breaks as one line (for chat messages and lists). */
    public static function plain(string $text): string
    {
        return trim(preg_replace('/\s*\|\|\s*/', ' ', $text));
    }

    /** @return array<int, array{type: string, text: string}> paragraphs ("p") and bullets ("li") */
    public static function blocks(string $text): array
    {
        $blocks = [];
        foreach (self::lines(str_replace('||', "\n", $text)) as $line) {
            if (preg_match('/^[-•]\s+(.*)$/u', $line, $m)) {
                $blocks[] = ['type' => 'li', 'text' => $m[1]];
            } else {
                $blocks[] = ['type' => 'p', 'text' => $line];
            }
        }
        return $blocks;
    }

    /** @return string[] */
    private static function lines(string $value): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];
        return array_values(array_filter(array_map('trim', $lines), 'strlen'));
    }

    /** @return array<int, string> step index => stored photo file name */
    public static function stepPhotos(array $manual): array
    {
        $raw = json_decode((string) ($manual['step_photos'] ?? ''), true);
        $out = [];
        if (is_array($raw)) {
            foreach ($raw as $index => $file) {
                if ((int) $index > 0 && is_string($file) && $file !== '') {
                    $out[(int) $index] = $file;
                }
            }
        }
        return $out;
    }

    /** Font size in px (1280-wide slide) that fills the text area of the slide. */
    private static function fontSize(array $slide): int
    {
        $chars = 0;
        foreach ($slide['blocks'] ?? [] as $block) {
            $chars += mb_strlen($block['text']) + 40; // each line also takes room
        }
        $chars = max(0, $chars - 40);
        switch ($slide['kind']) {
            case 'cover':
            case 'section':
                $len = mb_strlen((string) $slide['title']);
                return $len > 70 ? 36 : ($len > 45 ? 42 : 50);
            case 'issue':
                $table = [110 => 30, 200 => 26, 320 => 22, 480 => 19];
                break;
            case 'step':
                if (!empty($slide['photo'])) {
                    $table = [60 => 32, 110 => 28, 180 => 24, 260 => 21, 360 => 18];
                    break;
                }
                $table = [70 => 44, 140 => 38, 240 => 32, 380 => 27, 560 => 23];
                break;
            case 'text':
                $table = [80 => 40, 160 => 34, 260 => 30, 380 => 26, 560 => 22, 800 => 19, 1100 => 17];
                break;
            default:
                return 72;
        }
        foreach ($table as $limit => $size) {
            if ($chars <= $limit) {
                return $size;
            }
        }
        return (int) min($table) - 2;
    }

    // ------------------------------------------------------------------ browser

    /**
     * Gradients used by the illustrations. Put once on the page, outside the slides: a gradient
     * inside a hidden slide would not paint in the visible ones.
     */
    public static function svgDefs(): string
    {
        return '<svg width="0" height="0" style="position:absolute" aria-hidden="true" focusable="false"><defs>'
            . '<linearGradient id="mpFrame" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#F9C2B0"/><stop offset="1" stop-color="#F29A7E"/></linearGradient>'
            . '<linearGradient id="mpScreen" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#1E3A8A"/><stop offset=".55" stop-color="#2563EB"/><stop offset="1" stop-color="#1D4ED8"/></linearGradient>'
            . '</defs></svg>';
    }

    /** The slide illustrations as inline SVG (same drawing as the PowerPoint shapes). */
    public static function monitorSvg(): string
    {
        $icons = '';
        $colors = ['F6C23E', '93C5FD', 'FB923C', 'BFDBFE', 'F59E0B'];
        foreach ($colors as $i => $color) {
            $icons .= '<rect x="33" y="' . (30 + $i * 28) . '" width="18" height="18" rx="4" fill="#' . $color . '"/>';
        }
        $dock = '';
        for ($i = 0; $i < 8; $i++) {
            $dock .= '<circle cx="' . (185 + $i * 18) . '" cy="290" r="5" fill="#' . ($i % 3 === 0 ? 'FDBA74' : 'E0E7FF') . '"/>';
        }
        return '<svg viewBox="0 0 500 430" width="500" height="430" aria-hidden="true">'
            . '<rect x="0" y="0" width="500" height="330" rx="18" fill="url(#mpFrame)"/>'
            . '<rect x="16" y="14" width="468" height="292" rx="6" fill="url(#mpScreen)"/>'
            . '<path d="M16 200 C 120 140, 220 260, 330 190 S 450 150, 484 175 L 484 306 L 16 306 Z" fill="#FFFFFF" fill-opacity=".10"/>'
            . '<path d="M16 120 C 140 60, 230 190, 360 110 S 460 80, 484 95 L 484 140 C 450 120, 380 160, 330 175 C 220 220, 120 110, 16 170 Z" fill="#FFFFFF" fill-opacity=".08"/>'
            . $icons . $dock
            . '<path d="M215 330 L 285 330 L 295 400 L 205 400 Z" fill="#F4A58C"/>'
            . '<rect x="160" y="396" width="180" height="22" rx="8" fill="#F29A7E"/>'
            . '</svg>';
    }

    public static function issueSvg(): string
    {
        return '<svg viewBox="0 0 400 330" width="400" height="330" aria-hidden="true">'
            . '<rect x="0" y="40" width="400" height="240" rx="16" fill="#' . self::NAVY . '"/>'
            . '<rect x="14" y="54" width="372" height="200" rx="6" fill="#EEF2FF"/>'
            . '<rect x="40" y="200" width="200" height="14" rx="7" fill="#C7D2FE"/><rect x="40" y="224" width="140" height="14" rx="7" fill="#C7D2FE"/>'
            . '<rect x="260" y="200" width="100" height="38" rx="8" fill="#F26522" fill-opacity=".85"/>'
            . '<path d="M200 70 L 262 176 L 138 176 Z" fill="#F26522"/>'
            . '<rect x="194" y="104" width="12" height="42" rx="5" fill="#FFFFFF"/><circle cx="200" cy="160" r="7" fill="#FFFFFF"/>'
            . '<path d="M120 60 L 100 40 M 280 60 L 300 40 M 200 40 L 200 14" stroke="#F26522" stroke-width="8" stroke-linecap="round"/>'
            . '<path d="M175 280 L 225 280 L 235 314 L 165 314 Z" fill="#' . self::NAVY . '"/>'
            . '<rect x="130" y="310" width="140" height="16" rx="7" fill="#' . self::NAVY . '"/>'
            . '</svg>';
    }

    // ------------------------------------------------------------------ PowerPoint

    /**
     * The .pptx file contents.
     * @param callable|null $photoPath maps a stored step photo name to a file path (null = no photos)
     */
    public static function pptx(array $manual, ?callable $photoPath = null): string
    {
        $slides = self::slides($manual, $photoPath);
        $count = count($slides);
        $files = [];
        $media = [];

        $logoFile = dirname(__DIR__, 2) . '/public/assets/img/storagemart-logo.png';
        $hasLogo = is_file($logoFile);
        if ($hasLogo) {
            $media['ppt/media/logo.png'] = (string) file_get_contents($logoFile);
        }

        $ns = 'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
            . 'xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"';
        $relNs = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';

        $slideXml = [];
        $slideRels = [];
        foreach ($slides as $i => $slide) {
            $rels = ['rId1' => ['slideLayout', '../slideLayouts/slideLayout1.xml']];
            if ($hasLogo) {
                $rels['rId2'] = ['image', '../media/logo.png'];
            }
            $photo = null;
            if ($slide['kind'] === 'step' && !empty($slide['photo']) && $photoPath) {
                $path = $photoPath((string) $slide['photo']);
                $info = $path ? @getimagesize($path) : false;
                if ($info && in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
                    $ext = $info[2] === IMAGETYPE_PNG ? 'png' : 'jpeg';
                    $name = 'ppt/media/step' . ($i + 1) . '.' . $ext;
                    $media[$name] = (string) file_get_contents($path);
                    $rels['rId3'] = ['image', '../media/step' . ($i + 1) . '.' . $ext];
                    $photo = ['w' => (int) $info[0], 'h' => (int) $info[1]];
                }
            }
            if ($slide['kind'] === 'step' && $photo === null) {
                $slide['photo'] = null;
                $slide['size'] = self::fontSize($slide);
            }
            $slideXml[$i] = self::xml('<p:sld ' . $ns . '><p:cSld>'
                . '<p:bg><p:bgPr><a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill><a:effectLst/></p:bgPr></p:bg>'
                . '<p:spTree>' . self::emptyTree() . self::slideShapes($slide, $hasLogo, $photo, $i === $count - 1) . '</p:spTree></p:cSld>'
                . '<p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr></p:sld>');
            $relXml = '';
            foreach ($rels as $id => [$type, $target]) {
                $relXml .= '<Relationship Id="' . $id . '" Type="' . $relNs . '/' . $type . '" Target="' . $target . '"/>';
            }
            $slideRels[$i] = self::xml('<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $relXml . '</Relationships>');
        }

        $overrides = '';
        for ($i = 1; $i <= $count; $i++) {
            $overrides .= '<Override PartName="/ppt/slides/slide' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>';
        }
        $files['[Content_Types].xml'] = self::xml('<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Default Extension="png" ContentType="image/png"/>'
            . '<Default Extension="jpeg" ContentType="image/jpeg"/>'
            . '<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>'
            . '<Override PartName="/ppt/slideMasters/slideMaster1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideMaster+xml"/>'
            . '<Override PartName="/ppt/slideLayouts/slideLayout1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideLayout+xml"/>'
            . '<Override PartName="/ppt/theme/theme1.xml" ContentType="application/vnd.openxmlformats-officedocument.theme+xml"/>'
            . '<Override PartName="/ppt/presProps.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presProps+xml"/>'
            . '<Override PartName="/ppt/viewProps.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.viewProps+xml"/>'
            . '<Override PartName="/ppt/tableStyles.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.tableStyles+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . $overrides . '</Types>');

        $files['_rels/.rels'] = self::xml('<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="' . $relNs . '/officeDocument" Target="ppt/presentation.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="' . $relNs . '/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>');

        $now = gmdate('Y-m-d\TH:i:s\Z');
        $files['docProps/core.xml'] = self::xml('<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>' . self::esc($slides[0]['title']) . '</dc:title><dc:creator>StorageMart IT Department</dc:creator>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:modified></cp:coreProperties>');
        $files['docProps/app.xml'] = self::xml('<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">'
            . '<Application>StorageMart TMS</Application><Slides>' . $count . '</Slides></Properties>');

        $sldIds = '';
        $presRels = '<Relationship Id="rId1" Type="' . $relNs . '/slideMaster" Target="slideMasters/slideMaster1.xml"/>';
        for ($i = 1; $i <= $count; $i++) {
            $sldIds .= '<p:sldId id="' . (255 + $i) . '" r:id="rId' . ($i + 1) . '"/>';
            $presRels .= '<Relationship Id="rId' . ($i + 1) . '" Type="' . $relNs . '/slide" Target="slides/slide' . $i . '.xml"/>';
        }
        $next = $count + 2;
        $presRels .= '<Relationship Id="rId' . $next . '" Type="' . $relNs . '/presProps" Target="presProps.xml"/>'
            . '<Relationship Id="rId' . ($next + 1) . '" Type="' . $relNs . '/viewProps" Target="viewProps.xml"/>'
            . '<Relationship Id="rId' . ($next + 2) . '" Type="' . $relNs . '/theme" Target="theme/theme1.xml"/>'
            . '<Relationship Id="rId' . ($next + 3) . '" Type="' . $relNs . '/tableStyles" Target="tableStyles.xml"/>';

        $files['ppt/presentation.xml'] = self::xml('<p:presentation ' . $ns . ' saveSubsetFonts="1">'
            . '<p:sldMasterIdLst><p:sldMasterId id="2147483648" r:id="rId1"/></p:sldMasterIdLst>'
            . '<p:sldIdLst>' . $sldIds . '</p:sldIdLst>'
            . '<p:sldSz cx="' . (1280 * self::EMU) . '" cy="' . (720 * self::EMU) . '"/><p:notesSz cx="6858000" cy="9144000"/>'
            . '</p:presentation>');
        $files['ppt/_rels/presentation.xml.rels'] = self::xml('<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $presRels . '</Relationships>');
        $files['ppt/presProps.xml'] = self::xml('<p:presentationPr ' . $ns . '/>');
        $files['ppt/viewProps.xml'] = self::xml('<p:viewPr ' . $ns . '><p:normalViewPr><p:restoredLeft sz="15620"/><p:restoredTop sz="94660"/></p:normalViewPr>'
            . '<p:gridSpacing cx="76200" cy="76200"/></p:viewPr>');
        $files['ppt/tableStyles.xml'] = self::xml('<a:tblStyleLst xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" def="{5C22544A-7EE6-4342-B048-85BDC9FD1C3A}"/>');

        $files['ppt/slideMasters/slideMaster1.xml'] = self::xml('<p:sldMaster ' . $ns . '>'
            . '<p:cSld><p:bg><p:bgRef idx="1001"><a:schemeClr val="bg1"/></p:bgRef></p:bg><p:spTree>' . self::emptyTree() . '</p:spTree></p:cSld>'
            . '<p:clrMap bg1="lt1" tx1="dk1" bg2="lt2" tx2="dk2" accent1="accent1" accent2="accent2" accent3="accent3" accent4="accent4" accent5="accent5" accent6="accent6" hlink="hlink" folHlink="folHlink"/>'
            . '<p:sldLayoutIdLst><p:sldLayoutId id="2147483649" r:id="rId1"/></p:sldLayoutIdLst>'
            . '<p:txStyles><p:titleStyle><a:lvl1pPr><a:defRPr sz="4400"/></a:lvl1pPr></p:titleStyle>'
            . '<p:bodyStyle><a:lvl1pPr><a:defRPr sz="2800"/></a:lvl1pPr></p:bodyStyle>'
            . '<p:otherStyle><a:lvl1pPr><a:defRPr sz="1800"/></a:lvl1pPr></p:otherStyle></p:txStyles></p:sldMaster>');
        $files['ppt/slideMasters/_rels/slideMaster1.xml.rels'] = self::xml('<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="' . $relNs . '/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'
            . '<Relationship Id="rId2" Type="' . $relNs . '/theme" Target="../theme/theme1.xml"/>'
            . '</Relationships>');
        $files['ppt/slideLayouts/slideLayout1.xml'] = self::xml('<p:sldLayout ' . $ns . ' type="blank" preserve="1">'
            . '<p:cSld name="Blank"><p:spTree>' . self::emptyTree() . '</p:spTree></p:cSld><p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr></p:sldLayout>');
        $files['ppt/slideLayouts/_rels/slideLayout1.xml.rels'] = self::xml('<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="' . $relNs . '/slideMaster" Target="../slideMasters/slideMaster1.xml"/>'
            . '</Relationships>');
        $files['ppt/theme/theme1.xml'] = self::theme();

        foreach ($slideXml as $i => $xml) {
            $files['ppt/slides/slide' . ($i + 1) . '.xml'] = $xml;
            $files['ppt/slides/_rels/slide' . ($i + 1) . '.xml.rels'] = $slideRels[$i];
        }
        return self::zip($files + $media);
    }

    private static function emptyTree(): string
    {
        return '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>';
    }

    /** All shapes of one slide (same positions as the browser version). */
    private static function slideShapes(array $slide, bool $hasLogo, ?array $photo, bool $last): string
    {
        $s = new PptxShapes();
        $kind = $slide['kind'];

        // Soft pink circle at the lower right (every page).
        $s->ellipse(965, 330, 420, 420, ['FFF5F7', 'FBD3DA', 'F49A88'], 2700000);
        if (in_array($kind, ['text', 'step'], true) && ($kind === 'text' || $photo === null)) {
            // Orange circle fading to the right, behind the heading.
            $s->ellipse(32, 160, 322, 322, ['F26522', 'F9B394', 'FFFFFF'], 0, [100, 60, 0]);
        }

        // Header: logo, "IT DEPARTMENT MANUALS", menu lines, thin line.
        if ($hasLogo) {
            $s->picture('rId2', 'Logo', 64, 34, 232, 58);
        } else {
            $s->text(64, 40, 300, 50, [[['STORAGEMART', 26, true, self::NAVY]]]);
        }
        $s->text(830, 66, 330, 30, [[['IT DEPARTMENT MANUALS', 12, true, self::NAVY]]], 'r', 'ctr');
        $s->rect(1194, 74, 30, 4, [self::NAVY]);
        $s->rect(1194, 84, 22, 4, [self::NAVY]);
        $s->rect(64, 117, 1060, 2, [self::NAVY, self::NAVY, 'FFFFFF'], 0, [100, 70, 0]);

        $size = (int) $slide['size'];
        switch ($kind) {
            case 'cover':
            case 'section':
                self::monitorShapes($s, 125, 205);
                $s->text(660, 200, 560, 400, [[[mb_strtoupper($slide['title']), $size * 0.75, true, self::NAVY]]], 'ctr', 'ctr', 1.05);
                break;
            case 'issue':
                self::issueShapes($s, 120, 230);
                $s->rect(1132, 208, 72, 6, [self::ORANGE, 'FBD3C2']);
                $s->text(640, 222, 564, 76, [[[$slide['heading'], 42, true, self::ORANGE]]], 'r', 'ctr');
                $s->text(640, 306, 564, 300, self::paragraphs($slide['blocks'], $size), 'r', 't', 1.15);
                break;
            case 'step':
                $s->rect(1132, 208, 72, 6, [self::ORANGE, 'FBD3C2']);
                if ($photo !== null) {
                    $s->rect(64, 160, 770, 432, ['FFFFFF'], 0, null, self::NAVY);
                    [$px, $py, $pw, $ph] = self::fit($photo['w'], $photo['h'], 70, 166, 758, 420);
                    $s->picture('rId3', 'Step photo', $px, $py, $pw, $ph);
                    $s->text(860, 222, 344, 80, [[['STEP ' . $slide['number'], 46, true, self::ORANGE]]], 'r', 'ctr');
                    $s->text(870, 312, 334, 300, self::paragraphs($slide['blocks'], $size), 'just', 't', 1.15);
                } else {
                    $s->text(64, 126, 900, 80, [[['STEP ' . $slide['number'], 42, true, self::ORANGE]]], 'l', 'ctr');
                    $s->text(64, 224, 1140, 390, self::paragraphs($slide['blocks'], $size), 'just', 't', 1.15);
                }
                break;
            case 'text':
                $s->rect(1132, 208, 72, 6, [self::ORANGE, 'FBD3C2']);
                $headingSize = mb_strlen($slide['heading']) > 34 ? 30 : 42;
                $s->text(64, 126, 1060, 80, [[[$slide['heading'], $headingSize, true, self::ORANGE]]], 'l', 'ctr');
                $s->text(64, 224, 1140, 390, self::paragraphs($slide['blocks'], $size), 'just', 't', 1.15);
                break;
            case 'thanks':
                $s->text(240, 250, 800, 84, [[['Thank You.', 54, true, 'FFFFFF', self::ORANGE]]], 'ctr', 'ctr');
                $s->text(240, 332, 800, 84, [[['Thank', 54, true, self::NAVY], [' You.', 54, false, self::NAVY]]], 'ctr', 'ctr');
                $s->text(240, 414, 800, 84, [[['Thank You.', 54, true, 'FFFFFF', self::ORANGE]]], 'ctr', 'ctr');
                break;
        }

        // Footer: page number and the "next" button.
        $s->text(64, 650, 200, 30, [[[$slide['page'], 12, true, self::ORANGE]]], 'l', 'ctr');
        if (!$last) {
            $s->ellipse(1206, 638, 30, 30, [$kind === 'cover' || $kind === 'section' ? self::NAVY : '4C3D99']);
            $s->text(1206, 636, 30, 30, [[['›', 18, true, 'FFFFFF']]], 'ctr', 'ctr');
        }
        return $s->xml();
    }

    /** @return array<int, array> paragraphs for PptxShapes::text */
    private static function paragraphs(array $blocks, int $sizePx): array
    {
        $out = [];
        foreach ($blocks as $block) {
            $run = [[$block['text'], $sizePx * 0.75, false, self::NAVY]];
            $out[] = $block['type'] === 'li' ? ['bullet' => true, 'runs' => $run] : $run;
        }
        return $out;
    }

    private static function monitorShapes(PptxShapes $s, int $x, int $y): void
    {
        $s->rect($x, $y, 500, 330, ['F9C2B0', 'F29A7E'], 2700000, null, null, 'roundRect', 5000);
        $s->rect($x + 16, $y + 14, 468, 292, ['1E3A8A', '2563EB', '1D4ED8'], 2700000);
        $s->rect($x + 16, $y + 190, 468, 116, ['FFFFFF'], 0, [10], null, 'wave');
        foreach (['F6C23E', '93C5FD', 'FB923C', 'BFDBFE', 'F59E0B'] as $i => $color) {
            $s->rect($x + 33, $y + 30 + $i * 28, 18, 18, [$color], 0, null, null, 'roundRect', 20000);
        }
        for ($i = 0; $i < 8; $i++) {
            $s->ellipse($x + 180 + $i * 18, $y + 285, 10, 10, [$i % 3 === 0 ? 'FDBA74' : 'E0E7FF']);
        }
        $s->rect($x + 205, $y + 330, 90, 70, ['F4A58C'], 0, null, null, 'trapezoid');
        $s->rect($x + 160, $y + 396, 180, 22, ['F29A7E'], 0, null, null, 'roundRect', 30000);
    }

    private static function issueShapes(PptxShapes $s, int $x, int $y): void
    {
        $s->rect($x, $y + 40, 400, 240, [self::NAVY], 0, null, null, 'roundRect', 6000);
        $s->rect($x + 14, $y + 54, 372, 200, ['EEF2FF'], 0, null, null, 'roundRect', 2000);
        $s->rect($x + 40, $y + 200, 200, 14, ['C7D2FE'], 0, null, null, 'roundRect', 50000);
        $s->rect($x + 40, $y + 224, 140, 14, ['C7D2FE'], 0, null, null, 'roundRect', 50000);
        $s->rect($x + 260, $y + 200, 100, 38, [self::ORANGE], 0, null, null, 'roundRect', 20000);
        $s->rect($x + 138, $y + 70, 124, 106, [self::ORANGE], 0, null, null, 'triangle');
        $s->text($x + 138, $y + 88, 124, 90, [[['!', 40, true, 'FFFFFF']]], 'ctr', 'ctr');
        $s->rect($x + 165, $y + 280, 70, 34, [self::NAVY], 0, null, null, 'trapezoid');
        $s->rect($x + 130, $y + 310, 140, 16, [self::NAVY], 0, null, null, 'roundRect', 40000);
    }

    /** Fit an image (w x h) inside a box, centered. @return int[] x, y, w, h */
    public static function fit(int $w, int $h, int $boxX, int $boxY, int $boxW, int $boxH): array
    {
        if ($w <= 0 || $h <= 0) {
            return [$boxX, $boxY, $boxW, $boxH];
        }
        $scale = min($boxW / $w, $boxH / $h);
        $fw = (int) round($w * $scale);
        $fh = (int) round($h * $scale);
        return [$boxX + (int) (($boxW - $fw) / 2), $boxY + (int) (($boxH - $fh) / 2), $fw, $fh];
    }

    private static function theme(): string
    {
        $fill = '<a:solidFill><a:schemeClr val="phClr"/></a:solidFill>';
        $line = '<a:ln w="9525" cap="flat" cmpd="sng" algn="ctr"><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:prstDash val="solid"/></a:ln>';
        $effect = '<a:effectStyle><a:effectLst/></a:effectStyle>';
        return self::xml('<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="StorageMart IT Manuals">'
            . '<a:themeElements><a:clrScheme name="StorageMart">'
            . '<a:dk1><a:srgbClr val="1F3A93"/></a:dk1><a:lt1><a:srgbClr val="FFFFFF"/></a:lt1>'
            . '<a:dk2><a:srgbClr val="1F3A93"/></a:dk2><a:lt2><a:srgbClr val="FDE8EC"/></a:lt2>'
            . '<a:accent1><a:srgbClr val="F26522"/></a:accent1><a:accent2><a:srgbClr val="1F3A93"/></a:accent2>'
            . '<a:accent3><a:srgbClr val="F49A88"/></a:accent3><a:accent4><a:srgbClr val="4C3D99"/></a:accent4>'
            . '<a:accent5><a:srgbClr val="F6C23E"/></a:accent5><a:accent6><a:srgbClr val="2563EB"/></a:accent6>'
            . '<a:hlink><a:srgbClr val="2563EB"/></a:hlink><a:folHlink><a:srgbClr val="4C3D99"/></a:folHlink></a:clrScheme>'
            . '<a:fontScheme name="StorageMart"><a:majorFont><a:latin typeface="' . self::FONT . '"/><a:ea typeface=""/><a:cs typeface=""/></a:majorFont>'
            . '<a:minorFont><a:latin typeface="' . self::FONT . '"/><a:ea typeface=""/><a:cs typeface=""/></a:minorFont></a:fontScheme>'
            . '<a:fmtScheme name="StorageMart"><a:fillStyleLst>' . $fill . $fill . $fill . '</a:fillStyleLst>'
            . '<a:lnStyleLst>' . $line . $line . $line . '</a:lnStyleLst>'
            . '<a:effectStyleLst>' . $effect . $effect . $effect . '</a:effectStyleLst>'
            . '<a:bgFillStyleLst>' . $fill . $fill . $fill . '</a:bgFillStyleLst></a:fmtScheme></a:themeElements>'
            . '<a:objectDefaults/><a:extraClrSchemeLst/></a:theme>');
    }

    public static function xml(string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\r\n" . $body;
    }

    public static function esc(string $text): string
    {
        return htmlspecialchars($text, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    /** Minimal ZIP (stored entries) so no zip extension is needed on the server. */
    private static function zip(array $files): string
    {
        $data = '';
        $central = '';
        $offset = 0;
        $time = 0; // 00:00:00
        $date = ((2026 - 1980) << 9) | (1 << 5) | 1;
        foreach ($files as $name => $content) {
            $crc = crc32($content);
            $size = strlen($content);
            $header = pack('VvvvvvVVVvv', 0x04034b50, 20, 0, 0, $time, $date, $crc, $size, $size, strlen($name), 0);
            $central .= pack('VvvvvvvVVVvvvvvVV', 0x02014b50, 20, 20, 0, 0, $time, $date, $crc, $size, $size,
                strlen($name), 0, 0, 0, 0, 0, $offset) . $name;
            $data .= $header . $name . $content;
            $offset += strlen($header) + strlen($name) + $size;
        }
        return $data . $central . pack('VvvvvVVv', 0x06054b50, 0, 0, count($files), count($files), strlen($central), $offset, 0);
    }
}

/** Builds the shape XML of one PowerPoint slide; positions in px of a 1280 x 720 slide. */
class PptxShapes
{
    private const EMU = 9525;
    private const FONT = 'Century Gothic';
    private $id = 2;
    private $out = '';

    public function xml(): string
    {
        return $this->out;
    }

    /**
     * Rectangle-like shape with a solid (one color) or gradient fill.
     * @param string[] $colors
     * @param int[]|null $alphas percent opacity per color (100 = solid)
     */
    public function rect(int $x, int $y, int $w, int $h, array $colors, int $angle = 0, ?array $alphas = null,
                         ?string $outline = null, string $geom = 'rect', ?int $adj = null): void
    {
        $avLst = $adj !== null ? '<a:avLst><a:gd name="adj" fmla="val ' . $adj . '"/></a:avLst>' : '<a:avLst/>';
        $line = $outline ? '<a:ln w="19050"><a:solidFill><a:srgbClr val="' . $outline . '"/></a:solidFill></a:ln>' : '<a:ln><a:noFill/></a:ln>';
        $this->out .= '<p:sp><p:nvSpPr><p:cNvPr id="' . $this->id++ . '" name="Shape ' . $this->id . '"/><p:cNvSpPr/><p:nvPr/></p:nvSpPr>'
            . '<p:spPr>' . $this->xfrm($x, $y, $w, $h) . '<a:prstGeom prst="' . $geom . '">' . $avLst . '</a:prstGeom>'
            . $this->fill($colors, $angle, $alphas) . $line . '</p:spPr></p:sp>';
    }

    public function ellipse(int $x, int $y, int $w, int $h, array $colors, int $angle = 0, ?array $alphas = null): void
    {
        $this->rect($x, $y, $w, $h, $colors, $angle, $alphas, null, 'ellipse');
    }

    public function picture(string $relId, string $name, int $x, int $y, int $w, int $h): void
    {
        $this->out .= '<p:pic><p:nvPicPr><p:cNvPr id="' . $this->id++ . '" name="' . ManualPresentation::esc($name) . '"/>'
            . '<p:cNvPicPr><a:picLocks noChangeAspect="1"/></p:cNvPicPr><p:nvPr/></p:nvPicPr>'
            . '<p:blipFill><a:blip r:embed="' . $relId . '"/><a:stretch><a:fillRect/></a:stretch></p:blipFill>'
            . '<p:spPr>' . $this->xfrm($x, $y, $w, $h) . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr></p:pic>';
    }

    /**
     * Text box. Each paragraph is a list of runs [text, size pt, bold, color, outline color?]
     * or ['bullet' => true, 'runs' => [...]].
     */
    public function text(int $x, int $y, int $w, int $h, array $paragraphs, string $align = 'l', string $anchor = 't', float $spacing = 1.0): void
    {
        $paras = '';
        foreach ($paragraphs as $para) {
            $bullet = isset($para['bullet']);
            $runs = $bullet ? $para['runs'] : $para;
            $pPr = '<a:pPr algn="' . $align . '"' . ($bullet ? ' marL="342900" indent="-285750"' : '') . '>'
                . '<a:lnSpc><a:spcPct val="' . (int) round($spacing * 100000) . '"/></a:lnSpc>'
                . '<a:spcBef><a:spcPts val="' . ($bullet ? 200 : 600) . '"/></a:spcBef>'
                . ($bullet ? '<a:buClr><a:srgbClr val="' . ManualPresentation::NAVY . '"/></a:buClr><a:buFont typeface="Arial"/><a:buChar char="&#8226;"/>' : '<a:buNone/>')
                . '</a:pPr>';
            $r = '';
            foreach ($runs as $run) {
                [$text, $size, $bold, $color] = $run;
                $outline = $run[4] ?? null;
                $fill = $outline
                    ? '<a:ln w="19050"><a:solidFill><a:srgbClr val="' . $outline . '"/></a:solidFill></a:ln><a:noFill/>'
                    : '<a:solidFill><a:srgbClr val="' . $color . '"/></a:solidFill>';
                $r .= '<a:r><a:rPr lang="en-US" sz="' . (int) round($size * 100) . '" b="' . ($bold ? 1 : 0) . '" dirty="0">' . $fill
                    . '<a:latin typeface="' . self::FONT . '"/><a:cs typeface="' . self::FONT . '"/></a:rPr>'
                    . '<a:t>' . ManualPresentation::esc((string) $text) . '</a:t></a:r>';
            }
            $paras .= '<a:p>' . $pPr . $r . '</a:p>';
        }
        if ($paras === '') {
            $paras = '<a:p><a:endParaRPr lang="en-US" dirty="0"/></a:p>';
        }
        $this->out .= '<p:sp><p:nvSpPr><p:cNvPr id="' . $this->id++ . '" name="Text ' . $this->id . '"/><p:cNvSpPr txBox="1"/><p:nvPr/></p:nvSpPr>'
            . '<p:spPr>' . $this->xfrm($x, $y, $w, $h) . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom><a:noFill/><a:ln><a:noFill/></a:ln></p:spPr>'
            . '<p:txBody><a:bodyPr wrap="square" lIns="0" tIns="0" rIns="0" bIns="0" anchor="' . $anchor . '"><a:normAutofit/></a:bodyPr>'
            . '<a:lstStyle/>' . $paras . '</p:txBody></p:sp>';
    }

    private function xfrm(int $x, int $y, int $w, int $h): string
    {
        return '<a:xfrm><a:off x="' . ($x * self::EMU) . '" y="' . ($y * self::EMU) . '"/><a:ext cx="' . ($w * self::EMU) . '" cy="' . ($h * self::EMU) . '"/></a:xfrm>';
    }

    private function fill(array $colors, int $angle, ?array $alphas): string
    {
        $alpha = static function (int $i) use ($alphas): string {
            if ($alphas === null || !isset($alphas[$i]) || $alphas[$i] >= 100) {
                return '';
            }
            return '<a:alpha val="' . (max(0, (int) $alphas[$i]) * 1000) . '"/>';
        };
        if (count($colors) === 1) {
            return '<a:solidFill><a:srgbClr val="' . $colors[0] . '">' . $alpha(0) . '</a:srgbClr></a:solidFill>';
        }
        $stops = '';
        $last = count($colors) - 1;
        foreach ($colors as $i => $color) {
            $stops .= '<a:gs pos="' . (int) round($i / $last * 100000) . '"><a:srgbClr val="' . $color . '">' . $alpha($i) . '</a:srgbClr></a:gs>';
        }
        return '<a:gradFill rotWithShape="1"><a:gsLst>' . $stops . '</a:gsLst><a:lin ang="' . $angle . '" scaled="0"/></a:gradFill>';
    }
}
