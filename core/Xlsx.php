<?php
/**
 * Penulis file XLSX minimal (tanpa PhpSpreadsheet/composer).
 * Memakai ZipArchive + XML OpenXML. Cukup untuk dokumen laporan.
 */
class Xlsx
{
    // Style yang tersedia (indeks dipakai di Xlsx::cell)
    public const S_DEFAULT    = 0;
    public const S_TITLE      = 1;
    public const S_SUBTITLE   = 2;
    public const S_BISMILLAH  = 3;
    public const S_SECTION    = 4;
    public const S_HEADER     = 5;
    public const S_LABEL      = 6;
    public const S_LABEL_BOLD = 7;
    public const S_TEXT       = 8;
    public const S_NUM        = 9;
    public const S_NUM_BOLD   = 10;
    public const S_CENTER     = 11;
    public const S_CENTER_BOLD= 12;
    public const S_TOTAL_LABEL= 13;
    public const S_TOTAL_NUM  = 14;
    public const S_WRAP       = 15;
    public const S_BOLD       = 16;

    private string $sheetName;
    private array $cells = [];
    private array $merges = [];
    private array $widths = [];
    private array $heights = [];
    private array $shared = [];
    private array $sharedIdx = [];
    private int $maxRow = 0;
    private int $maxCol = 0;

    public function __construct(string $sheetName = 'Sheet1')
    {
        $sheetName = (string) preg_replace('/[^A-Za-z0-9 _-]/', '', $sheetName);
        $this->sheetName = mb_substr($sheetName !== '' ? $sheetName : 'Sheet1', 0, 31);
    }

    /** Set sel. $value angka disimpan sebagai angka, string sebagai shared string. */
    public function cell(int $row, int $col, $value, int $style = self::S_DEFAULT): void
    {
        if ($row < 1 || $col < 1) return;
        $this->cells[$row][$col] = ['v' => $value, 's' => $style];
        $this->maxRow = max($this->maxRow, $row);
        $this->maxCol = max($this->maxCol, $col);
    }

    /** Label teks (selalu string). */
    public function text(int $row, int $col, string $value, int $style = self::S_DEFAULT): void
    {
        $this->cell($row, $col, $value, $style);
    }

    /** Merge sel (1-based, kolom A=1). */
    public function merge(int $r1, int $c1, int $r2, int $c2): void
    {
        $this->merges[] = self::colName($c1) . $r1 . ':' . self::colName($c2) . $r2;
    }

    public function width(int $col, float $w): void
    {
        $this->widths[$col] = $w;
    }

    public function rowHeight(int $row, float $h): void
    {
        $this->heights[$row] = $h;
    }

