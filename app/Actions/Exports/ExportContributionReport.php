<?php

declare(strict_types=1);

namespace App\Actions\Exports;

use App\Actions\Reports\BuildContributionReport;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use RuntimeException;
use ZipArchive;

final class ExportContributionReport
{
    public function __construct(
        private readonly BuildContributionReport $buildContributionReport,
    ) {
    }

    public function execute(int $year, int $startMonth, int $endMonth): string
    {
        $report = $this->buildContributionReport->execute($year, $startMonth, $endMonth);

        return $this->workbook($report);
    }

    /**
     * @param array{
     *     start: Carbon,
     *     end: Carbon,
     *     weeks: Collection<int, Carbon>,
     *     members: Collection<int, Member>,
     *     memberTotals: array<int, float>,
     *     reportTotal: float
     * } $report
     */
    private function workbook(array $report): string
    {
        $path = tempnam(sys_get_temp_dir(), 'purok-contribution-report-');

        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary workbook file.');
        }

        $zip = new ZipArchive();

        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to open a temporary workbook file.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRelationships());
        $zip->addFromString('xl/workbook.xml', $this->workbookXml());
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationships());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheet($report));
        $zip->close();

        $contents = file_get_contents($path);
        @unlink($path);

        if ($contents === false) {
            throw new RuntimeException('Unable to read the generated workbook.');
        }

        return $contents;
    }

    /**
     * @param array{
     *     start: Carbon,
     *     end: Carbon,
     *     weeks: Collection<int, Carbon>,
     *     members: Collection<int, Member>,
     *     memberTotals: array<int, float>,
     *     reportTotal: float
     * } $report
     */
    private function sheet(array $report): string
    {
        $weeks = $report['weeks']->values();
        $members = $report['members']->values();
        $lastColumn = $this->columnName($weeks->count() + 2);
        $rows = [];

        $rows[] = '<row r="1" ht="24" customHeight="1"><c r="A1" t="inlineStr" s="1"><is><t>Member Contributions</t></is></c></row>';
        $rows[] = '<row r="2"><c r="A2" t="inlineStr" s="2"><is><t>'.$this->escape('Period: '.$report['start']->format('M Y').' - '.$report['end']->format('M Y')).'</t></is></c></row>';
        $rows[] = '<row r="3"><c r="A3" t="inlineStr" s="2"><is><t>'.$this->escape('Generated: '.now()->format('Y-m-d H:i')).'</t></is></c></row>';
        $rows[] = '<row r="4"><c r="A4" t="inlineStr" s="3"><is><t>Report Total</t></is></c><c r="B4" s="4"><v>'.number_format((float) $report['reportTotal'], 2, '.', '').'</v></c></row>';

        $headerCells = ['<c r="A6" t="inlineStr" s="3"><is><t>Member</t></is></c>'];
        foreach ($weeks as $index => $week) {
            $column = $this->columnName($index + 2);
            $headerCells[] = '<c r="'.$column.'6" t="inlineStr" s="3"><is><t>'.$this->escape($week->format('M d')).'</t></is></c>';
        }
        $headerCells[] = '<c r="'.$lastColumn.'6" t="inlineStr" s="3"><is><t>Total</t></is></c>';
        $rows[] = '<row r="6">'.implode('', $headerCells).'</row>';

        $rowNumber = 7;

        foreach ($members as $member) {
            $cells = [
                '<c r="A'.$rowNumber.'" t="inlineStr" s="5"><is><t>'.$this->escape((string) $member->name).'</t></is></c>',
            ];

            foreach ($weeks as $index => $week) {
                $weekString = $week->toDateString();
                $contribution = $member->contributions->first(
                    fn ($item) => $item->week_start->toDateString() === $weekString,
                );
                $column = $this->columnName($index + 2);
                $style = $contribution ? 6 : 7;
                $text = $contribution ? 'Paid' : '-';

                $cells[] = '<c r="'.$column.$rowNumber.'" t="inlineStr" s="'.$style.'"><is><t>'.$text.'</t></is></c>';
            }

            $cells[] = '<c r="'.$lastColumn.$rowNumber.'" s="4"><v>'.number_format((float) ($report['memberTotals'][$member->id] ?? 0), 2, '.', '').'</v></c>';
            $rows[] = '<row r="'.$rowNumber.'">'.implode('', $cells).'</row>';
            $rowNumber++;
        }

        if ($members->isEmpty()) {
            $rows[] = '<row r="'.$rowNumber.'"><c r="A'.$rowNumber.'" t="inlineStr" s="5"><is><t>No contribution records found for this period.</t></is></c></row>';
        }

        $lastRow = max(6, $rowNumber - 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<dimension ref="A1:'.$lastColumn.$lastRow.'"/>'
            .'<sheetViews><sheetView workbookViewId="0"><pane xSplit="1" ySplit="6" topLeftCell="B7" activePane="bottomRight" state="frozen"/></sheetView></sheetViews>'
            .'<sheetFormatPr defaultRowHeight="15"/>'
            .'<cols>'.$this->columns($weeks->count()).'</cols>'
            .'<sheetData>'.implode('', $rows).'</sheetData>'
            .'<autoFilter ref="A6:'.$lastColumn.$lastRow.'"/>'
            .'<mergeCells count="3"><mergeCell ref="A1:'.$lastColumn.'1"/><mergeCell ref="A2:'.$lastColumn.'2"/><mergeCell ref="A3:'.$lastColumn.'3"/></mergeCells>'
            .'</worksheet>';
    }

    private function columns(int $weekCount): string
    {
        $columns = ['<col min="1" max="1" width="32" customWidth="1"/>'];

        if ($weekCount > 0) {
            $columns[] = '<col min="2" max="'.($weekCount + 1).'" width="12" customWidth="1"/>';
        }

        $totalColumn = $weekCount + 2;
        $columns[] = '<col min="'.$totalColumn.'" max="'.$totalColumn.'" width="14" customWidth="1"/>';

        return implode('', $columns);
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="16"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="4"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF3F4F6"/><bgColor indexed="64"/></patternFill></fill><fill><patternFill patternType="solid"><fgColor rgb="FFDCFCE7"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"><color rgb="FFD1D5DB"/></left><right style="thin"><color rgb="FFD1D5DB"/></right><top style="thin"><color rgb="FFD1D5DB"/></top><bottom style="thin"><color rgb="FFD1D5DB"/></bottom><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="8">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .'<xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"/>'
            .'<xf numFmtId="4" fontId="2" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyNumberFormat="1"/>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"/>'
            .'<xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1"><alignment horizontal="center"/></xf>'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1"><alignment horizontal="center"/></xf>'
            .'</cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            .'</styleSheet>';
    }

    private function workbookXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Contributions" sheetId="1" r:id="rId1"/></sheets>'
            .'</workbook>';
    }

    private function workbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    private function rootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    private function columnName(int $number): string
    {
        $name = '';

        while ($number > 0) {
            $number--;
            $name = chr(65 + ($number % 26)).$name;
            $number = intdiv($number, 26);
        }

        return $name;
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($this->xmlText($value), ENT_XML1 | ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function xmlText(string $value): string
    {
        if (! mb_check_encoding($value, 'UTF-8')) {
            $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
        }

        return (string) preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', $value);
    }
}
