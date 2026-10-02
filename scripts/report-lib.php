<?php

/**
 * Builder .docx (Office Open XML) minimal — PHP murni + ZipArchive.
 * Mendukung: cover, heading 1-3, paragraf, list bullet bertingkat,
 * list bernomor, tabel berbingkai + header berwarna, page break,
 * footer nomor halaman.
 */

class DocxBuilder
{
    public const ACCENT = '047857';   // emerald-800
    public const DARK = '0F172A';     // slate-900
    public const GREY = '64748B';     // slate-500
    public const HEADFILL = 'E7F6EF'; // header tabel

    private array $body = [];
    private array $numberingUsage = ['bullet' => false, 'number' => false];
    private int $listSeq = 0;
    private array $rels = [];
    private int $relId = 1;
    private bool $hasFooter = false;

    // ---------------------------------------------------------------- core

    private function esc(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }

    private function runXml(string $text, array $opt = []): string
    {
        $rPr = '<w:rPr>';
        $rPr .= '<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>';
        if (! empty($opt['bold'])) {
            $rPr .= '<w:b/>';
        }
        if (! empty($opt['italic'])) {
            $rPr .= '<w:i/>';
        }
        if (! empty($opt['color'])) {
            $rPr .= '<w:color w:val="'.$opt['color'].'"/>';
        }
        if (! empty($opt['size'])) {
            $rPr .= '<w:sz w:val="'.($opt['size'] * 2).'"/><w:szCs w:val="'.($opt['size'] * 2).'"/>';
        }
        $rPr .= '</w:rPr>';

        $textXml = '';
        $parts = preg_split('/(\n)/', $text);
        foreach ($parts as $i => $part) {
            if ($i > 0) {
                $textXml .= '<w:br/>';
            }
            $textXml .= '<w:t xml:space="preserve">'.$this->esc($part).'</w:t>';
        }

        return '<w:r>'.$rPr.$textXml.'</w:r>';
    }

    private function paraXml(string $inner, array $pPr = []): string
    {
        $xml = '<w:p>';
        if ($pPr) {
            $xml .= '<w:pPr>'.implode('', $pPr).'</w:pPr>';
        }
        $xml .= $inner.'</w:p>';

        return $xml;
    }

    private function nextRel(): string
    {
        $id = 'rId'.(++$this->relId);

        return $id;
    }

    // ------------------------------------------------------------- content

    public function h1(string $text): void
    {
        $this->body[] = $this->paraXml(
            $this->runXml($text, ['bold' => true, 'color' => self::ACCENT, 'size' => 17]),
            ['<w:pStyle w:val="Heading1"/>', '<w:spacing w:before="360" w:after="160"/>']
        );
    }

    public function h2(string $text): void
    {
        $this->body[] = $this->paraXml(
            $this->runXml($text, ['bold' => true, 'color' => self::DARK, 'size' => 14]),
            ['<w:pStyle w:val="Heading2"/>', '<w:spacing w:before="280" w:after="120"/>']
        );
    }

    public function h3(string $text): void
    {
        $this->body[] = $this->paraXml(
            $this->runXml($text, ['bold' => true, 'color' => self::DARK, 'size' => 12]),
            ['<w:pStyle w:val="Heading3"/>', '<w:spacing w:before="220" w:after="100"/>']
        );
    }

    /** Paragraf deskriptif. $runs = array of [text, opts] untuk bold inline, dsb. */
    public function p(array|string $runs, array $opt = []): void
    {
        $runs = is_string($runs) ? [[$runs, []]] : $runs;
        $inner = '';
        foreach ($runs as [$text, $o]) {
            $inner .= $this->runXml($text, $o + ($opt['run'] ?? []));
        }
        $pPr = ['<w:spacing w:after="120" w:line="276" w:lineRule="auto"/>'];
        if (! empty($opt['justify'])) {
            $pPr[] = '<w:jc w:val="both"/>';
        }
        if (! empty($opt['indent'])) {
            $pPr[] = '<w:ind w:left="'.($opt['indent'] * 567).'"/>';
        }
        $this->body[] = $this->paraXml($inner, $pPr);
    }

