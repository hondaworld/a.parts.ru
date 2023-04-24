<?php


namespace App\Model\Income\Service\IncomeOrder;


use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Mtk extends ExcelPrice implements IncomeOrderExcelImpl
{
    public function create(): string
    {
        $filename = "zakaz_mtk #" . $this->incomeOrder->getDocumentNum() . " - " . $this->incomeOrder->getDateofadded()->format('d.m.Y') . ".xls";
        $path = $this->pathIncomeFiles . "/income/" . $filename;

        $spreadsheet = $this->loadXls($this->pathIncomeFiles . '/income/excel/mtk.xls');
        $aSheet = $spreadsheet->getActiveSheet();

        // Стили
        $numFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_TOP
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

        $aSheet->getStyle('A17')->applyFromArray($numFont);
        $aSheet->setCellValue("A17", $this->incomeOrder->getDocumentNum());

        $aSheet->getStyle('A19')->applyFromArray($numFont);
        $aSheet->setCellValue("A19", $this->incomeOrder->getDateofadded()->format('d.m.Y'));

        $j = 22;
        foreach ($this->incomeOrder->getIncomes() as $income) {
            $aSheet->getStyle('A' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("A" . $j . "", ($j - 21));

            $aSheet->getStyle('B' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $aSheet->getStyle('B' . $j . '')->applyFromArray($numberFont);
            $aSheet->setCellValue("B" . $j . "", " " . $income->getZapCard()->getNumber()->getValue());

            $aSheet->getStyle('C' . $j . '')->applyFromArray($textFont);
            //$aSheet->setCellValue("C".$j."", $income->getZapCard()->getDetailName());
            $aSheet->setCellValue("C" . $j . "", '');

            $aSheet->getStyle('D' . $j . '')->applyFromArray($tablePriceFont);
            $aSheet->setCellValue("D" . $j . "", $income->getQuantity());

            $j++;
        }

        $this->saveXls($path, $spreadsheet);

        return $path;
    }
}