    /** Tulis file ke output lalu exit. */
    public function stream(string $filename): void
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        if ($tmp === false) throw new RuntimeException('Gagal membuat file sementara.');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($tmp);
            throw new RuntimeException('Gagal membuat xlsx.');
        }
        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRels());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRels());
        $zip->addFromString('xl/styles.xml', $this->styles());
        // sheet() mengisi shared string table, jadi sharedStrings harus diambil setelahnya
        $sheetXml = $this->sheet();
        $zip->addFromString('xl/sharedStrings.xml', $this->sharedStringsXml());
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);
        $zip->close();

        if (!headers_sent()) {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($tmp));
            header('Cache-Control: no-store');
        }
        readfile($tmp);
        @unlink($tmp);
        exit;
    }

    private static function colName(int $col): string
    {
        $s = '';
        while ($col > 0) {
            $mod = ($col - 1) % 26;
            $s = chr(65 + $mod) . $s;
            $col = intdiv($col - $mod, 26);
        }
        return $s;
    }

    private function ss(string $text): int
    {
        if (array_key_exists($text, $this->sharedIdx)) return $this->sharedIdx[$text];
        $idx = count($this->shared);
        $this->shared[$idx] = $text;
        $this->sharedIdx[$text] = $idx;
        return $idx;
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '</Types>';
    }

    private function rootRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" '
            . 'xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="' . htmlspecialchars($this->sheetName, ENT_XML1) . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
    }

    private function workbookRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
            . '</Relationships>';
    }

    private function styles(): string
    {
        // xf: [numFmt, font, fill, border, align]
        $xfs = [
            '0;0;0;0;',                                                       // 0 default
            '0;2;0;0;horizontal=center;vertical=center;',                    // 1 title
            '0;1;0;0;horizontal=center;',                                    // 2 subtitle
            '0;3;0;0;horizontal=center;',                                    // 3 bismillah
            '0;1;2;1;',                                                       // 4 section (bold, fill, border)
            '0;1;2;1;horizontal=center;vertical=center;wrapText=1;',         // 5 header
            '0;0;0;1;',                                                       // 6 label border
            '0;1;0;1;',                                                       // 7 label bold border
            '0;0;0;1;vertical=top;wrapText=1;',                               // 8 wrap text
            '3;0;0;1;horizontal=right;',                                     // 9 number border
            '3;1;0;1;horizontal=right;',                                     // 10 number bold
            '0;0;0;1;horizontal=center;',                                    // 11 center border
            '0;1;0;1;horizontal=center;',                                    // 12 center bold
            '0;1;0;1;',                                                       // 13 total label bold
            '3;1;0;1;horizontal=right;',                                     // 14 total number bold
            '0;0;0;1;vertical=top;',                                          // 15 wrap/top
            '0;1;0;0;',                                                       // 16 bold
        ];
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="0"/>'
            . '<fonts count="4">'
            . '<font><sz val="11"/><color theme="1"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color theme="1"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="14"/><color theme="1"/><name val="Calibri"/></font>'
            . '<font><i/><sz val="11"/><color theme="1"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFD9E1F2"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FF000000"/></left><right style="thin"><color rgb="FF000000"/></right><top style="thin"><color rgb="FF000000"/></top><bottom style="thin"><color rgb="FF000000"/></bottom><diagonal/></border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="' . count($xfs) . '">';
        foreach ($xfs as $i => $xf) {
            $p = explode(';', $xf);
            $align = '';
            if ($p[4] !== '') {
                $parts = [];
                foreach (explode(';', $p[4]) as $kv) {
                    if ($kv === '') continue;
                    [$k, $v] = array_pad(explode('=', $kv, 2), 2, '');
                    $parts[] = $k . '="' . $v . '"';
                }
                $align = ' applyAlignment="1" alignment="' . implode(' ', $parts) . '"';
            }
            $xml .= '<xf numFmtId="' . $p[0] . '" fontId="' . $p[1] . '" fillId="' . $p[2] . '" borderId="' . $p[3] . '"'
                . ' xfId="0"' . (($p[1] !== '0') ? ' applyFont="1"' : '') . (($p[2] !== '0') ? ' applyFill="1"' : '')
                . (($p[3] !== '0') ? ' applyBorder="1"' : '') . $align . '/>';
        }
        $xml .= '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
        return $xml;
    }

    private function sheet(): string
    {
        $dim = 'A1:' . self::colName(max(1, $this->maxCol)) . max(1, $this->maxRow);
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<dimension ref="' . $dim . '"/>'
            . '<sheetViews><sheetView workbookViewId="0"><selection activeCell="A1" sqref="A1"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="15"/>';
        if ($this->widths) {
            ksort($this->widths);
            $xml .= '<cols>';
            foreach ($this->widths as $c => $w) $xml .= '<col min="' . $c . '" max="' . $c . '" width="' . $w . '" customWidth="1"/>';
            $xml .= '</cols>';
        }
        $xml .= '<sheetData>';
        for ($r = 1; $r <= $this->maxRow; $r++) {
            $ht = $this->heights[$r] ?? null;
            $xml .= '<row r="' . $r . '"' . ($ht ? ' ht="' . $ht . '" customHeight="1"' : '') . '>';
            for ($c = 1; $c <= $this->maxCol; $c++) {
                if (!isset($this->cells[$r][$c])) continue;
                $cell = $this->cells[$r][$c];
                $ref = self::colName($c) . $r;
                $s = $cell['s'] ? ' s="' . $cell['s'] . '"' : '';
                $v = $cell['v'];
                $isNum = is_int($v) || is_float($v) || (is_string($v) && $v !== '' && is_numeric($v) && !preg_match('/^0\d/', $v));
                if ($isNum) {
                    $num = $v + 0;
                    $xml .= '<c r="' . $ref . '"' . $s . '><v>' . $num . '</v></c>';
                } else {
                    $xml .= '<c r="' . $ref . '"' . $s . ' t="s"><v>' . $this->ss((string) $v) . '</v></c>';
                }
            }
            $xml .= '</row>';
        }
        $xml .= '</sheetData>';
        if ($this->merges) {
            $xml .= '<mergeCells count="' . count($this->merges) . '">';
            foreach ($this->merges as $m) $xml .= '<mergeCell ref="' . $m . '"/>';
            $xml .= '</mergeCells>';
        }
        $xml .= '<pageMargins left="0.7" right="0.7" top="0.75" bottom="0.75" header="0.3" footer="0.3"/>';
        $xml .= '</worksheet>';
        return $xml;
    }

    private function sharedStringsXml(): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($this->shared) . '" uniqueCount="' . count($this->shared) . '">';
        foreach ($this->shared as $text) {
            $xml .= '<si><t xml:space="preserve">' . htmlspecialchars((string) $text, ENT_XML1) . '</t></si>';
        }
        return $xml . '</sst>';
    }
}
