<?php


namespace App\Model\Income\Service\IncomeOrder;


class AutoEuro extends ExcelPrice implements IncomeOrderExcelImpl
{
    public function create(): string
    {
        $filename = "order" . $this->incomeOrder->getProvider()->getId() . "_" . $this->incomeOrder->getDocumentNum() . ".csv";
        $path = $this->pathIncomeFiles . "/income/" . $filename;

        $out = $this->loadCsv($path);

        $j = 1;
        foreach ($this->incomeOrder->getIncomes() as $income) {
            fputcsv($out, array(
                $this->helper->convertTextToCP1251($income->getZapCard()->getCreater()->getName()),
                $this->helper->convertTextToCP1251($income->getZapCard()->getDetailName()),
                $this->helper->convertTextToCP1251(" " . $income->getZapCard()->getNumber()->getValue()),
                $this->helper->convertTextToCP1251($income->getProviderPrice()->getDescription()),
                str_replace(".", ",", $income->getPrice()),
                $income->getQuantity()
            ), ";", '"');
            $j++;
        }

        $this->saveCsv($out);

        return $path;
    }
}