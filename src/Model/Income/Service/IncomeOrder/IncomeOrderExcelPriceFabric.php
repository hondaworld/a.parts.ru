<?php


namespace App\Model\Income\Service\IncomeOrder;


use App\Model\Income\Entity\Order\IncomeOrder;

class IncomeOrderExcelPriceFabric
{
    public static function get(IncomeOrder $incomeOrder, string $pathIncomeFiles): IncomeOrderExcelImpl
    {
        if ($incomeOrder->getProvider()->getId() == 1) return new Eur($incomeOrder, $pathIncomeFiles);
        else if (in_array($incomeOrder->getProvider()->getId(), [19, 20, 2])) return new Usa($incomeOrder, $pathIncomeFiles);
        else if ($incomeOrder->getProvider()->getId() == 4) return new Jap($incomeOrder, $pathIncomeFiles);
        else if ($incomeOrder->getProvider()->getId() == 47) return new JapMsk($incomeOrder, $pathIncomeFiles);
        else if (in_array($incomeOrder->getProvider()->getId(), [11, 43])) return new AutoRus($incomeOrder, $pathIncomeFiles);
        else if ($incomeOrder->getProvider()->getId() == 12) return new Mtk($incomeOrder, $pathIncomeFiles);
        else if (in_array($incomeOrder->getProvider()->getId(), [21, 72, 45])) return new Oae($incomeOrder, $pathIncomeFiles);
        else if ($incomeOrder->getProvider()->getId() == 64) return new Nika($incomeOrder, $pathIncomeFiles);
        else if (in_array($incomeOrder->getProvider()->getId(), [66, 77, 7])) return new Voshod($incomeOrder, $pathIncomeFiles);
        else if (in_array($incomeOrder->getProvider()->getId(), [15, 68])) return new AutoEuro($incomeOrder, $pathIncomeFiles);
        else if (in_array($incomeOrder->getProvider()->getId(), [55, 62])) return new Tiss($incomeOrder, $pathIncomeFiles);
        else return new Standart($incomeOrder, $pathIncomeFiles);
    }
}