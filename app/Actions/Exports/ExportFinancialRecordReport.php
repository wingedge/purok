<?php

declare(strict_types=1);

namespace App\Actions\Exports;

use App\Actions\Reports\BuildFinancialRecordReport;
use App\Enums\FinancialReportType;
use RuntimeException;
use ZipArchive;

final class ExportFinancialRecordReport
{
    public function __construct(private readonly BuildFinancialRecordReport $buildReport) {}

    public function execute(FinancialReportType $type, int $year, ?int $month = null): string
    {
        $report = $this->buildReport->execute($type, $year, $month);
        $path = tempnam(sys_get_temp_dir(), 'purok-financial-report-');

        if ($path === false) {
            throw new RuntimeException('Unable to create a temporary workbook file.');
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to open a temporary workbook file.');
        }

        $zip->addFromString('[Content_Types].xml', $this->contentTypes());
        $zip->addFromString('_rels/.rels', $this->rootRelationships());
        $zip->addFromString('xl/workbook.xml', $this->workbook($type));
        $zip->addFromString('xl/_rels/workbook.xml.rels', $this->workbookRelationships());
        $zip->addFromString('xl/styles.xml', $this->styles());
        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheet($type, $report));
        $zip->close();

        $contents = file_get_contents($path);
        @unlink($path);

        if ($contents === false) {
            throw new RuntimeException('Unable to read the generated workbook.');
        }

        return $contents;
    }

    /** @param array<string, mixed> $report */
    private function sheet(FinancialReportType $type, array $report): string
    {
        $rows = [
            '<row r="1" ht="24" customHeight="1"><c r="A1" t="inlineStr" s="1"><is><t>'.$type->title().'</t></is></c></row>',
            '<row r="2"><c r="A2" t="inlineStr"><is><t>'.$this->escape('Period: '.$report['start']->format('M d, Y').' - '.$report['end']->format('M d, Y')).'</t></is></c></row>',
            '<row r="3"><c r="A3" t="inlineStr"><is><t>'.$this->escape('Generated: '.now()->format('Y-m-d H:i')).'</t></is></c></row>',
            '<row r="4"><c r="A4" t="inlineStr" s="2"><is><t>Total</t></is></c><c r="B4" s="3"><v>'.number_format($report['total'], 2, '.', '').'</v></c></row>',
            '<row r="6"><c r="A6" t="inlineStr" s="2"><is><t>Date</t></is></c><c r="B6" t="inlineStr" s="2"><is><t>'.$type->classificationLabel().'</t></is></c><c r="C6" t="inlineStr" s="2"><is><t>Description</t></is></c><c r="D6" t="inlineStr" s="2"><is><t>Amount</t></is></c></row>',
        ];
        $rowNumber = 7;

        foreach ($report['records'] as $record) {
            $classification = $type === FinancialReportType::Expense ? $record->category : $record->source;
            $rows[] = '<row r="'.$rowNumber.'">'
                .'<c r="A'.$rowNumber.'" t="inlineStr" s="4"><is><t>'.$record->date->format('Y-m-d').'</t></is></c>'
                .'<c r="B'.$rowNumber.'" t="inlineStr" s="4"><is><t>'.$this->escape((string) $classification).'</t></is></c>'
                .'<c r="C'.$rowNumber.'" t="inlineStr" s="4"><is><t>'.$this->escape((string) $record->description).'</t></is></c>'
                .'<c r="D'.$rowNumber.'" s="5"><v>'.number_format((float) $record->amount, 2, '.', '').'</v></c>'
                .'</row>';
            $rowNumber++;
        }

        if ($report['records']->isEmpty()) {
            $rows[] = '<row r="7"><c r="A7" t="inlineStr" s="4"><is><t>No records found for this period.</t></is></c></row>';
        }

        $lastRow = max(7, $rowNumber - 1);

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><dimension ref="A1:D'.$lastRow.'"/>'
            .'<sheetViews><sheetView workbookViewId="0"><pane ySplit="6" topLeftCell="A7" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<cols><col min="1" max="1" width="14" customWidth="1"/><col min="2" max="2" width="28" customWidth="1"/><col min="3" max="3" width="48" customWidth="1"/><col min="4" max="4" width="16" customWidth="1"/></cols>'
            .'<sheetData>'.implode('', $rows).'</sheetData><autoFilter ref="A6:D'.$lastRow.'"/>'
            .'<mergeCells count="3"><mergeCell ref="A1:D1"/><mergeCell ref="A2:D2"/><mergeCell ref="A3:D3"/></mergeCells></worksheet>';
    }

    private function styles(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="16"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FFF3F4F6"/></patternFill></fill></fills>'
            .'<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border><border><left style="thin"/><right style="thin"/><top style="thin"/><bottom style="thin"/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs><cellXfs count="6"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="2" fillId="2" borderId="1" xfId="0"/><xf numFmtId="4" fontId="2" fillId="0" borderId="1" xfId="0"/><xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"/><xf numFmtId="4" fontId="0" fillId="0" borderId="1" xfId="0"/></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private function workbook(FinancialReportType $type): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="'.ucfirst($type->value).'s" sheetId="1" r:id="rId1"/></sheets></workbook>';
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