    public function spacer(int $pt = 8): void
    {
        $this->body[] = $this->paraXml(
            '',
            ['<w:spacing w:after="0" w:before="0"/><w:rPr><w:sz w:val="'.($pt * 2).'"/></w:rPr>']
        );
    }

    public function pageBreak(): void
    {
        $this->body[] = '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';
    }

    /**
     * Normalisasi item list menjadi daftar run-tuple [text, opts].
     * Menerima: "teks" | [text, opts] | [[text, opts], [text, opts], ...]
     */
    private function toRuns(array|string $item): array
    {
        if (is_string($item)) {
            return [[$item, []]];
        }
        if (is_string($item[0] ?? null)) {
            return [[$item[0], $item[1] ?? []]];
        }

        return $item;
    }

    /** Bullet list. depth 0/1. */
    public function bullets(array $items): void
    {
        $this->numberingUsage['bullet'] = true;
        foreach ($items as $item) {
            $depth = 0;
            if (is_array($item) && isset($item['depth'])) {
                $depth = $item['depth'];
                unset($item['depth']);
                $item = $item['runs'] ?? $item;
            }
            $inner = '';
            foreach ($this->toRuns($item) as [$text, $o]) {
                $inner .= $this->runXml($text, $o);
            }
            $this->body[] = $this->paraXml($inner, [
                '<w:numPr><w:ilvl w:val="'.$depth.'"/><w:numId w:val="1"/></w:numPr>',
                '<w:ind w:left="'.(720 + $depth * 360).'"/>',
                '<w:spacing w:after="80" w:line="264" w:lineRule="auto"/>',
            ]);
        }
    }

    /** List bernomor (berlanjut per panggilan, reset manual dengan argumen). */
    public function numbered(array $items, bool $reset = true): void
    {
        $this->numberingUsage['number'] = true;
        if ($reset) {
            $this->listSeq++;
        }
        $n = 0;
        foreach ($items as $item) {
            $n++;
            $inner = '';
            foreach ($this->toRuns($item) as [$text, $o]) {
                $inner .= $this->runXml($text, $o);
            }
            $this->body[] = $this->paraXml($inner, [
                '<w:numPr><w:ilvl w:val="0"/><w:numId w:val="'.(1 + $this->listSeq).'"/></w:numPr>',
                '<w:ind w:left="720"/>',
                '<w:spacing w:after="80" w:line="264" w:lineRule="auto"/>',
            ]);
        }
    }

    /** Callout / kotak catatan: paragraf dengan shading + border kiri. */
    public function note(string $text, string $fill = 'F0FDF4', string $border = self::ACCENT): void
    {
        $inner = $this->runXml($text, ['size' => 10.5, 'color' => '14532D']);
        $this->body[] = '<w:p><w:pPr>'
            .'<w:pBdr><w:left w:val="single" w:sz="18" w:space="4" w:color="'.$border.'"/></w:pBdr>'
            .'<w:shd w:val="clear" w:fill="'.$fill.'"/>'
            .'<w:ind w:left="200" w:right="200"/>'
            .'<w:spacing w:before="120" w:after="160" w:line="264" w:lineRule="auto"/>'
            .'</w:pPr>'.$inner.'</w:p>';
    }

