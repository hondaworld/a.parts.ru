<?php


namespace App\Model\Income\Service\IncomeOrder;


use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Voshod extends ExcelPrice implements IncomeOrderExcelImpl
{
    public function create(): string
    {
        $filename = "order" . $this->incomeOrder->getProvider()->getId() . "_" . $this->incomeOrder->getDocumentNum() . ".xls";
        $path = $this->pathIncomeFiles . "/income/" . $filename;

        $spreadsheet = $this->loadXls($this->pathIncomeFiles . '/income/excel/voshod.xls');
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
            $aSheet->setCellValue("A" . $j . "", $income->getId());

            $aSheet->getStyle('B' . $j . '')->applyFromArray($textFont)->applyFromArray($border);
            $aSheet->setCellValue("B" . $j . "", $income->getZapCard()->getCreater()->getName());

            $aSheet->getStyle('C' . $j . '')->applyFromArray($textFont)->applyFromArray($border);
            $aSheet->setCellValue("C" . $j . "", $income->getZapCard()->getDetailName());

            $aSheet->getStyle('D' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $aSheet->getStyle('D' . $j . '')->applyFromArray($numberFont)->applyFromArray($border);
            $aSheet->setCellValue("D" . $j . "", " " . $income->getZapCard()->getNumber()->getValue());

            $aSheet->getStyle('E' . $j . '')->applyFromArray($textFont)->applyFromArray($border);

            $aSheet->getStyle('F' . $j . '')->applyFromArray($textFont)->applyFromArray($border);
            $aSheet->setCellValue("F" . $j . "", $income->getProviderPrice()->getDescription());

            $aSheet->getStyle('G' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->getStyle('G' . $j . '')->applyFromArray($tablePriceFont)->applyFromArray($border);
            $aSheet->setCellValue("G" . $j . "", str_replace(".", ",", $income->getPrice()));

            $aSheet->getStyle('H' . $j . '')->applyFromArray($textFont)->applyFromArray($border);
            $aSheet->setCellValue("H" . $j . "", $income->getQuantity());

            $j++;
        }

        $this->saveXls($path, $spreadsheet);

        return $path;
    }
}