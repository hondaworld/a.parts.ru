<?php


namespace App\Model\Income\Service\IncomeOrder;


use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Jap extends ExcelPrice implements IncomeOrderExcelImpl
{
    public function create(): string
    {
        $document_num = $this->incomeOrder->getDocumentNum();
        if ($document_num < 10) $document_num = "00" . $document_num;
        if ($document_num < 100) $document_num = "0" . $document_num;
        $filename = "order #" . $document_num . " - " . $this->incomeOrder->getDateofadded()->format('d.m.Y') . ".xls";
        $path = $this->pathIncomeFiles . "/income/" . $filename;

        $spreadsheet = $this->loadXls($this->pathIncomeFiles . '/income/excel/jap.xls');
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
        $headerFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '11',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_BOTTOM
            ),
        );
        $numberFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '8',
                'bold' => false
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_TOP
            ),
        );
        $nameFont = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '8',
                'bold' => false
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
        $priceFont1 = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => true,
                'color' => array('rgb' => 'DD0806')
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_TOP
            ),
        );
        $priceFont2 = array(
            'font' => array(
                'name' => 'Arial Cyr',
                'size' => '10',
                'bold' => false,
                'color' => array('rgb' => '0000D4')
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
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
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_TOP
            ),
        );

        $aSheet->getStyle('A2')->applyFromArray($headerFont);
        $aSheet->setCellValue("A2", "Заказ №" . $document_num . " - " . $this->incomeOrder->getDateofadded()->format('d.m.Y') . "");

        $j = 4;
        foreach ($this->incomeOrder->getIncomes() as $income) {
            $aSheet->getStyle('A' . $j . '')->applyFromArray($textFont)->applyFromArray($border);
            $aSheet->setCellValue("A" . $j . "", ($j - 3));

            $aSheet->getStyle('B' . $j . '')->applyFromArray($textFont)->applyFromArray($border);

            $aSheet->getStyle('C' . $j . '')->applyFromArray($nameFont)->applyFromArray($border);
            $aSheet->setCellValue("C" . $j . "", $income->getZapCard()->getDetailName());

            $aSheet->getStyle('D' . $j . '')->applyFromArray($numberFont)->applyFromArray($border);
            $aSheet->setCellValue("D" . $j . "", $income->getZapCard()->getCreater()->getName());

            $aSheet->getStyle('E' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $aSheet->getStyle('E' . $j . '')->applyFromArray($numberFont)->applyFromArray($border);
            $aSheet->setCellValue("E" . $j . "", " " . $income->getZapCard()->getNumber()->getValue());

            $aSheet->getStyle('F' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->getStyle('F' . $j . '')->applyFromArray($priceFont1)->applyFromArray($border);
            $aSheet->setCellValue('F' . $j . "", str_replace(".", ",", $income->getPrice()));

            $aSheet->getStyle('G' . $j . '')->applyFromArray($tablePriceFont)->applyFromArray($border);
            $aSheet->setCellValue("G" . $j . "", $income->getQuantity());

            $aSheet->getStyle('H' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
            $aSheet->getStyle('H' . $j . '')->applyFromArray($priceFont2)->applyFromArray($border);
            $aSheet->setCellValue('H' . $j . "", str_replace(".", ",", ($income->getPrice() * $income->getQuantity())));

            $aSheet->getStyle('I' . $j . '')->applyFromArray($textFont)->applyFromArray($border);

            $aSheet->getStyle('J' . $j . '')->applyFromArray($textFont)->applyFromArray($border);

            $j++;
        }

        $this->saveXls($path, $spreadsheet);

        return $path;
    }
}