    /**
     * Tabel. $header = array string; $rows = array of array (string atau
     * [text, opts]); $weights = bobot lebar kolom (relatif).
     * Opts per sel: bold, color, fill, align.
     */
    public function table(array $header, array $rows, array $weights = [], int $fontSize = 10): void
    {
        $cols = count($header);
        $total = array_sum($weights ?: array_fill(0, $cols, 1));
        $pct = 100.0;
        $widths = array_map(fn ($w) => (int) round($w / $total * 9360), $weights ?: array_fill(0, $cols, 1));

        $grid = '';
        foreach ($widths as $w) {
            $grid .= '<w:gridCol w:w="'.$w.'"/>';
        }
        $borders = '<w:tblBorders>'
            .'<w:top w:val="single" w:sz="4" w:color="CBD5E1"/><w:left w:val="single" w:sz="4" w:color="CBD5E1"/>'
            .'<w:bottom w:val="single" w:sz="4" w:color="CBD5E1"/><w:right w:val="single" w:sz="4" w:color="CBD5E1"/>'
            .'<w:insideH w:val="single" w:sz="4" w:color="CBD5E1"/><w:insideV w:val="single" w:sz="4" w:color="CBD5E1"/>'
            .'</w:tblBorders>';

        $xml = '<w:tbl><w:tblPr><w:tblW w:w="9360" w:type="dxa"/>'.$borders
            .'<w:tblLayout w:type="fixed"/><w:tblCellMar>'
            .'<w:top w:w="60" w:type="dxa"/><w:left w:w="100" w:type="dxa"/>'
            .'<w:bottom w:w="60" w:type="dxa"/><w:right w:w="100" w:type="dxa"/>'
            .'</w:tblCellMar></w:tblPr><w:tblGrid>'.$grid.'</w:tblGrid>';

        // Header row
        $xml .= '<w:tr><w:trPr><w:tblHeader/></w:trPr>';
        foreach ($header as $i => $text) {
            $xml .= $this->cell($text, [
                'bold' => true, 'color' => 'FFFFFF', 'fill' => self::ACCENT,
                'width' => $widths[$i], 'size' => $fontSize,
            ]);
        }
        $xml .= '</w:tr>';

        // Body rows
        $shade = false;
        foreach ($rows as $row) {
            $xml .= '<w:tr>';
            foreach ($row as $i => $cell) {
                // Toleransi: sel boleh string, [text, opts], atau [[text, opts]] bersarang.
                if (is_array($cell) && isset($cell[0]) && is_array($cell[0])) {
                    $cell = $cell[0];
                }
                $opt = is_array($cell) ? $cell : [$cell];
                $text = (string) array_shift($opt);
                $opt['width'] = $widths[$i] ?? null;
                $opt['size'] = $fontSize;
                $opt['fill'] = $opt['fill'] ?? ($shade ? 'F8FAFC' : null);
                $xml .= $this->cell((string) $text, $opt);
            }
            $xml .= '</w:tr>';
            $shade = ! $shade;
        }
        $xml .= '</w:tbl>';
        $this->body[] = $xml;
        $this->body[] = $this->paraXml('', ['<w:spacing w:after="120"/>']);
    }

    private function cell(string $text, array $opt): string
    {
        $tcPr = '<w:tcPr>';
        if (! empty($opt['width'])) {
            $tcPr .= '<w:tcW w:w="'.$opt['width'].'" w:type="dxa"/>';
        }
        if (! empty($opt['fill'])) {
            $tcPr .= '<w:shd w:val="clear" w:fill="'.$opt['fill'].'"/>';
        }
        if (isset($opt['align'])) {
            $tcPr .= '<w:vAlign w:val="center"/>';
        }
        $tcPr .= '</w:tcPr>';

        $runs = [[$text, array_intersect_key($opt, array_flip(['bold', 'color', 'size', 'italic']))]];
        $inner = '';
        foreach ($runs as [$t, $o]) {
            $inner .= $this->runXml($t, $o);
        }
        $pPr = '<w:pPr><w:spacing w:after="0" w:before="0" w:line="240" w:lineRule="auto"/>'
            .(isset($opt['align']) ? '<w:jc w:val="'.$opt['align'].'"/>' : '')
            .'</w:pPr>';

        return '<w:tc>'.$tcPr.$pPr.$inner.'</w:tc>';
    }

