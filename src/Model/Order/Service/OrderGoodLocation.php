<?php

namespace App\Model\Order\Service;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Location\ZapSkladLocationRepository;
use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\ReadModel\Income\IncomeSkladFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;

class OrderGoodLocation
{
    private IncomeSkladFetcher $incomeSkladFetcher;
    private ZapSkladFetcher $zapSkladFetcher;
    private ProviderPriceFetcher $providerPriceFetcher;
    private ZapSkladLocationRepository $zapSkladLocationRepository;

    public function __construct(IncomeSkladFetcher $incomeSkladFetcher, ZapSkladFetcher $zapSkladFetcher, ProviderPriceFetcher $providerPriceFetcher, ZapSkladLocationRepository $zapSkladLocationRepository)
    {
        $this->incomeSkladFetcher = $incomeSkladFetcher;
        $this->zapSkladFetcher = $zapSkladFetcher;
        $this->providerPriceFetcher = $providerPriceFetcher;
        $this->zapSkladLocationRepository = $zapSkladLocationRepository;
    }

    public function get(?int $incomeID, ?int $zapSkladID, ?int $providerPriceID, array $expenseSklads = []): string
    {
        $location = '';
        if ($providerPriceID) {
            $location .= $this->providerPriceFetcher->get($providerPriceID)->getDescription() . "\n";
        }

        if ($expenseSklads) $location .= $this->getExpenseSkladFrom($expenseSklads);

        if ($zapSkladID) {
            $location .= $this->zapSkladFetcher->get($zapSkladID)->getNameShort();
        } elseif ($providerPriceID) {
            $skladName = $this->getZakazSkladName($incomeID);
            if ($skladName) $location .= $skladName;
        }

        if ($expenseSklads) $location .= $this->getExpenseSkladTo($expenseSklads);

        return $location;
    }

    public function simple(?int $zapSkladID, ?int $providerPriceID): string
    {
        if ($providerPriceID) {
            return $this->providerPriceFetcher->get($providerPriceID)->getDescription();
        }

        if ($zapSkladID) {
            return $this->zapSkladFetcher->get($zapSkladID)->getNameShort();
        }

        return '';
    }

    /**
     * Получение наименование склада, где деталь находится в данный момент
     *
     * @param int|null $incomeID
     * @param int|null $zapSkladID
     * @param int|null $providerPriceID
     * @return string|null
     */
    public function getSkladName(?int $incomeID, ?int $zapSkladID, ?int $providerPriceID): ?string
    {
        if ($zapSkladID) {
            return $this->zapSkladFetcher->get($zapSkladID)->getNameShort();
        } elseif ($providerPriceID) {
            return $this->getZakazSkladName($incomeID);
        }
        return null;
    }

    /**
     * Если деталь заказная, возвращает название поставщика, иначе ячейку склада
     *
     * @param ZapCard|null $zapCard
     * @param int|null $zapSkladID
     * @param int|null $providerPriceID
     * @return string|null
     */
    public function getSkladLocation(?ZapCard $zapCard, ?int $zapSkladID, ?int $providerPriceID): ?string
    {
        if ($zapCard && $zapSkladID) {
            $zapSkladLocation = $this->zapSkladLocationRepository->findOneBy(['zapCard' => $zapCard, 'zapSklad' => $this->zapSkladFetcher->get($zapSkladID)]);
            if ($zapSkladLocation) {
                return $zapSkladLocation->getLocation() ? $zapSkladLocation->getLocation()->getName() : null;
            }
        } elseif ($providerPriceID) {
            return $this->providerPriceFetcher->get($providerPriceID)->getProvider()->getName();
        }
        return null;
    }

    private function getZakazSkladName(?int $incomeID): ?string
    {
        if (!$incomeID) return null;
        return $this->incomeSkladFetcher->getSkladNameByIncome($incomeID);
    }

    private function getExpenseSkladFrom(array $expenses): string
    {
        $expenseFrom = null;
        foreach ($expenses as $expense) {
            if ($expense['status'] == ExpenseSklad::SENT) {
                $expenseFrom = $expense['sklad_name_from'];
            }
        }
        return $expenseFrom ? $expenseFrom . ' -> ' : '';
    }

    private function getExpenseSkladTo(array $expenses): string
    {
        $expenseTo = '';
        foreach ($expenses as $expense) {
            if (in_array($expense['status'], [ExpenseSklad::ADDED, ExpenseSklad::PACKED])) {
                $expenseTo = $expense['sklad_name_to'];
            }
        }
        return $expenseTo ? ' -> ' . $expenseTo : '';
    }
}