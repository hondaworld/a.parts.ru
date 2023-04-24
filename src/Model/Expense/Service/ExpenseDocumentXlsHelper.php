<?php

namespace App\Model\Expense\Service;

use App\Model\Expense\Entity\DocumentPrint\ExpenseDocumentPrint;
use DateTime;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class ExpenseDocumentXlsHelper
{
    /**
     * @param Spreadsheet $spreadsheet
     * @return Spreadsheet
     * @throws Exception
     */
    public function merge(Spreadsheet $spreadsheet): Spreadsheet
    {
        $spreadsheet->getActiveSheet()->mergeCells('B1:AP1');
        $spreadsheet->getActiveSheet()->mergeCells('AO2:AP2');
        $spreadsheet->getActiveSheet()->mergeCells('AO3:AP3');
        $spreadsheet->getActiveSheet()->mergeCells('AO4:AP4');
        $spreadsheet->getActiveSheet()->mergeCells('AO5:AP6');
        $spreadsheet->getActiveSheet()->mergeCells('AO7:AP7');
        $spreadsheet->getActiveSheet()->mergeCells('AO8:AP8');
        $spreadsheet->getActiveSheet()->mergeCells('AO9:AP10');
        $spreadsheet->getActiveSheet()->mergeCells('AO11:AP12');
        $spreadsheet->getActiveSheet()->mergeCells('AO13:AP14');
        $spreadsheet->getActiveSheet()->mergeCells('AO15:AP15');
        $spreadsheet->getActiveSheet()->mergeCells('AO16:AP16');
        $spreadsheet->getActiveSheet()->mergeCells('AO17:AP17');
        $spreadsheet->getActiveSheet()->mergeCells('AO18:AP18');

        $spreadsheet->getActiveSheet()->mergeCells('B3:AH4');
        $spreadsheet->getActiveSheet()->mergeCells('AJ3:AN3');
        $spreadsheet->getActiveSheet()->mergeCells('AL4:AN4');

        $spreadsheet->getActiveSheet()->mergeCells('D5:AH5');

        $spreadsheet->getActiveSheet()->mergeCells('G7:AA7');
        $spreadsheet->getActiveSheet()->mergeCells('AB7:AN7');

        $spreadsheet->getActiveSheet()->mergeCells('B8:C8');
        $spreadsheet->getActiveSheet()->mergeCells('D8:AK8');
        $spreadsheet->getActiveSheet()->mergeCells('AL8:AN8');

        $spreadsheet->getActiveSheet()->mergeCells('D9:AH9');

        $spreadsheet->getActiveSheet()->mergeCells('B10:C10');
        $spreadsheet->getActiveSheet()->mergeCells('D10:AK10');
        $spreadsheet->getActiveSheet()->mergeCells('AL10:AN10');

        $spreadsheet->getActiveSheet()->mergeCells('D11:AH11');

        $spreadsheet->getActiveSheet()->mergeCells('B12:C12');
        $spreadsheet->getActiveSheet()->mergeCells('D12:AK12');
        $spreadsheet->getActiveSheet()->mergeCells('AL12:AN12');

        $spreadsheet->getActiveSheet()->mergeCells('D13:AH13');

        $spreadsheet->getActiveSheet()->mergeCells('B14:C14');
        $spreadsheet->getActiveSheet()->mergeCells('D14:AK14');
        $spreadsheet->getActiveSheet()->mergeCells('AM13:AN14');

        $spreadsheet->getActiveSheet()->mergeCells('B15:C15');
        $spreadsheet->getActiveSheet()->mergeCells('D15:AK15');
        $spreadsheet->getActiveSheet()->mergeCells('AM15:AN15');

        $spreadsheet->getActiveSheet()->mergeCells('N16:P16');
        $spreadsheet->getActiveSheet()->mergeCells('Q16:W16');
        $spreadsheet->getActiveSheet()->mergeCells('AE16:AK16');
        $spreadsheet->getActiveSheet()->mergeCells('AM16:AN16');

        $spreadsheet->getActiveSheet()->mergeCells('H17:M17');
        $spreadsheet->getActiveSheet()->mergeCells('N17:P17');
        $spreadsheet->getActiveSheet()->mergeCells('Q17:W17');
        $spreadsheet->getActiveSheet()->mergeCells('AM17:AN17');

        $spreadsheet->getActiveSheet()->mergeCells('AJ18:AN18');

        return $spreadsheet;
    }

    public function mergeSumPage(Spreadsheet $spreadsheet, int $j): Spreadsheet
    {
        $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':Q' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('R' . $j . ':U' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('V' . $j . ':X' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('Y' . $j . ':Z' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AA' . $j . ':AC' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AD' . $j . ':AF' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AG' . $j . ':AJ' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AK' . $j . ':AM' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AN' . $j . ':AO' . $j . '');
        return $spreadsheet;
    }

    public function mergeRowNames(Spreadsheet $spreadsheet, int $j): Spreadsheet
    {
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':B' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':I' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('C' . ($j + 1) . ':F' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('G' . ($j + 1) . ':I' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('J' . $j . ':N' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('J' . ($j + 1) . ':L' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('M' . ($j + 1) . ':N' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('O' . $j . ':O' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('P' . $j . ':U' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('P' . ($j + 1) . ':Q' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('R' . ($j + 1) . ':U' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('V' . $j . ':X' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('Y' . $j . ':Z' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('AA' . $j . ':AC' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('AD' . $j . ':AF' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('AG' . $j . ':AM' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AG' . ($j + 1) . ':AJ' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('AK' . ($j + 1) . ':AM' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('AN' . $j . ':AO' . ($j + 1) . '');
        return $spreadsheet;
    }

    public function mergeRow(Spreadsheet $spreadsheet, int $j): Spreadsheet
    {
        $spreadsheet->getActiveSheet()->mergeCells('C' . $j . ':F' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('G' . $j . ':I' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('J' . $j . ':L' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('M' . $j . ':N' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('P' . $j . ':Q' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('R' . $j . ':U' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('V' . $j . ':X' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('Y' . $j . ':Z' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AA' . $j . ':AC' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AD' . $j . ':AF' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AG' . $j . ':AJ' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AK' . $j . ':AM' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AN' . $j . ':AO' . $j . '');
        return $spreadsheet;
    }

    public function mergeRowSum(Spreadsheet $spreadsheet, int $j): Spreadsheet
    {
        $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':Q' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('R' . $j . ':U' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('V' . $j . ':X' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('Y' . $j . ':Z' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AA' . $j . ':AC' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AD' . $j . ':AF' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AG' . $j . ':AJ' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AK' . $j . ':AM' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AN' . $j . ':AO' . $j . '');
        return $spreadsheet;
    }

    public function header($aSheet, ExpenseDocumentPrint $expenseDocumentPrint, string $document_num, DateTime $document_date)
    {
        $arStyles = $this->getStyles();

        $aSheet->getRowDimension(1)->setRowHeight(17);
        $aSheet->getRowDimension(3)->setRowHeight(16.25);
        $aSheet->getRowDimension(4)->setRowHeight(16.5);
        $aSheet->getRowDimension(5)->setRowHeight(8);
        $aSheet->getRowDimension(7)->setRowHeight(14.75);
        $aSheet->getRowDimension(8)->setRowHeight(34.5);
        $aSheet->getRowDimension(9)->setRowHeight(8);
        $aSheet->getRowDimension(10)->setRowHeight(22.5);
        $aSheet->getRowDimension(11)->setRowHeight(8);
        $aSheet->getRowDimension(12)->setRowHeight(34.5);
        $aSheet->getRowDimension(13)->setRowHeight(8);


        $aSheet->getColumnDimension('A')->setWidth(0.64);
        $aSheet->getColumnDimension('B')->setWidth(4.83);
        $aSheet->getColumnDimension('C')->setWidth(11.5);
        $aSheet->getColumnDimension('D')->setWidth(1);
        $aSheet->getColumnDimension('E')->setWidth(0.27);
        $aSheet->getColumnDimension('F')->setWidth(13);
        $aSheet->getColumnDimension('G')->setWidth(0.55);
        $aSheet->getColumnDimension('H')->setWidth(4);
        $aSheet->getColumnDimension('I')->setWidth(9.33);
        $aSheet->getColumnDimension('J')->setWidth(5.83);
        $aSheet->getColumnDimension('K')->setWidth(1.33);
        $aSheet->getColumnDimension('L')->setWidth(0.36);
        $aSheet->getColumnDimension('M')->setWidth(0.82);
        $aSheet->getColumnDimension('N')->setWidth(5);
        $aSheet->getColumnDimension('O')->setWidth(6);
        $aSheet->getColumnDimension('P')->setWidth(2.5);
        $aSheet->getColumnDimension('Q')->setWidth(2.67);
        $aSheet->getColumnDimension('R')->setWidth(0.45);
        $aSheet->getColumnDimension('S')->setWidth(1.33);
        $aSheet->getColumnDimension('T')->setWidth(3.67);
        $aSheet->getColumnDimension('U')->setWidth(0.91);
        $aSheet->getColumnDimension('V')->setWidth(2.5);
        $aSheet->getColumnDimension('W')->setWidth(3.33);
        $aSheet->getColumnDimension('X')->setWidth(0.91);
        $aSheet->getColumnDimension('Y')->setWidth(4.33);
        $aSheet->getColumnDimension('Z')->setWidth(4.33);
        $aSheet->getColumnDimension('AA')->setWidth(8);
        $aSheet->getColumnDimension('AB')->setWidth(1);
        $aSheet->getColumnDimension('AC')->setWidth(0.73);
        $aSheet->getColumnDimension('AD')->setWidth(2.5);
        $aSheet->getColumnDimension('AE')->setWidth(2.67);
        $aSheet->getColumnDimension('AF')->setWidth(7.33);
        $aSheet->getColumnDimension('AG')->setWidth(0.09);
        $aSheet->getColumnDimension('AH')->setWidth(0.09);
        $aSheet->getColumnDimension('AI')->setWidth(1);
        $aSheet->getColumnDimension('AJ')->setWidth(7.17);
        $aSheet->getColumnDimension('AK')->setWidth(3.33);
        $aSheet->getColumnDimension('AL')->setWidth(2);
        $aSheet->getColumnDimension('AM')->setWidth(5.33);
        $aSheet->getColumnDimension('AN')->setWidth(1);
        $aSheet->getColumnDimension('AO')->setWidth(10.17);
        $aSheet->getColumnDimension('AP')->setWidth(0.64);


        $aSheet->getStyle('AO2')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_center'])->applyFromArray($arStyles['border_bottom']);
        $aSheet->getStyle('AP2')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_center'])->applyFromArray($arStyles['border_bottom']);
        $aSheet->setCellValue("AO2", "Коды");


// Заполнение ячеек
        $aSheet->getStyle('B4:AK4')->applyFromArray($arStyles['border_bottom1']);

        $aSheet->getStyle('AO4:AO17')->applyFromArray($arStyles['border_left_right']);
        $aSheet->getStyle('AP4:AP17')->applyFromArray($arStyles['border_left_right']);


        $aSheet->getStyle('B1')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('B1')->applyFromArray($arStyles['topFont']);
        $aSheet->setCellValue("B1", "Унифицированная форма № ТОРГ-12\nУтверждена постановлением Госкомстата России от 25.12.98 № 132");


        $aSheet->getStyle('B3')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('B3')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_left_bottom'])->applyFromArray($arStyles['border_bottom1']);
        $aSheet->setCellValue("B3", $expenseDocumentPrint->getFromGruz()->getGruz());


        $aSheet->getStyle('AJ3')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("AJ3", "Форма по ОКУД");

        $aSheet->getStyle('AL4')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("AL4", "по ОКПО");

        $aSheet->getStyle('AO3')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_center_bottom'])->applyFromArray($arStyles['border_top_left_right']);
        $aSheet->getStyle('AP3')->applyFromArray($arStyles['border_top_left_right']);
        $aSheet->setCellValue("AO3", "");

        $aSheet->getStyle('AO4')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $aSheet->getStyle('AO4')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_center_bottom']);

        $aSheet->setCellValue("AO4", " " . $expenseDocumentPrint->getFromGruz()->getGruzOkpo());

        $aSheet->getStyle('D5')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("D5", "организация-грузоотправитель, адрес, телефон, факс, банковские реквизиты");

        $aSheet->getStyle('B6:AK6')->applyFromArray($arStyles['border_bottom1']);
        $aSheet->getStyle('AO5')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_center']);

        $aSheet->getStyle('G7')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("G7", "структурное подразделение");

        $aSheet->getStyle('AB7')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("AB7", "Вид деятельности по ОКДП");

        $aSheet->getStyle('AO7')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_center_bottom']);
        $aSheet->setCellValue("AO7", "");

        $aSheet->getStyle('B8')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("B8", "Грузополучатель");

        $aSheet->getStyle('D8')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('D8')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_left_bottom'])->applyFromArray($arStyles['border_bottom1']);
        $aSheet->getStyle('E8:AK8')->applyFromArray($arStyles['border_bottom1']);
        $aSheet->setCellValue("D8", $expenseDocumentPrint->getToGruz()->getGruz());

        $aSheet->getStyle('AL8')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("AL8", "по ОКПО");

        $aSheet->getStyle('AO8')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $aSheet->getStyle('AO8')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_center_bottom']);
        $aSheet->setCellValue("AO8", " " . $expenseDocumentPrint->getToGruz()->getGruzOkpo());

        $aSheet->getStyle('D9')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("D9", "организация, адрес, телефон, факс, банковские реквизиты");

        $aSheet->getStyle('B10')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("B10", "Поставщик");

        $aSheet->getStyle('D10')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('D10')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_left_bottom'])->applyFromArray($arStyles['border_bottom1']);
        $aSheet->getStyle('E10:AK10')->applyFromArray($arStyles['border_bottom1']);
        $aSheet->setCellValue("D10", $expenseDocumentPrint->getFrom()->getNakladnaya());

        $aSheet->getStyle('AL10')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("AL10", "по ОКПО");

        $aSheet->getStyle('AO9')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $aSheet->getStyle('AO9')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_center_bottom']);
        $aSheet->setCellValue("AO9", " " . $expenseDocumentPrint->getFrom()->getOkpo());

        $aSheet->getStyle('D11')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("D11", "организация, адрес, телефон, факс, банковские реквизиты");

        $aSheet->getStyle('B12')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("B12", "Плательщик");

        $aSheet->getStyle('D12')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('D12')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_left_bottom'])->applyFromArray($arStyles['border_bottom1']);
        $aSheet->getStyle('E12:AK12')->applyFromArray($arStyles['border_bottom1']);
        $aSheet->setCellValue("D12", $expenseDocumentPrint->getToCash()->getCash());

        $aSheet->getStyle('AL12')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("AL12", "по ОКПО");

        $aSheet->getStyle('AO11')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
        $aSheet->getStyle('AO11')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_center_bottom']);
        $aSheet->setCellValue("AO11", " " . $expenseDocumentPrint->getToCash()->getCashOkpo());

        $aSheet->getStyle('D13')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("D13", "организация, адрес, телефон, факс, банковские реквизиты");

        $aSheet->getStyle('B14')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("B14", "Основание");

        $aSheet->getStyle('D14')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('D14')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_left_bottom'])->applyFromArray($arStyles['border_bottom1']);
        $aSheet->getStyle('E14:AK14')->applyFromArray($arStyles['border_bottom1']);
        $aSheet->setCellValue("D14", $expenseDocumentPrint->getNakladnayaosn());

        $aSheet->getStyle('AM13')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom'])->applyFromArray($arStyles['border']);
        $aSheet->getStyle('AN13')->applyFromArray($arStyles['border']);
        $aSheet->getStyle('AM14')->applyFromArray($arStyles['border']);
        $aSheet->getStyle('AN14')->applyFromArray($arStyles['border']);
        $aSheet->setCellValue("AM13", "номер");

        $aSheet->getStyle('AO13')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_center_bottom']);
        $aSheet->setCellValue("AO13", "");

        $aSheet->getStyle('D15')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center_top']);
        $aSheet->setCellValue("D15", "договор, заказ-наряд");

        $aSheet->getStyle('AM15')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom'])->applyFromArray($arStyles['border']);
        $aSheet->getStyle('AN15')->applyFromArray($arStyles['border']);
        $aSheet->setCellValue("AM15", "дата");

        $aSheet->getStyle('AO15')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_center_bottom']);
        $aSheet->setCellValue("AO15", "");

        $aSheet->getStyle('N16')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_left'])->applyFromArray($arStyles['border']);
        $aSheet->getStyle('O16')->applyFromArray($arStyles['border']);
        $aSheet->getStyle('P16')->applyFromArray($arStyles['border']);
        $aSheet->setCellValue("N16", "Номер документа");

        $aSheet->getStyle('Q16')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_left'])->applyFromArray($arStyles['border']);
        $aSheet->getStyle('R16:W16')->applyFromArray($arStyles['border']);
        $aSheet->setCellValue("Q16", "Дата составления");

        $aSheet->getStyle('AE16')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right']);
        $aSheet->setCellValue("AE16", "Транспортная накладная");

        $aSheet->getStyle('AM16')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom'])->applyFromArray($arStyles['border']);
        $aSheet->getStyle('AN16')->applyFromArray($arStyles['border']);
        $aSheet->setCellValue("AM16", "номер");

        $aSheet->getStyle('AO16')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_center_bottom']);
        $aSheet->setCellValue("AO16", "");

        $aSheet->getStyle('H17')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_right']);
        $aSheet->setCellValue("H17", "ТОВАРНАЯ НАКЛАДНАЯ");

        $aSheet->getStyle('N17')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_center'])->applyFromArray($arStyles['border_medium']);
        $aSheet->getStyle('O17')->applyFromArray($arStyles['border_medium']);
        $aSheet->getStyle('P17')->applyFromArray($arStyles['border_medium']);
        $aSheet->setCellValue("N17", $document_num);

        $aSheet->getStyle('Q17')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_center'])->applyFromArray($arStyles['border_medium']);
        $aSheet->getStyle('R17:W17')->applyFromArray($arStyles['border_medium']);
        $aSheet->setCellValue("Q17", $document_date->format('d.m.Y'));

        $aSheet->getStyle('AM17')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom'])->applyFromArray($arStyles['border']);
        $aSheet->getStyle('AN17')->applyFromArray($arStyles['border']);
        $aSheet->setCellValue("AM17", "дата");

        $aSheet->getStyle('AO17')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_center_bottom']);
        $aSheet->setCellValue("AO17", "");


        $aSheet->getStyle('AJ18')->applyFromArray($arStyles['textBigFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("AJ18", "Вид операции");

        $aSheet->getStyle('AO18')->applyFromArray($arStyles['textBigBoldFont'])->applyFromArray($arStyles['align_center_bottom'])->applyFromArray($arStyles['border_left_right_bottom']);
        $aSheet->getStyle('AP18')->applyFromArray($arStyles['border_left_right_bottom']);
        $aSheet->setCellValue("AO18", "");

        return $aSheet;
    }

    public function rowSumPage($aSheet, array $sumPage, int $j)
    {
        $arStyles = $this->getStyles();

        $aSheet->getStyle('N' . $j . '')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_right']);
        $aSheet->setCellValue("N" . $j . "", "Итого");

        $aSheet->getStyle('R' . $j . ':AO' . $j)->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['border']);
        $aSheet->setCellValue("R" . $j . "", "");
        $aSheet->setCellValue("V" . $j . "", "");
        $aSheet->getStyle('Y' . $j . '')->applyFromArray($arStyles['align_right']);
        $aSheet->setCellValue("Y" . $j . "", $sumPage['quantity'] . ",000");

        $aSheet->getStyle('AA' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("AA" . $j . "", $sumPage['priceWithoutNds']);

        $aSheet->getStyle('AD' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("AD" . $j . "", $sumPage['sumWithoutNds']);

        $aSheet->getStyle('AG' . $j . '')->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("AG" . $j . "", "X");

        $aSheet->getStyle('AK' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("AK" . $j . "", $sumPage['ndsSum']);

        $aSheet->getStyle('AN' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("AN" . $j . "", $sumPage['sum']);

        return $aSheet;
    }

    public function rowNames($aSheet, int $j, int $page_num)
    {
        $arStyles = $this->getStyles();

        $aSheet->getStyle('AO' . $j)->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['italicFont'])->applyFromArray($arStyles['align_right_bottom']);
        $aSheet->setCellValue("AO" . $j, "Страница " . $page_num);

        $j++;

        $aSheet->getRowDimension($j)->setRowHeight(11);
        $aSheet->getRowDimension($j + 1)->setRowHeight(20.75);

        $aSheet->getStyle('B' . $j . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('C' . $j . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('J' . $j . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('O' . $j . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('P' . $j . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('V' . $j . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('Y' . $j . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('AA' . $j . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('AD' . $j . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('AG' . $j . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);

        $aSheet->getStyle('C' . ($j + 1) . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('G' . ($j + 1) . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('J' . ($j + 1) . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('M' . ($j + 1) . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('P' . ($j + 1) . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('R' . ($j + 1) . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('AG' . ($j + 1) . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('AK' . ($j + 1) . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('AN' . $j . '')->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);


        $aSheet->getStyle('B' . $j)->applyFromArray($arStyles['border']);
        $aSheet->getStyle('C' . $j)->applyFromArray($arStyles['border']);
        $aSheet->getStyle('B' . $j . ':AO' . $j)->applyFromArray($arStyles['border']);
        $aSheet->getStyle('B' . ($j + 1) . ':AO' . ($j + 1))->applyFromArray($arStyles['border']);

        $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);


        $aSheet->setCellValue("B" . $j . "", "Но-\nмер по по-\nрядку");

        $aSheet->getStyle('J' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("J" . $j . "", "Единица измерения");

        $aSheet->getStyle('J' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("C" . ($j + 1) . "", "наиме-\nнование");
        $aSheet->getStyle('M' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("M" . ($j + 1) . "", "код по\nОКЕИ");

        $aSheet->getStyle('C' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("C" . $j . "", "Товар");

        $aSheet->getStyle('C' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("C" . ($j + 1) . "", "наименование товара");
        $aSheet->getStyle('G' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("G" . ($j + 1) . "", "артикул");

        $aSheet->getStyle('O' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("O" . $j . "", "Вид\nупаковки");

        $aSheet->getStyle('P' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("P" . $j . "", "Количество");

        $aSheet->getStyle('P' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("P" . ($j + 1) . "", "в одном\nместе");
        $aSheet->getStyle('R' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("R" . ($j + 1) . "", "мест,\nштук");

        $aSheet->getStyle('V' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("V" . $j . "", "Масса брутто");

        $aSheet->getStyle('Y' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("Y" . $j . "", "Коли-\nчество\n(масса\nнетто)");

        $aSheet->getStyle('AA' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("AA" . $j . "", "Цена,\nруб. коп.");

        $aSheet->getStyle('AD' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("AD" . $j . "", "Сумма без\nучета НДС,\nруб. коп.");

        $aSheet->getStyle('AG' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("AG" . $j . "", "НДС");

        $aSheet->getStyle('AG' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("AG" . ($j + 1) . "", "ставка, %");
        $aSheet->getStyle('AK' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("AK" . ($j + 1) . "", "сумма,\nруб. коп.");

        $aSheet->getStyle('AN' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("AN" . $j . "", "Сумма с\nучетом\nНДС,\nруб. коп.");

        return $aSheet;
    }

    public function rowNamesNumbers($aSheet, int $j)
    {
        $arStyles = $this->getStyles();

        $aSheet->getStyle('B' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('C' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('G' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('J' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('M' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('O' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('P' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('R' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('V' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('Y' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('AA' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('AD' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('AG' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('AK' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);
        $aSheet->getStyle('AN' . $j)->applyFromArray($arStyles['textSmallFont'])->applyFromArray($arStyles['align_center']);

        $aSheet->getStyle('B' . $j . ':AO' . $j)->applyFromArray($arStyles['border']);

        $aSheet->setCellValue("B" . $j . "", "1");
        $aSheet->setCellValue("C" . $j . "", "2");
        $aSheet->setCellValue("G" . $j . "", "3");
        $aSheet->setCellValue("J" . $j . "", "4");
        $aSheet->setCellValue("M" . $j . "", "5");
        $aSheet->setCellValue("O" . $j . "", "6");
        $aSheet->setCellValue("P" . $j . "", "7");
        $aSheet->setCellValue("R" . $j . "", "8");
        $aSheet->setCellValue("V" . $j . "", "9");
        $aSheet->setCellValue("Y" . $j . "", "10");
        $aSheet->setCellValue("AA" . $j . "", "11");
        $aSheet->setCellValue("AD" . $j . "", "12");
        $aSheet->setCellValue("AG" . $j . "", "13");
        $aSheet->setCellValue("AK" . $j . "", "14");
        $aSheet->setCellValue("AN" . $j . "", "15");

        return $aSheet;
    }

    public function lastRowSumPage($aSheet, array $sumPage, int $j)
    {
        $arStyles = $this->getStyles();
        $aSheet->getStyle('N'.$j.'')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_right']);
        $aSheet->getStyle('R' . $j . ':AO' . $j)->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['border']);

        $aSheet->setCellValue("N" . $j . "", "Итого");
        $aSheet->setCellValue("R" . $j . "", "");
        $aSheet->setCellValue("V" . $j . "", "");
        $aSheet->getStyle('Y' . $j . '')->applyFromArray($arStyles['align_right']);
        $aSheet->setCellValue("Y" . $j . "", $sumPage['quantity'] . ",000");
        $aSheet->getStyle('AA' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("AA" . $j . "", $sumPage['priceWithoutNds']);
        $aSheet->getStyle('AD' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("AD" . $j . "", $sumPage['sumWithoutNds']);
        $aSheet->getStyle('AG' . $j . '')->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("AG" . $j . "", "X");
        $aSheet->getStyle('AK' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("AK" . $j . "", $sumPage['ndsSum']);
        $aSheet->getStyle('AN' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("AN" . $j . "", $sumPage['sum']);

        return $aSheet;
    }

    public function lastRowSum($aSheet, array $sum, int $j)
    {
        $arStyles = $this->getStyles();
        $aSheet->getStyle('N'.$j.'')->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['align_right']);
        $aSheet->getStyle('R' . $j . ':AO' . $j)->applyFromArray($arStyles['textFont'])->applyFromArray($arStyles['border']);

        $aSheet->setCellValue("N" . $j . "", "Всего по накладной");
        $aSheet->setCellValue("R" . $j . "", "");
        $aSheet->setCellValue("V" . $j . "", "");
        $aSheet->getStyle('Y' . $j . '')->applyFromArray($arStyles['align_right']);
        $aSheet->setCellValue("Y" . $j . "", $sum['quantity'] . ",000");
        $aSheet->getStyle('AA' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("AA" . $j . "", $sum['priceWithoutNds']);
        $aSheet->getStyle('AD' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("AD" . $j . "", $sum['sumWithoutNds']);
        $aSheet->getStyle('AG' . $j . '')->applyFromArray($arStyles['align_center']);
        $aSheet->setCellValue("AG" . $j . "", "X");
        $aSheet->getStyle('AK' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("AK" . $j . "", $sum['ndsSum']);
        $aSheet->getStyle('AN' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("AN" . $j . "", $sum['sum']);

        return $aSheet;
    }

    public function getStyles(): array
    {
        return [
            'topFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '6',
                    'bold' => false
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_TOP
                ),
            ),
            'headerFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '14',
                    'bold' => true
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_TOP
                ),
            ),
            'textFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '8',
                    'bold' => false
                )
            ),
            'textSmallFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '6',
                    'bold' => false
                )
            ),
            'textMediumFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '7',
                    'bold' => false
                )
            ),
            'textBigFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '9',
                    'bold' => false
                )
            ),
            'textBigBoldFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '9',
                    'bold' => true
                )
            ),
            'italicFont' => array(
                'font' => array(
                    'italic' => true
                )
            ),
            'boldFont' => array(
                'font' => array(
                    'bold' => true
                )
            ),
            'border' => array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            ),
            'border_medium' => array(
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            ),
            'border_bottom' => array(
                'borders' => array(
                    'top' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'left' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'right' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'bottom' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            ),
            'border_top' => array(
                'borders' => array(
                    'top' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                    'left' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'right' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'bottom' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            ),
            'border_top_left_right' => array(
                'borders' => array(
                    'top' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                    'left' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                    'right' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                    'bottom' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            ),
            'border_top_right' => array(
                'borders' => array(
                    'top' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                    'left' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'right' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                    'bottom' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            ),
            'border_left_right' => array(
                'borders' => array(
                    'inside' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'top' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'left' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                    'right' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                    'bottom' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            ),
            'border_right' => array(
                'borders' => array(
                    'top' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'left' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'right' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                    'bottom' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            ),
            'border_left_right_bottom' => array(
                'borders' => array(
                    'top' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'left' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                    'right' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                    'bottom' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            ),
            'border_right_bottom' => array(
                'borders' => array(
                    'top' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'left' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                    'right' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                    'bottom' => array(
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            ),
            'border_bottom1' => array(
                'borders' => array(
                    'bottom' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            ),
            'border_left1' => array(
                'borders' => array(
                    'left' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
            ),
            'align_left' => array(
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ),
            ),
            'align_left_bottom' => array(
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_BOTTOM
                ),
            ),
            'align_center' => array(
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ),
            ),
            'align_center_bottom' => array(
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_BOTTOM
                ),
            ),
            'align_center_top' => array(
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_TOP
                ),
            ),
            'align_right' => array(
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_CENTER
                ),
            ),
            'align_right_bottom' => array(
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_BOTTOM
                ),
            )
        ];
    }

}