    public function cover(
        string $title,
        string $subtitle,
        string $org,
        string $date,
        array $meta = []
    ): void {
        for ($i = 0; $i < 5; $i++) {
            $this->spacer(30);
        }
        $this->body[] = $this->paraXml(
            $this->runXml($org, ['bold' => true, 'color' => self::ACCENT, 'size' => 13]),
            ['<w:jc w:val="center"/>']
        );
        $this->spacer(20);
        $this->body[] = $this->paraXml(
            $this->runXml($title, ['bold' => true, 'color' => self::DARK, 'size' => 26]),
            ['<w:jc w:val="center"/>', '<w:spacing w:after="200"/>']
        );
        $this->body[] = $this->paraXml(
            $this->runXml($subtitle, ['color' => self::GREY, 'size' => 14]),
            ['<w:jc w:val="center"/>', '<w:spacing w:after="600"/>']
        );
        // garis aksen
        $this->body[] = '<w:p><w:pPr><w:pBdr><w:bottom w:val="single" w:sz="12" w:space="1" w:color="'.self::ACCENT.'"/></w:pBdr><w:ind w:left="2000" w:right="2000"/></w:pPr></w:p>';
        $this->spacer(30);
        foreach ($meta as $label => $value) {
            $this->body[] = $this->paraXml(
                $this->runXml($label.'  ', ['color' => self::GREY, 'size' => 11])
                .$this->runXml($value, ['bold' => true, 'size' => 11, 'color' => self::DARK]),
                ['<w:jc w:val="center"/>', '<w:spacing w:after="60"/>']
            );
        }
        $this->body[] = $this->paraXml(
            $this->runXml($date, ['color' => self::GREY, 'size' => 11]),
            ['<w:jc w:val="center"/>']
        );
        $this->pageBreak();
    }

    public function footerPageNumber(string $left): void
    {
        $this->hasFooter = true;
        $this->footerContent = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:ftr xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:p><w:pPr><w:tabs><w:tab w:val="center" w:pos="4680"/><w:tab w:val="right" w:pos="9360"/></w:tabs>'
            .'<w:pBdr><w:top w:val="single" w:sz="4" w:space="4" w:color="CBD5E1"/></w:pBdr>'
            .'<w:spacing w:before="60"/></w:pPr>'
            .$this->runXml($left, ['size' => 8.5, 'color' => self::GREY])
            .$this->runXml("\t", ['size' => 8.5])
            .'<w:r><w:rPr><w:color w:val="'.self::GREY.'"/><w:sz w:val="17"/></w:rPr><w:fldChar w:fldCharType="begin"/></w:r>'
            .'<w:r><w:rPr><w:color w:val="'.self::GREY.'"/><w:sz w:val="17"/></w:rPr><w:instrText xml:space="preserve"> PAGE </w:instrText></w:r>'
            .'<w:r><w:rPr><w:color w:val="'.self::GREY.'"/><w:sz w:val="17"/></w:rPr><w:fldChar w:fldCharType="separate"/></w:r>'
            .'<w:r><w:rPr><w:color w:val="'.self::GREY.'"/><w:sz w:val="17"/></w:rPr><w:t>1</w:t></w:r>'
            .'<w:r><w:rPr><w:color w:val="'.self::GREY.'"/><w:sz w:val="17"/></w:rPr><w:fldChar w:fldCharType="end"/></w:r>'
            .'</w:p></w:ftr>';
    }

    private string $footerContent = '';

    // ---------------------------------------------------------------- save

    public function save(string $path): void
    {
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            .'<Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            .($this->numberingUsage['bullet'] || $this->numberingUsage['number']
                ? '<Override PartName="/word/numbering.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml"/>'
                : '')
            .($this->hasFooter
                ? '<Override PartName="/word/footer1.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml"/>'
                : '')
            .'</Types>';

        $rootRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            .'</Relationships>';

