<?php

declare(strict_types=1);

namespace App\Actions\Exports;

use App\Actions\Reports\BuildCommunityFundingReport;
use RuntimeException;
use ZipArchive;

final class ExportCommunityFundingReport
{
    public function __construct(private readonly BuildCommunityFundingReport $buildReport) {}

    public function execute(int $eventId): string
    {
        $report = $this->buildReport->execute($eventId);
        $path = tempnam(sys_get_temp_dir(), 'purok-community-funding-report-');

        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary workbook file.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to open a temporary workbook file.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRelationships());
        $zip->addFromString('xl/workbook.xml', $this->workbook());
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

    /** @param array<string, mixed> $report */
    private function sheet(array $report): string
    {
        $event = $report['event'];
        $rows = [
            '<row r="1" ht="24" customHeight="1"><c r="A1" t="inlineStr" s="1"><is><t>Community Funding Report</t></is></c></row>',
            '<row r="2"><c r="A2" t="inlineStr"><is><t>'.$this->escape('Event: '.$event->name).'</t></is></c></row>',
            '<row r="3"><c r="A3" t="inlineStr"><is><t>'.$this->escape('Deadline: '.($event->deadline?->format('Y-m-d') ?? 'None')).'</t></is></c></row>',
            '<row r="4"><c r="A4" t="inlineStr" s="2"><is><t>Goal Amount</t></is></c><c r="B4" s="3"><v>'.number_format($report['goalAmount'], 2, '.', '').'</v></c><c r="C4" t="inlineStr" s="2"><is><t>Collected</t></is></c><c r="D4" s="3"><v>'.number_format($report['collectedAmount'], 2, '.', '').'</v></c></row>',
            '<row r="5"><c r="A5" t="inlineStr" s="2"><is><t>Remaining</t></is></c><c r="B5" s="3"><v>'.number_format($report['remainingAmount'], 2, '.', '').'</v></c><c r="C5" t="inlineStr" s="2"><is><t>Progress</t></is></c><c r="D5" s="6"><v>'.number_format($report['progressPercentage'] / 100, 4, '.', '').'</v></c></row>',
            '<row r="7"><c r="A7" t="inlineStr" s="2"><is><t>Date Received</t></is></c><c r="B7" t="inlineStr" s="2"><is><t>Member</t></is></c><c r="C7" t="inlineStr" s="2"><is><t>Remarks</t></is></c><c r="D7" t="inlineStr" s="2"><is><t>Amount</t></is></c></row>',
        ];
        $rowNumber = 8;

        foreach ($report['donations'] as $donation) {
            $rows[] = '<row r="'.$rowNumber.'">'
                .'<c r="A'.$rowNumber.'" t="inlineStr" s="4"><is><t>'.$donation->received_at->format('Y-m-d').'</t></is></c>'
                .'<c r="B'.$rowNumber.'" t="inlineStr" s="4"><is><t>'.$this->escape((string) $donation->member?->name).'</t></is></c>'
                .'<c r="C'.$rowNumber.'" t="inlineStr" s="4"><is><t>'.$this->escape((string) $donation->remarks).'</t></is></c>'
                .'<c r="D'.$rowNumber.'" s="5"><v>'.number_format((float) $donation->amount, 2, '.', '').'</v></c></row>';
            $rowNumber++;
        }

        if ($report['donations']->isEmpty()) {
            $rows[] = '<row r="8"><c r="A8" t="inlineStr" s="4"><is><t>No donations recorded for this event.</t></is></c></row>';
        }

        $lastRow = max(8, $rowNumber - 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><dimension ref="A1:D'.$lastRow.'"/>'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="7" topLeftCell="A8" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<cols><col min="1" max="1" width="16" customWidth="1"/><col min="2" max="2" width="30" customWidth="1"/><col min="3" max="3" width="48" customWidth="1"/><col min="4" max="4" width="16" customWidth="1"/></cols>'
            .'<sheetData>'.implode('', $rows).'</sheetData><autoFilter ref="A7:D'.$lastRow.'"/>'
            .'<mergeCells count="3"><mergeCell ref="A1:D1"/><mergeCell ref="A2:D2"/><mergeCell ref="A3:D3"/></mergeCells></worksheet>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="1"><numFmt numFmtId="164" formatCode="0.00%"/></numFmts><fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="16"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF3F4F6"/></patternFill></fill></fills>'
            .'<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"/><right style="thin"/><top style="thin"/><bottom style="thin"/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="7"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0"/><xf numFmtId="4" fontId="2" fillId="0" borderId="1" xfId="0"/><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"/><xf numFmtId="4" fontId="0" fillId="0" borderId="1" xfId="0"/><xf numFmtId="164" fontId="2" fillId="0" borderId="1" xfId="0"/></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private function workbook(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Community Funding" sheetId="1" r:id="rId1"/></sheets></workbook>';
    }

    private function workbookRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
    }

    private function rootRelationships(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>';
    }

    private function contentTypes(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>';
    }

    private function escape(string $value): string
    {
        $value = (string) preg_replace('/[^\x{9}\x{A}\x{D}\x{20}-\x{D7FF}\x{E000}-\x{FFFD}]/u', '', mb_convert_encoding($value, 'UTF-8', 'UTF-8'));

        return htmlspecialchars($value, ENT_XML1 | ENT_COMPAT | ENT_SUBSTITUTE, 'UTF-8');
    }
}
