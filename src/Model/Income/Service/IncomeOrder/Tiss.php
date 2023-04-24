<?php


namespace App\Model\Income\Service\IncomeOrder;


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class Tiss extends ExcelPrice implements IncomeOrderExcelImpl
{
    public function create(): string
    {
        $filename = "order" . $this->incomeOrder->getProvider()->getId() . "_" . $this->incomeOrder->getDocumentNum() . ".xls";
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
        $aSheet->getColumnDimension('C')->setWidth(6);
        $aSheet->getColumnDimension('D')->setWidth(25);


        $aSheet->getStyle('A1')->applyFromArray($headerFont);
        $aSheet->setCellValue("A1", "Производитель");

        $aSheet->getStyle('B1')->applyFromArray($headerFont);
        $aSheet->setCellValue("B1", "Номер");

        $aSheet->getStyle('C1')->applyFromArray($headerFont);
        $aSheet->setCellValue("C1", "Количество");

        $aSheet->getStyle('D1')->applyFromArray($headerFont);
        $aSheet->setCellValue("D1", "Максимальный срок поставки");

        $j = 2;
        foreach ($this->incomeOrder->getIncomes() as $income) {
            $aSheet->getStyle('A' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("A" . $j . "", $income->getZapCard()->getCreater()->getName());

            $aSheet->getStyle('B' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $aSheet->getStyle('B' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("B" . $j . "", " " . $income->getZapCard()->getNumber()->getValue());

            $aSheet->getStyle('C' . $j . '')->applyFromArray($tablePriceFont);
            $aSheet->setCellValue("C" . $j . "", $income->getQuantity());

            $aSheet->getStyle('D' . $j . '')->applyFromArray($tablePriceFont);
            $aSheet->setCellValue("D" . $j . "", '1');

            $j++;
        }

        $this->saveXls($path, $spreadsheet);

        return $path;
    }
}