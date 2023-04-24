<?php


namespace App\Model\Income\Service\IncomeOrder;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class Oae extends ExcelPrice implements IncomeOrderExcelImpl
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

        $aSheet->getColumnDimension('A')->setWidth(11);
        $aSheet->getColumnDimension('B')->setWidth(35);
        $aSheet->getColumnDimension('C')->setWidth(15);
        $aSheet->getColumnDimension('D')->setWidth(25);
        $aSheet->getColumnDimension('E')->setWidth(15);
        $aSheet->getColumnDimension('F')->setWidth(10);
        $aSheet->getColumnDimension('G')->setWidth(6);
        $aSheet->getColumnDimension('H')->setWidth(10);

        $aSheet->getStyle('A1')->applyFromArray($headerFont);
        $aSheet->setCellValue("A1", "Производитель");

        $aSheet->getStyle('B1')->applyFromArray($headerFont);
        $aSheet->setCellValue("B1", "Наименование");

        $aSheet->getStyle('C1')->applyFromArray($headerFont);
        $aSheet->setCellValue("C1", "Номер детали");

        $aSheet->getStyle('D1')->applyFromArray($headerFont);
        $aSheet->setCellValue("D1", "Регион");

        $aSheet->getStyle('E1')->applyFromArray($headerFont);
        $aSheet->setCellValue("E1", "# заказа");

        $aSheet->getStyle('F1')->applyFromArray($headerFont);
        $aSheet->setCellValue("F1", "Цена");

        $aSheet->getStyle('G1')->applyFromArray($headerFont);
        $aSheet->setCellValue("G1", "Количество");

        $aSheet->getStyle('H1')->applyFromArray($headerFont);
        $aSheet->setCellValue("H1", "ID заказа");


        $j = 2;
        foreach ($this->incomeOrder->getIncomes() as $income) {
            $aSheet->getStyle('A' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("A" . $j . "", $income->getZapCard()->getCreater()->getName());

            $aSheet->getStyle('B' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("B" . $j . "", $income->getZapCard()->getDetailName());

            $aSheet->getStyle('C' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $aSheet->getStyle('C' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("C" . $j . "", " " . $income->getZapCard()->getNumber()->getValue());

            $aSheet->getStyle('D' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("D" . $j . "", $income->getProviderPrice()->getDescription());

            $aSheet->getStyle('E' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("E" . $j . "", ($income->getOrderGoods() ? $income->getOrderGoods()[0]->getOrder()->getId() : 'Склад'));

            $aSheet->getStyle('F' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->getStyle('F' . $j . '')->applyFromArray($tablePriceFont);
            $aSheet->setCellValue('F' . $j . "", str_replace(".", ",", $income->getPriceZak()));

            $aSheet->getStyle('G' . $j . '')->applyFromArray($tablePriceFont);
            $aSheet->setCellValue("G" . $j . "", $income->getQuantity());

            $aSheet->getStyle('H' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("H" . $j . "", $income->getId());

            $j++;
        }

        $this->saveXls($path, $spreadsheet);

        return $path;
    }
}