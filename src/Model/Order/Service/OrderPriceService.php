<?php

namespace App\Model\Order\Service;

use App\ReadModel\Expense\ExpenseFetcher;
use App\ReadModel\Income\IncomeFetcher;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;

class OrderPriceService
{
    private ExpenseFetcher $expenseFetcher;
    private PartPriceService $partPriceService;
    private IncomeFetcher $incomeFetcher;

    public function __construct(ExpenseFetcher $expenseFetcher, PartPriceService $partPriceService, IncomeFetcher $incomeFetcher)
    {
        $this->expenseFetcher = $expenseFetcher;
        $this->partPriceService = $partPriceService;
        $this->incomeFetcher = $incomeFetcher;
    }

    /**
     * Получаем цены в массиве
     * [
     *      priceZak - закупочная цена,
     *      price - отпускная цена,
     *      isPriceWrong - является ли цена неточной
     * ]
     *
     * @param array $good
     * @return array
     */
    public function get(array $good): array
    {
        $arr = [];
        $arr['isPriceWrong'] = false;
        $arr['price'] = $this->price($good);
        $arr['priceZak'] = $this->fromExpense($good['goodID']);
        if (!$arr['priceZak']) {
            if ($good['providerPriceID']) {
                $arr = $this->zakaz($good, $arr);
            } else {
                $arr = $this->sklad($good, $arr);
            }
        }
        return $arr;
    }

    private function price(array $good): float
    {
        return round($good['price'] - $good['price'] * $good['discount'] / 100) * $good['quantity'];
    }

    private function fromExpense(int $goodID): ?float
    {
        try {
            return $this->expenseFetcher->getSumPriceZakByGoodID($goodID);
        } catch (Exception $e) {
            return null;
        }
    }

    private function zakaz(array $good, array $arr): array
    {
        if ($good['incomeID']) {
            $arr['priceZak'] = $good['income_price'] * $good['quantity'];
        } else {
            try {
                $priceWithDostRub = ($this->partPriceService->onePriceWithStringData($good['number'], $good['providerPriceID'], $good['providerPriceID']))['priceWithDostRub'];
            } catch (Exception $e) {
                $priceWithDostRub = 0;
            }
            if ($priceWithDostRub) {
                $arr['priceZak'] = $priceWithDostRub * $good['quantity'];
            } else {
                $arr['isPriceWrong'] = true;
            }
        }
        return $arr;
    }

    private function sklad(array $good, array $arr): array
    {
        if (!$good['zapCard']) {
            $arr['isPriceWrong'] = true;
        } else {
            try {
                $price = $this->incomeFetcher->findPriceInWarehouseByZapCard($good['zapCard']->getId());
            } catch (Exception $e) {
                $price = 0;
            }
            if ($price) {
                $arr['priceZak'] = $price;
            } else {
                $arr['isPriceWrong'] = true;
            }
        }
        return $arr;
    }
}