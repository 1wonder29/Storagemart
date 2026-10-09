<?php

/**
 * Troubleshooting guide as slides: the same slides are shown in the browser
 * (/manuals/view) and downloaded as a PowerPoint file (/manuals/pptx), built from the
 * guide's title, description and steps. No Canva or other outside link needed.
 * The .pptx is written with a small built-in ZIP writer, so the server needs no extra PHP extension.
 */
class ManualPresentation
{
    private const W = 12192000; // 16:9 slide, EMU
    private const H = 6858000;
    private const BLUE = '1F3A93';
    private const ORANGE = 'F26522';
    private const DARK = '1F2937';
    private const MUTED = '6B7280';

    /**
     * @return array<int, array{kind: string, title: string, text: string, number?: int, total?: int}>
     */
    public static function slides(array $manual): array
    {
        $steps = self::steps((string) ($manual['steps'] ?? ''));
        $title = trim((string) ($manual['title'] ?? 'IT Troubleshooting Guide'));
        $slides = [[
            'kind' => 'title',
            'title' => $title,
            'text' => trim((string) ($manual['description'] ?? '')) ?: 'Try these steps before IT arrives.',
        ]];
        foreach ($steps as $i => $step) {
            $slides[] = ['kind' => 'step', 'title' => $title, 'text' => $step, 'number' => $i + 1, 'total' => count($steps)];
        }
        $slides[] = [
            'kind' => 'end',
            'title' => 'Still not working?',
            'text' => 'Tap "Need help from IT personnel" in the ticket chat and IT will help you. Send a photo of any error message.',
        ];
        return $slides;
    }

    /** @return string[] one step per non-empty line */
    public static function steps(string $value): array
    {
        $lines = preg_split('/\r\n|\r|\n/', $value) ?: [];
        return array_values(array_filter(array_map('trim', $lines), 'strlen'));
    }

