<?php


namespace App\Model\Income\Service\IncomeOrder;


use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Eur extends ExcelPrice implements IncomeOrderExcelImpl
{
    public function create(): string
    {
        $document_num = $this->incomeOrder->getDocumentNum();
        if ($document_num < 10) $document_num = "00" . $document_num;
        if ($document_num < 100) $document_num = "0" . $document_num;
        $filename = "Order " . $document_num . " - multiparts_.xls";
        $path = $this->pathIncomeFiles . "/income/" . $filename;

        $spreadsheet = $this->loadXls($this->pathIncomeFiles . '/income/excel/eur.xls');
        $aSheet = $spreadsheet->getActiveSheet();

        // Стили
        $border = array(
            'borders' => array(
                'outline' => array(
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => array('rgb' => '000000'),
                ),
            ),
        );
        $numberFont = array(
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

        $j = 2;
        foreach ($this->incomeOrder->getIncomes() as $income) {

            $aSheet->getStyle('A' . $j . '')->applyFromArray($textFont)->applyFromArray($border);
            $aSheet->setCellValue("A" . $j . "", "MULTIPARTS");

            $aSheet->getStyle('B' . $j . '')->applyFromArray($textFont)->applyFromArray($border);
            $aSheet->setCellValue("B" . $j . "", $this->incomeOrder->getDocumentNum());

            $aSheet->getStyle('C' . $j . '')->applyFromArray($textFont)->applyFromArray($border);
            $aSheet->setCellValue("C" . $j . "", ($j - 1));

            $aSheet->getStyle('D' . $j . '')->applyFromArray($textFont)->applyFromArray($border);
            $aSheet->setCellValue("D" . $j . "", $income->getZapCard()->getCreater()->getName());

            $aSheet->getStyle('E' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $aSheet->getStyle('E' . $j . '')->applyFromArray($numberFont)->applyFromArray($border);
            $aSheet->setCellValue("E" . $j . "", " " . $income->getZapCard()->getNumber()->getValue());

            $aSheet->getStyle('F' . $j . '')->applyFromArray($textFont)->applyFromArray($border);
            $aSheet->setCellValue("F" . $j . "", $income->getZapCard()->getDetailName());

            $aSheet->getStyle('G' . $j . '')->applyFromArray($tablePriceFont)->applyFromArray($border);
            $aSheet->setCellValue("G" . $j . "", $income->getQuantity());

            $aSheet->getStyle('H' . $j . '')->applyFromArray($textFont)->applyFromArray($border);

            $aSheet->getStyle('I' . $j . '')->applyFromArray($textFont)->applyFromArray($border);
            $aSheet->setCellValue("I" . $j . "", $income->getId());

            $j++;
        }

        $this->saveXls($path, $spreadsheet);

        return $path;
    }
}