        $docRelId = 'rId1';
        $docRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="'.$docRelId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .($this->numberingUsage['bullet'] || $this->numberingUsage['number']
                ? '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/numbering" Target="numbering.xml"/>'
                : '')
            .($this->hasFooter
                ? '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/footer" Target="footer1.xml"/>'
                : '')
            .'</Relationships>';

        $numPr = '';
        if ($this->numberingUsage['bullet'] || $this->numberingUsage['number']) {
            $numPr = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                .'<w:numbering xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
                // abstract 0 = bullet dua level
                .'<w:abstractNum w:abstractNumId="0"><w:multiLevelType w:val="hybridMultilevel"/>'
                .'<w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="&#8226;"/>'
                .'<w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr>'
                .'<w:rPr><w:rFonts w:ascii="Symbol" w:hAnsi="Symbol" w:hint="default"/></w:rPr></w:lvl>'
                .'<w:lvl w:ilvl="1"><w:start w:val="1"/><w:numFmt w:val="bullet"/><w:lvlText w:val="&#8211;"/>'
                .'<w:lvlJc w:val="left"/><w:pPr><w:ind w:left="1080" w:hanging="360"/></w:pPr>'
                .'<w:rPr><w:rFonts w:ascii="Courier New" w:hAnsi="Courier New" w:hint="default"/></w:rPr></w:lvl>'
                .'</w:abstractNum>'
                // abstract 1..N = decimal per numbered() call
                .'<w:abstractNum w:abstractNumId="1"><w:multiLevelType w:val="singleLevel"/>'
                .'<w:lvl w:ilvl="0"><w:start w:val="1"/><w:numFmt w:val="decimal"/><w:lvlText w:val="%1."/>'
                .'<w:lvlJc w:val="left"/><w:pPr><w:ind w:left="720" w:hanging="360"/></w:pPr></w:lvl>'
                .'</w:abstractNum>'
                .'<w:num w:numId="1"><w:abstractNumId w:val="0"/></w:num>';
            for ($i = 2; $i <= 1 + $this->listSeq; $i++) {
                $numPr .= '<w:num w:numId="'.$i.'"><w:abstractNumId w:val="1"/>'
                    .'<w:lvlOverride w:ilvl="0"><w:startOverride w:val="1"/></w:lvlOverride></w:num>';
            }
            $numPr .= '</w:numbering>';
        }

        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            .'<w:docDefaults><w:rPrDefault><w:rPr>'
            .'<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Calibri"/>'
            .'<w:sz w:val="21"/><w:szCs w:val="21"/><w:color w:val="1E293B"/>'
            .'</w:rPr></w:rPrDefault><w:pPrDefault><w:pPr><w:spacing w:after="120"/></w:pPr></w:pPrDefault></w:docDefaults>'
            .'<w:style w:type="paragraph" w:default="1" w:styleId="Normal"><w:name w:val="Normal"/></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Heading1"><w:name w:val="heading 1"/><w:basedOn w:val="Normal"/>'
            .'<w:pPr><w:outlineLvl w:val="0"/></w:pPr></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Heading2"><w:name w:val="heading 2"/><w:basedOn w:val="Normal"/>'
            .'<w:pPr><w:outlineLvl w:val="1"/></w:pPr></w:style>'
            .'<w:style w:type="paragraph" w:styleId="Heading3"><w:name w:val="heading 3"/><w:basedOn w:val="Normal"/>'
            .'<w:pPr><w:outlineLvl w:val="2"/></w:pPr></w:style>'
            .'<w:style w:type="table" w:styleId="TableGrid"><w:name w:val="Table Grid"/></w:style>'
            .'</w:styles>';

        $sectPr = '<w:sectPr>'
            .($this->hasFooter ? '<w:footerReference r:id="rId3" w:type="default"/>' : '')
            .'<w:pgSz w:w="11906" w:h="16838"/>' // A4
            .'<w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134" w:header="720" w:footer="720" w:gutter="0"/>'
            .'</w:sectPr>';

        $document = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main" '
            .'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<w:body>'.implode('', $this->body).$sectPr.'</w:body></w:document>';

        $zip = new ZipArchive;
        if (true !== $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
            throw new RuntimeException("Gagal membuat zip: $path");
        }
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rootRels);
        $zip->addFromString('word/document.xml', $document);
        $zip->addFromString('word/styles.xml', $styles);
        if ($numPr !== '') {
            $zip->addFromString('word/numbering.xml', $numPr);
        }
        if ($this->hasFooter) {
            $zip->addFromString('word/footer1.xml', $this->footerContent);
        }
        $zip->addFromString('word/_rels/document.xml.rels', $docRels);
        $zip->close();
    }
}