    /** The .pptx file contents. */
    public static function pptx(array $manual): string
    {
        $slides = self::slides($manual);
        $files = [];
        $count = count($slides);

        $overrides = '';
        for ($i = 1; $i <= $count; $i++) {
            $overrides .= '<Override PartName="/ppt/slides/slide' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>';
        }
        $files['[Content_Types].xml'] = self::xml('<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
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
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
            . '</Relationships>');

        $now = gmdate('Y-m-d\TH:i:s\Z');
        $files['docProps/core.xml'] = self::xml('<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
            . 'xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" '
            . 'xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
            . '<dc:title>' . self::esc($slides[0]['title']) . '</dc:title><dc:creator>StorageMart TMS</dc:creator>'
            . '<dcterms:created xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:created>'
            . '<dcterms:modified xsi:type="dcterms:W3CDTF">' . $now . '</dcterms:modified></cp:coreProperties>');
        $files['docProps/app.xml'] = self::xml('<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">'
            . '<Application>StorageMart TMS</Application><Slides>' . $count . '</Slides></Properties>');

        $ns = 'xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" '
            . 'xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"';

        $sldIds = '';
        $presRels = '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="slideMasters/slideMaster1.xml"/>';
        for ($i = 1; $i <= $count; $i++) {
            $sldIds .= '<p:sldId id="' . (255 + $i) . '" r:id="rId' . ($i + 1) . '"/>';
            $presRels .= '<Relationship Id="rId' . ($i + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide' . $i . '.xml"/>';
        }
        $next = $count + 2;
        $presRels .= '<Relationship Id="rId' . $next . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/presProps" Target="presProps.xml"/>'
            . '<Relationship Id="rId' . ($next + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/viewProps" Target="viewProps.xml"/>'
            . '<Relationship Id="rId' . ($next + 2) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="theme/theme1.xml"/>'
            . '<Relationship Id="rId' . ($next + 3) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/tableStyles" Target="tableStyles.xml"/>';

        $files['ppt/presentation.xml'] = self::xml('<p:presentation ' . $ns . ' saveSubsetFonts="1">'
            . '<p:sldMasterIdLst><p:sldMasterId id="2147483648" r:id="rId1"/></p:sldMasterIdLst>'
            . '<p:sldIdLst>' . $sldIds . '</p:sldIdLst>'
            . '<p:sldSz cx="' . self::W . '" cy="' . self::H . '"/><p:notesSz cx="6858000" cy="9144000"/>'
            . '</p:presentation>');
        $files['ppt/_rels/presentation.xml.rels'] = self::xml('<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $presRels . '</Relationships>');
        $files['ppt/presProps.xml'] = self::xml('<p:presentationPr ' . $ns . '/>');
        $files['ppt/viewProps.xml'] = self::xml('<p:viewPr ' . $ns . '><p:normalViewPr><p:restoredLeft sz="15620"/><p:restoredTop sz="94660"/></p:normalViewPr>'
            . '<p:gridSpacing cx="76200" cy="76200"/></p:viewPr>');
        $files['ppt/tableStyles.xml'] = self::xml('<a:tblStyleLst xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" def="{5C22544A-7EE6-4342-B048-85BDC9FD1C3A}"/>');

        $emptyTree = '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/><a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>';
        $files['ppt/slideMasters/slideMaster1.xml'] = self::xml('<p:sldMaster ' . $ns . '>'
            . '<p:cSld><p:bg><p:bgRef idx="1001"><a:schemeClr val="bg1"/></p:bgRef></p:bg><p:spTree>' . $emptyTree . '</p:spTree></p:cSld>'
            . '<p:clrMap bg1="lt1" tx1="dk1" bg2="lt2" tx2="dk2" accent1="accent1" accent2="accent2" accent3="accent3" accent4="accent4" accent5="accent5" accent6="accent6" hlink="hlink" folHlink="folHlink"/>'
            . '<p:sldLayoutIdLst><p:sldLayoutId id="2147483649" r:id="rId1"/></p:sldLayoutIdLst>'
            . '<p:txStyles><p:titleStyle><a:lvl1pPr><a:defRPr sz="4400"/></a:lvl1pPr></p:titleStyle>'
            . '<p:bodyStyle><a:lvl1pPr><a:defRPr sz="2800"/></a:lvl1pPr></p:bodyStyle>'
            . '<p:otherStyle><a:lvl1pPr><a:defRPr sz="1800"/></a:lvl1pPr></p:otherStyle></p:txStyles></p:sldMaster>');
        $files['ppt/slideMasters/_rels/slideMaster1.xml.rels'] = self::xml('<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/theme" Target="../theme/theme1.xml"/>'
            . '</Relationships>');
        $files['ppt/slideLayouts/slideLayout1.xml'] = self::xml('<p:sldLayout ' . $ns . ' type="blank" preserve="1">'
            . '<p:cSld name="Blank"><p:spTree>' . $emptyTree . '</p:spTree></p:cSld><p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr></p:sldLayout>');
        $files['ppt/slideLayouts/_rels/slideLayout1.xml.rels'] = self::xml('<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="../slideMasters/slideMaster1.xml"/>'
            . '</Relationships>');
        $files['ppt/theme/theme1.xml'] = self::theme();

        foreach ($slides as $i => $slide) {
            $files['ppt/slides/slide' . ($i + 1) . '.xml'] = self::xml('<p:sld ' . $ns . '><p:cSld>'
                . '<p:bg><p:bgPr><a:solidFill><a:srgbClr val="' . ($slide['kind'] === 'step' ? 'FFFFFF' : self::BLUE) . '"/></a:solidFill><a:effectLst/></p:bgPr></p:bg>'
                . '<p:spTree>' . $emptyTree . self::slideShapes($slide) . '</p:spTree></p:cSld>'
                . '<p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr></p:sld>');
            $files['ppt/slides/_rels/slide' . ($i + 1) . '.xml.rels'] = self::xml('<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'
                . '</Relationships>');
        }

        return self::zip($files);
    }

    private static function slideShapes(array $slide): string
    {
        $id = 2;
        $out = '';
        if ($slide['kind'] === 'step') {
            $out .= self::shape($id++, 'Header', 0, 0, self::W, 1100000, self::BLUE, [
                [self::esc('Step ' . $slide['number'] . ' of ' . $slide['total'] . '  ·  ' . $slide['title']), 2200, true, 'FFFFFF'],
            ], 'l', 'ctr', 600000);
            $out .= self::shape($id++, 'Accent', 0, 1100000, self::W, 90000, self::ORANGE, []);
            $out .= self::shape($id++, 'Number', 700000, 2300000, 1800000, 1800000, self::ORANGE, [
                [(string) $slide['number'], 6000, true, 'FFFFFF'],
            ], 'ctr', 'ctr', 0, 'ellipse');
            $size = mb_strlen($slide['text']) > 140 ? 2800 : (mb_strlen($slide['text']) > 70 ? 3400 : 4000);
            $out .= self::shape($id++, 'Step', 2900000, 1700000, 8600000, 3000000, null, [
                [self::esc($slide['text']), $size, true, self::DARK],
            ], 'l', 'ctr');
            $out .= self::shape($id++, 'Footer', 600000, 6000000, 11000000, 500000, null, [
                ['StorageMart IT Helpdesk  ·  Try each step, then check if it works.', 1400, false, self::MUTED],
            ], 'l', 'ctr');
        } else {
            $out .= self::shape($id++, 'Accent', 600000, 1500000, 1400000, 110000, self::ORANGE, []);
            $out .= self::shape($id++, 'Kicker', 600000, 900000, 10000000, 500000, null, [
                [$slide['kind'] === 'title' ? 'STORAGEMART IT HELPDESK' : 'NEED MORE HELP?', 1600, true, 'FBB48A'],
            ], 'l', 'ctr');
            $out .= self::shape($id++, 'Title', 600000, 1800000, 11000000, 1900000, null, [
                [self::esc($slide['title']), mb_strlen($slide['title']) > 40 ? 4000 : 5000, true, 'FFFFFF'],
            ], 'l', 't');
            $out .= self::shape($id++, 'Text', 600000, 3900000, 10500000, 1600000, null, [
                [self::esc($slide['text']), 2400, false, 'DCE3F5'],
            ], 'l', 't');
            $out .= self::shape($id++, 'Footer', 600000, 6000000, 11000000, 500000, null, [
                [$slide['kind'] === 'title' ? 'Follow the steps on the next slides.' : 'Thank you!', 1400, false, 'AFC0EA'],
            ], 'l', 'ctr');
        }
        return $out;
    }

    /** @param array<int, array{0: string, 1: int, 2: bool, 3: string}> $runs one paragraph per entry (text already escaped) */
    private static function shape(int $id, string $name, int $x, int $y, int $w, int $h, ?string $fill, array $runs,
                                  string $align = 'l', string $anchor = 't', int $leftInset = 91440, string $geom = 'rect'): string
    {
        $paras = '';
        foreach ($runs as [$text, $size, $bold, $color]) {
            $paras .= '<a:p><a:pPr algn="' . $align . '"/><a:r><a:rPr lang="en-US" sz="' . $size . '" b="' . ($bold ? 1 : 0) . '" dirty="0">'
                . '<a:solidFill><a:srgbClr val="' . $color . '"/></a:solidFill><a:latin typeface="Arial"/><a:cs typeface="Arial"/></a:rPr>'
                . '<a:t>' . $text . '</a:t></a:r></a:p>';
        }
        if ($paras === '') {
            $paras = '<a:p><a:endParaRPr lang="en-US" dirty="0"/></a:p>';
        }
        return '<p:sp><p:nvSpPr><p:cNvPr id="' . $id . '" name="' . $name . '"/><p:cNvSpPr txBox="1"/><p:nvPr/></p:nvSpPr>'
            . '<p:spPr><a:xfrm><a:off x="' . $x . '" y="' . $y . '"/><a:ext cx="' . $w . '" cy="' . $h . '"/></a:xfrm>'
            . '<a:prstGeom prst="' . $geom . '"><a:avLst/></a:prstGeom>'
            . ($fill ? '<a:solidFill><a:srgbClr val="' . $fill . '"/></a:solidFill>' : '<a:noFill/>') . '<a:ln><a:noFill/></a:ln></p:spPr>'
            . '<p:txBody><a:bodyPr wrap="square" lIns="' . $leftInset . '" tIns="45720" rIns="91440" bIns="45720" anchor="' . $anchor . '"><a:normAutofit/></a:bodyPr>'
            . '<a:lstStyle/>' . $paras . '</p:txBody></p:sp>';
    }

    private static function theme(): string
    {
        $fill = '<a:solidFill><a:schemeClr val="phClr"/></a:solidFill>';
        $line = '<a:ln w="9525" cap="flat" cmpd="sng" algn="ctr"><a:solidFill><a:schemeClr val="phClr"/></a:solidFill><a:prstDash val="solid"/></a:ln>';
        $effect = '<a:effectStyle><a:effectLst/></a:effectStyle>';
        return self::xml('<a:theme xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main" name="StorageMart">'
            . '<a:themeElements><a:clrScheme name="StorageMart">'
            . '<a:dk1><a:srgbClr val="1F2937"/></a:dk1><a:lt1><a:srgbClr val="FFFFFF"/></a:lt1>'
            . '<a:dk2><a:srgbClr val="1F3A93"/></a:dk2><a:lt2><a:srgbClr val="EEF1F7"/></a:lt2>'
            . '<a:accent1><a:srgbClr val="1F3A93"/></a:accent1><a:accent2><a:srgbClr val="F26522"/></a:accent2>'
            . '<a:accent3><a:srgbClr val="1CC88A"/></a:accent3><a:accent4><a:srgbClr val="36B9CC"/></a:accent4>'
            . '<a:accent5><a:srgbClr val="F6C23E"/></a:accent5><a:accent6><a:srgbClr val="E74A3B"/></a:accent6>'
            . '<a:hlink><a:srgbClr val="3355C9"/></a:hlink><a:folHlink><a:srgbClr val="6B4FBB"/></a:folHlink></a:clrScheme>'
            . '<a:fontScheme name="StorageMart"><a:majorFont><a:latin typeface="Arial"/><a:ea typeface=""/><a:cs typeface=""/></a:majorFont>'
            . '<a:minorFont><a:latin typeface="Arial"/><a:ea typeface=""/><a:cs typeface=""/></a:minorFont></a:fontScheme>'
            . '<a:fmtScheme name="StorageMart"><a:fillStyleLst>' . $fill . $fill . $fill . '</a:fillStyleLst>'
            . '<a:lnStyleLst>' . $line . $line . $line . '</a:lnStyleLst>'
            . '<a:effectStyleLst>' . $effect . $effect . $effect . '</a:effectStyleLst>'
            . '<a:bgFillStyleLst>' . $fill . $fill . $fill . '</a:bgFillStyleLst></a:fmtScheme></a:themeElements>'
            . '<a:objectDefaults/><a:extraClrSchemeLst/></a:theme>');
    }

    private static function xml(string $body): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\r\n" . $body;
    }

    private static function esc(string $text): string
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
