<?php

namespace App\Model\Expense\Service;

use App\Model\Expense\Entity\DocumentPrint\ExpenseDocumentPrint;
use App\Model\Expense\Entity\SchetFakPrint\SchetFakPrint;
use DateTime;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class SchetFakXlsHelper
{
    /**
     * @param Spreadsheet $spreadsheet
     * @return Spreadsheet
     * @throws Exception
     */
    public function merge(Spreadsheet $spreadsheet): Spreadsheet
    {
        $spreadsheet->getActiveSheet()->mergeCells('B1:AC1');
        $spreadsheet->getActiveSheet()->mergeCells('B2:AC2');
        $spreadsheet->getActiveSheet()->mergeCells('B3:AC3');
        $spreadsheet->getActiveSheet()->mergeCells('B4:AC4');
        $spreadsheet->getActiveSheet()->mergeCells('B5:AC5');
        $spreadsheet->getActiveSheet()->mergeCells('B6:AC6');
        $spreadsheet->getActiveSheet()->mergeCells('B7:AC7');
        $spreadsheet->getActiveSheet()->mergeCells('B8:AC8');
        $spreadsheet->getActiveSheet()->mergeCells('B9:AC9');
        $spreadsheet->getActiveSheet()->mergeCells('B10:AC10');
        $spreadsheet->getActiveSheet()->mergeCells('B11:AC11');
        $spreadsheet->getActiveSheet()->mergeCells('B12:AC12');
        $spreadsheet->getActiveSheet()->mergeCells('B13:AC13');
        $spreadsheet->getActiveSheet()->mergeCells('B14:AC14');
        $spreadsheet->getActiveSheet()->mergeCells('B15:AC15');
        return $spreadsheet;
    }

    public function mergeSumPage(Spreadsheet $spreadsheet, int $j): Spreadsheet
    {
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':K' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':M' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':O' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('Q' . $j . ':T' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':V' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('Y' . $j . ':Y' . $j . '');
        return $spreadsheet;
    }

    public function mergeHeaderPage(Spreadsheet $spreadsheet, int $j): Spreadsheet
    {
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':K' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':M' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':O' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('Q' . $j . ':T' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':V' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('Y' . $j . ':Y' . $j . '');
        return $spreadsheet;
    }

    public function mergeRowNames(Spreadsheet $spreadsheet, int $j): Spreadsheet
    {
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':C' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('D' . $j . ':D' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('E' . $j . ':H' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('F' . ($j + 1) . ':H' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('I' . $j . ':I' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('J' . $j . ':K' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':M' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':O' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('P' . $j . ':P' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('Q' . $j . ':T' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':V' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('W' . $j . ':X' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('Y' . $j . ':Y' . ($j + 1) . '');
        $spreadsheet->getActiveSheet()->mergeCells('Z' . $j . ':AA' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('AB' . $j . ':AB' . ($j + 1) . '');
        return $spreadsheet;
    }

    public function mergeRow(Spreadsheet $spreadsheet, int $j): Spreadsheet
    {
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':C' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('F' . $j . ':H' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('J' . $j . ':K' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':M' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':O' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('Q' . $j . ':T' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':V' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('Y' . $j . ':Y' . $j . '');
        return $spreadsheet;
    }

    public function mergeRowSum(Spreadsheet $spreadsheet, int $j): Spreadsheet
    {
        $spreadsheet->getActiveSheet()->mergeCells('B' . $j . ':K' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('L' . $j . ':M' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('N' . $j . ':O' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('Q' . $j . ':T' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('U' . $j . ':V' . $j . '');
        $spreadsheet->getActiveSheet()->mergeCells('Y' . $j . ':Y' . $j . '');
        return $spreadsheet;
    }

    public function header($aSheet, SchetFakPrint $schetFakPrint, string $document_num, DateTime $document_date, string $expense_document_num, DateTime $expense_document_date, int $countGoods)
    {
        $arStyles = $this->getStyles();

        $aSheet->getRowDimension(1)->setRowHeight(40.5);
        $aSheet->getRowDimension(4)->setRowHeight(11.25);
        $aSheet->getRowDimension(5)->setRowHeight(11.25);
        $aSheet->getRowDimension(6)->setRowHeight(11.25);
        $aSheet->getRowDimension(7)->setRowHeight(11.25);
        $aSheet->getRowDimension(8)->setRowHeight(11.25);
        $aSheet->getRowDimension(9)->setRowHeight(11.25);
        $aSheet->getRowDimension(10)->setRowHeight(11.25);
        $aSheet->getRowDimension(11)->setRowHeight(11.25);
        $aSheet->getRowDimension(12)->setRowHeight(11.25);
        $aSheet->getRowDimension(13)->setRowHeight(11.25);
        $aSheet->getRowDimension(14)->setRowHeight(11.25);
        $aSheet->getRowDimension(15)->setRowHeight(11.25);
        $aSheet->getRowDimension(16)->setRowHeight(11.25);
        $aSheet->getRowDimension(17)->setRowHeight(11.25);

        $aSheet->getColumnDimension('A')->setWidth(0.64);
        $aSheet->getColumnDimension('B')->setWidth(28);
        $aSheet->getColumnDimension('C')->setWidth(9);
        $aSheet->getColumnDimension('D')->setWidth(6);
        $aSheet->getColumnDimension('E')->setWidth(6);
        $aSheet->getColumnDimension('F')->setWidth(3.5);
        $aSheet->getColumnDimension('G')->setWidth(0.7);
        $aSheet->getColumnDimension('H')->setWidth(7.3);
        $aSheet->getColumnDimension('I')->setWidth(8);
        $aSheet->getColumnDimension('J')->setWidth(5.5);
        $aSheet->getColumnDimension('K')->setWidth(4.5);
        $aSheet->getColumnDimension('L')->setWidth(2.5);
        $aSheet->getColumnDimension('M')->setWidth(10.5);
        $aSheet->getColumnDimension('N')->setWidth(8);
        $aSheet->getColumnDimension('O')->setWidth(0.75);
        $aSheet->getColumnDimension('P')->setWidth(13);
        $aSheet->getColumnDimension('Q')->setWidth(0.65);
        $aSheet->getColumnDimension('R')->setWidth(1.6);
        $aSheet->getColumnDimension('S')->setWidth(9);
        $aSheet->getColumnDimension('T')->setWidth(1.4);
        $aSheet->getColumnDimension('U')->setWidth(11);
        $aSheet->getColumnDimension('V')->setWidth(3);
        $aSheet->getColumnDimension('W')->setWidth(8);
        $aSheet->getColumnDimension('X')->setWidth(11.5);
        $aSheet->getColumnDimension('Y')->setWidth(15);
        $aSheet->getColumnDimension('Z')->setWidth(8);
        $aSheet->getColumnDimension('AA')->setWidth(11.5);
        $aSheet->getColumnDimension('AB')->setWidth(25);
        $aSheet->getColumnDimension('AC')->setWidth(0.3);


// Заполнение ячеек

        $aSheet->getStyle('B1')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('B1')->applyFromArray($arStyles['topFont']);
        $aSheet->setCellValue("B1", "Приложение № 1\nк постановлению Правительства\nРоссийской Федерации\nот 26 декабря 2011 г. № 1137\n(в ред. Постановления Правительства РФ от 02.04.2021 № 534)");


        $aSheet->getStyle('B2')->applyFromArray($arStyles['headerFont']);
        $aSheet->setCellValue("B2", "Счет-фактура № $document_num от " . $document_date->format('d.m.Y'));

        $aSheet->getStyle('B3')->applyFromArray($arStyles['headerFont']);
        $aSheet->setCellValue("B3", "Исправление № -- от --");

        $aSheet->getStyle('B4')->applyFromArray($arStyles['textFont']);
        $aSheet->setCellValue("B4", "Продавец: " . $schetFakPrint->getFrom()->getName());

        $aSheet->getStyle('B5')->applyFromArray($arStyles['textFont']);
        $aSheet->setCellValue("B5", "Адрес: " . $schetFakPrint->getFrom()->getAddress());

        $aSheet->getStyle('B6')->applyFromArray($arStyles['textFont']);
        $aSheet->setCellValue("B6", "ИНН/КПП продавца: " . $schetFakPrint->getFrom()->getInn() . " / " . $schetFakPrint->getFrom()->getKpp());

        if ($schetFakPrint->getFromGruz()->getGruzName() != '') {
            $from_address_gruz = $schetFakPrint->getFromGruz()->getGruzName() . " " . $schetFakPrint->getFromGruz()->getGruzAddress();
        } else {
            $from_address_gruz = $schetFakPrint->getFrom()->getName() . " " . $schetFakPrint->getFrom()->getAddress();
        }

        $aSheet->getStyle('B7')->applyFromArray($arStyles['textFont']);
        $aSheet->setCellValue("B7", "Грузоотправитель и его адрес: " . $from_address_gruz);

        $aSheet->getStyle('B8')->applyFromArray($arStyles['textFont']);
        $aSheet->setCellValue("B8", "Грузополучатель и его адрес: " . $schetFakPrint->getToGruz()->getGruzName() . " " . $schetFakPrint->getToGruz()->getAddressGruz());

        $aSheet->getStyle('B9')->applyFromArray($arStyles['textFont']);
        $aSheet->setCellValue("B9", "К платежно-расчетному документу:                  от");

        $aSheet->getStyle('B10')->applyFromArray($arStyles['textFont']);
        $aSheet->setCellValue("B10", "Документ об отгрузке № п/п:     1 - " . $countGoods . "        №      " . $expense_document_num . "       от " . $expense_document_date->format('d.m.Y'));

        $aSheet->getStyle('B11')->applyFromArray($arStyles['textFont']);
        $aSheet->setCellValue("B11", "Покупатель: " . $schetFakPrint->getToCash()->getCashName());

        $aSheet->getStyle('B12')->applyFromArray($arStyles['textFont']);
        $aSheet->setCellValue("B12", "Адрес: " . $schetFakPrint->getToCash()->getAddressCash());

        $to_inn_cash_all = "";
        if ($schetFakPrint->getToCash()->getInnCash() != "")
            $to_inn_cash_all .= StripSlashes("" . $schetFakPrint->getToCash()->getInnCash());
        if ($schetFakPrint->getToCash()->getKppCash() != "")
            $to_inn_cash_all .= StripSlashes(" / " . $schetFakPrint->getToCash()->getKppCash());

        $aSheet->getStyle('B13')->applyFromArray($arStyles['textFont']);
        $aSheet->setCellValue("B13", "ИНН/КПП покупателя: " . $to_inn_cash_all);

        $aSheet->getStyle('B14')->applyFromArray($arStyles['textFont']);
        $aSheet->setCellValue("B14", "Валюта: код 643 наименование Российский рубль");

        $aSheet->getStyle('B15')->applyFromArray($arStyles['textFont']);
        $aSheet->setCellValue("B15", "Идентификатор государственного контракта, договора (соглашения) (при наличии)");

        return $aSheet;
    }

    public function rowSumPage($aSheet, array $sumPage, int $j)
    {
        $arStyles = $this->getStyles();

        $aSheet->getRowDimension($j)->setRowHeight(11.75);

        $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('B' . $j . ':K' . $j)->applyFromArray($arStyles['tableAllFont']);
        $aSheet->setCellValue("B" . $j . "", "Итого по странице");


        $aSheet->getStyle('L' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->getStyle('L' . $j . ':M' . $j)->applyFromArray($arStyles['tablePriceFont']);
        $aSheet->setCellValue("L" . $j . "", $sumPage['priceWithoutNds']);

        $aSheet->getStyle('N' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('N' . $j . ':P' . $j)->applyFromArray($arStyles['tableText1Font']);
        $aSheet->setCellValue("N" . $j . "", "X");

        $aSheet->getStyle('P' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("P" . $j . "", "");

        $aSheet->getStyle('Q' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->getStyle('Q' . $j . ':V' . $j)->applyFromArray($arStyles['tablePriceFont']);
        $aSheet->setCellValue("Q" . $j . "", $sumPage['ndsSum']);

        $aSheet->getStyle('U' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("U" . $j . "", $sumPage['sum']);

        return $aSheet;
    }

    public function rowHeaderPage($aSheet, int $page_num, int $j, string $document_num, DateTime $document_date)
    {
        $arStyles = $this->getStyles();

        $aSheet->getRowDimension($j)->setRowHeight(11.75);
        $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('B' . $j . ':K' . $j)->applyFromArray($arStyles['pageLeftFont']);
        $aSheet->setCellValue("B" . $j . "", "Счет-фактура № $document_num от " . $document_date->format('d.m.Y'));

        $aSheet->getStyle('AB' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('AB' . $j . '')->applyFromArray($arStyles['pageRightFont']);
        $aSheet->setCellValue("AB" . $j . "", "Страница: $page_num");

        return $aSheet;
    }

    public function rowNames($aSheet, int $j, int $page_num)
    {
        $arStyles = $this->getStyles();

        $aSheet->getRowDimension($j)->setRowHeight(54.25);
        $aSheet->getRowDimension($j + 1)->setRowHeight(32.75);

        $aSheet->getStyle('B' . $j . ':AB' . ($j + 1))->applyFromArray($arStyles['tableHeaderFont']);

        $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("B" . $j . "", "Наименование товара (описание выполненных работ, оказанных услуг), имущественного права");

        $aSheet->getStyle('D' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("D" . $j . "", "Код вида товара");

        $aSheet->getStyle('E' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("E" . $j . "", "Единица измерения");

        $aSheet->getStyle('E' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("E" . ($j + 1) . "", "код");

        $aSheet->getStyle('F' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("F" . ($j + 1) . "", "условное обозначение (национальное)");

        $aSheet->getStyle('I' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("I" . $j . "", "Коли-\nчество\n(объем)");

        $aSheet->getStyle('J' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("J" . $j . "", "Цена (тариф) за единицу измерения");

        $aSheet->getStyle('L' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("L" . $j . "", "Стоимость товаров (работ, услуг), имущественных прав без налога - всего");

        $aSheet->getStyle('N' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("N" . $j . "", "В том числе сумма акциза");

        $aSheet->getStyle('P' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("P" . $j . "", "Налоговая ставка");

        $aSheet->getStyle('Q' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("Q" . $j . "", "Сумма налога, предъявляемая покупателю");

        $aSheet->getStyle('U' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("U" . $j . "", "Стоимость товаров(работ, услуг), имущественных прав с налогом - всего");

        $aSheet->getStyle('W' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("W" . $j . "", "Страна происхождения товара");

        $aSheet->getStyle('W' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("W" . ($j + 1) . "", "цифровой код");

        $aSheet->getStyle('X' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("X" . ($j + 1) . "", "краткое наименование");

        $aSheet->getStyle('Y' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("Y" . $j . "", "Регистрационный номер таможенной декларации");

        $aSheet->getStyle('Z' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("Z" . $j . "", "Количественная единица измерения товара, используемая в целях осуществления прослеживаемости");

        $aSheet->getStyle('Z' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("Z" . ($j + 1) . "", "Код");

        $aSheet->getStyle('AA' . ($j + 1) . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("AA" . ($j + 1) . "", "Условное обозначение");

        $aSheet->getStyle('AB' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->setCellValue("AB" . $j . "", "Количество товара, подлежащего прослеживаемости, в количественной единице измерения товара, используемой в целях осуществления прослеживаемости");

        return $aSheet;
    }

    public function rowNamesNumbers($aSheet, int $j)
    {
        $arStyles = $this->getStyles();

        $aSheet->getStyle('B' . $j . ':AB' . $j)->applyFromArray($arStyles['tableNumberFont']);

        $aSheet->setCellValue("B" . $j . "", "1");
        $aSheet->setCellValue("D" . $j . "", "1a");
        $aSheet->setCellValue("E" . $j . "", "2");
        $aSheet->setCellValue("F" . $j . "", "2a");
        $aSheet->setCellValue("I" . $j . "", "3");
        $aSheet->setCellValue("J" . $j . "", "4");
        $aSheet->setCellValue("L" . $j . "", "5");
        $aSheet->setCellValue("N" . $j . "", "6");
        $aSheet->setCellValue("P" . $j . "", "7");
        $aSheet->setCellValue("Q" . $j . "", "8");
        $aSheet->setCellValue("U" . $j . "", "9");
        $aSheet->setCellValue("W" . $j . "", "10");
        $aSheet->setCellValue("X" . $j . "", "10a");
        $aSheet->setCellValue("Y" . $j . "", "11");
        $aSheet->setCellValue("Z" . $j . "", "12");
        $aSheet->setCellValue("AA" . $j . "", "12a");
        $aSheet->setCellValue("AB" . $j . "", "13");
        return $aSheet;
    }

    public function lastRowSum($aSheet, array $sum, int $j)
    {
        $arStyles = $this->getStyles();
        $aSheet->getStyle('B' . $j . '')->getAlignment()->setWrapText(true);

        $aSheet->getStyle('B' . $j . ':K' . $j)->applyFromArray($arStyles['tableAllFont']);
        $aSheet->setCellValue("B" . $j . "", "Всего к оплате");


        $aSheet->getStyle('L' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->getStyle('L' . $j . ':M' . $j)->applyFromArray($arStyles['tablePriceFont']);
        $aSheet->setCellValue("L" . $j . "", $sum['sumWithoutNds']);

        $aSheet->getStyle('N' . $j . '')->getAlignment()->setWrapText(true);
        $aSheet->getStyle('N' . $j . ':P' . $j)->applyFromArray($arStyles['tableText1Font']);
        $aSheet->setCellValue("N" . $j . "", "X");

        $aSheet->getStyle('P' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("P" . $j . "", "");

        $aSheet->getStyle('Q' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->getStyle('Q' . $j . ':V' . $j)->applyFromArray($arStyles['tablePriceFont']);
        $aSheet->setCellValue("Q" . $j . "", $sum['ndsSum']);

        $aSheet->getStyle('U' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
        $aSheet->setCellValue("U" . $j . "", $sum['sum']);

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
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_BOTTOM
                ),
            ),
            'tableHeaderFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '8',
                    'bold' => false
                ),
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ),
            ),
            'tableNumberFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '6',
                    'bold' => false
                ),
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ),
            ),
            'tableTextFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '8',
                    'bold' => false
                ),
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_TOP
                ),
            ),
            'tableText1Font' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '8',
                    'bold' => false
                ),
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_TOP
                ),
            ),
            'tablePriceFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '8',
                    'bold' => false
                ),
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
                'alignment' => array(
                    'vertical' => Alignment::VERTICAL_TOP
                ),
            ),
            'tableAllFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '8',
                    'bold' => true
                ),
                'borders' => array(
                    'allBorders' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_TOP
                ),
            ),
            'podpFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '8',
                    'bold' => false
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_BOTTOM
                ),
            ),
            'podp1Font' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '8',
                    'bold' => false
                ),
                'borders' => array(
                    'bottom' => array(
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => array('rgb' => '000000'),
                    ),
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_BOTTOM
                ),
            ),
            'podp2Font' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '6',
                    'bold' => false
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_TOP
                ),
            ),
            'pageLeftFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '8',
                    'bold' => false
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'vertical' => Alignment::VERTICAL_BOTTOM
                ),
            ),
            'pageRightFont' => array(
                'font' => array(
                    'name' => 'Arial Cyr',
                    'size' => '8',
                    'bold' => false
                ),
                'alignment' => array(
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                    'vertical' => Alignment::VERTICAL_BOTTOM
                ),
            )
        ];
    }

}