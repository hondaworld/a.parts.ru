<?php


namespace App\Model\Income\Service\IncomeOrder;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class Usa extends ExcelPrice implements IncomeOrderExcelImpl
{
    public function create(): string
    {
        $filename = "order" . $this->zapSklad . $this->incomeOrder->getProvider()->getId() . "_" . $this->incomeOrder->getDocumentNum() . ".xls";
        $path = $this->pathIncomeFiles . "/income/" . $filename;

        $spreadsheet = new Spreadsheet();
        $aSheet = $spreadsheet->getActiveSheet();
        $aSheet->getPageMargins()->setTop(0);
        $aSheet->getPageMargins()->setLeft(0);
        $aSheet->getPageMargins()->setRight(0);
        $aSheet->getPageMargins()->setBottom(0);
        $aSheet
            ->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT);

        // Стили
        $headerFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP
            ),
        );
        $textFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP
            ),
        );
        $tablePriceFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => false
            ),
            'alignment' => array(
                'vertical' => Alignment::VERTICAL_TOP
            ),
        );

        $aSheet->getColumnDimension('A')->setWidth(6);
        $aSheet->getColumnDimension('B')->setWidth(15);
        $aSheet->getColumnDimension('C')->setWidth(15);
        $aSheet->getColumnDimension('D')->setWidth(11);
        $aSheet->getColumnDimension('E')->setWidth(35);
        $aSheet->getColumnDimension('F')->setWidth(35);
        $aSheet->getColumnDimension('G')->setWidth(11);
        $aSheet->getColumnDimension('H')->setWidth(6);
        $aSheet->getColumnDimension('I')->setWidth(6);
        $aSheet->getColumnDimension('J')->setWidth(15);
        $aSheet->getColumnDimension('K')->setWidth(15);


        $aSheet->getStyle('A1')->applyFromArray($headerFont);
        $aSheet->setCellValue("A1", "№");

        $aSheet->getStyle('B1')->applyFromArray($headerFont);
        $aSheet->setCellValue("B1", "Номер заказа");

        $aSheet->getStyle('C1')->applyFromArray($headerFont);
        $aSheet->setCellValue("C1", "Код заказа");

        $aSheet->getStyle('D1')->applyFromArray($headerFont);
        $aSheet->setCellValue("D1", "Марка");

        $aSheet->getStyle('E1')->applyFromArray($headerFont);
        $aSheet->setCellValue("E1", "Название");

        $aSheet->getStyle('F1')->applyFromArray($headerFont);
        $aSheet->setCellValue("F1", "Номер");

        $aSheet->getStyle('G1')->applyFromArray($headerFont);
        $aSheet->setCellValue("G1", "Цена");

        $aSheet->getStyle('H1')->applyFromArray($headerFont);
        $aSheet->setCellValue("H1", "Вал");

        $aSheet->getStyle('I1')->applyFromArray($headerFont);
        $aSheet->setCellValue("I1", "Кол");

        $aSheet->getStyle('K1')->applyFromArray($headerFont);
        $aSheet->setCellValue("K1", "Склад");


        $j = 2;
        foreach ($this->incomeOrder->getIncomes() as $income) {
            $aSheet->getStyle('A' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("A" . $j . "", ($j - 1));

            $aSheet->getStyle('B' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("B" . $j . "", $income->getId());

            $aSheet->getStyle('C' . $j . '')->applyFromArray($textFont);

            $aSheet->getStyle('D' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("D" . $j . "", $income->getZapCard()->getCreater()->getName());

            $aSheet->getStyle('E' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("E" . $j . "", $income->getZapCard()->getDetailName());

            $aSheet->getStyle('F' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $aSheet->getStyle('F' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("F" . $j . "", " " . $income->getZapCard()->getNumber()->getValue());

            $aSheet->getStyle('G' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->getStyle('G' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("G" . $j . "", str_replace(".", ",", $income->getPriceZak()));

            $aSheet->getStyle('H' . $j . '')->applyFromArray($textFont);

            $aSheet->getStyle('I' . $j . '')->applyFromArray($tablePriceFont);
            $aSheet->setCellValue("I" . $j . "", $income->getQuantity());

            $aSheet->getStyle('J' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->getStyle('J' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("J" . $j . "", str_replace(".", ",", ($income->getPriceZak() * $income->getQuantity())));

            $aSheet->getStyle('K' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("K" . $j . "", ($income->getOrderGoods() ? $income->getOrderGoods()[0]->getOrder()->getId() : 'Склад'));

            $j++;
        }

        $this->saveXls($path, $spreadsheet);

        return $path;
    }
}