<?php


namespace App\Model\Income\Service\IncomeOrder;


use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class Nika extends ExcelPrice implements IncomeOrderExcelImpl
{
    public function create(): string
    {
        $filename = "Заказ запчастей ООО ПартсРу " . $this->incomeOrder->getDateofadded()->format('d.m') . ".xls";
        $path = $this->pathIncomeFiles . "/income/" . $filename;

        $spreadsheet = $this->loadXls($this->pathIncomeFiles . '/income/excel/nika.xls');
        $aSheet = $spreadsheet->getActiveSheet();

        // Стили
        $textFont = array(
            'font' => array(
                'name' => 'Tahoma',
                'size' => '10',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_TOP
            ),
        );
        $tablePriceFont = array(
            'font' => array(
                'name' => 'Tahoma',
                'size' => '10',
                'bold' => true
            ),
            'alignment' => array(
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_TOP
            ),
        );

        //$aSheet->setCellValue("C2", "№ НИКА ".$arIncomeOrder["document_num"]);
        $aSheet->setCellValue("D2", "Дата " . $this->incomeOrder->getDateofadded()->format('d.m.y'));

        $j = 11;
        foreach ($this->incomeOrder->getIncomes() as $income) {
            $aSheet->getStyle('A' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("A" . $j . "", ($j - 10));

            $aSheet->getStyle('B' . $j . '')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
            $aSheet->getStyle('B' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("B" . $j . "", $this->getTireToyota($income->getZapCard()->getNumber()->getValue()));

            $aSheet->getStyle('C' . $j . '')->applyFromArray($textFont);
            $aSheet->setCellValue("C" . $j . "", $income->getZapCard()->getDetailName());

            $aSheet->getStyle('D' . $j . '')->applyFromArray($tablePriceFont);
            $aSheet->setCellValue("D" . $j . "", $income->getQuantity());

            $j++;
        }

        $this->saveXls($path, $spreadsheet);

        return $path;
    }

    private function getTireToyota(string $str): string
    {
        if (strpos($str, ".") !== false) return $str;
        $arTire = array(5, 10);
        $i = 1;
        $str1 = "";
        $str = trim($str);
        while (strlen($str) > 0) {
            $str1 = $str1 . substr($str, 0, 1);
            $str = substr($str, 1);
            if (in_array($i, $arTire) && (strlen($str) > 0)) {
                $str1 = $str1 . "-";
            }
            $i++;
        }
        return $str1;
    }
}