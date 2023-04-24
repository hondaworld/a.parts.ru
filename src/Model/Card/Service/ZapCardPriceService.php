<?php

namespace App\Model\Card\Service;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Main\Main;
use App\Model\Card\Entity\Main\MainRepository;
use App\Model\User\Entity\Opt\Opt;
use App\ReadModel\Card\ZapCardStockNumberFetcher;
use Doctrine\DBAL\Exception;

class ZapCardPriceService
{
    private ZapCard $zapCard;
    private Main $settings;
    private int $rounding;
    private array $stocks;

    public function __construct(MainRepository $mainRepository, ZapCardStockNumberFetcher $zapCardStockNumberFetcher)
    {
        $this->settings = $mainRepository->getSettings();
        $this->rounding = $this->settings->getRounding() ?: 0;
        try {
            $this->stocks = $zapCardStockNumberFetcher->findAllWithNumberAndCreater();
        } catch (Exception $e) {
            $this->stocks = [];
        }
    }

    public function priceOpt(ZapCard $zapCard, Opt $opt, ?float $priceZak = null, float $skladKoef = 1): float
    {
        $this->zapCard = $zapCard;
//        $this->zapCardStockNumber = $this->zapCardStockNumberRepository->findFromNumberAndCreater($this->zapCard->getNumber(), $this->zapCard->getCreater());

        $profit = $this->profitFromPriceGroup($opt);
        if ($profit == null) $profit = $this->profitFromZapCard($opt);
        return $this->getPrice($profit, $priceZak, $skladKoef);
    }

    public function priceAllOpt(ZapCard $zapCard, array $opts): array
    {
        $this->zapCard = $zapCard;
//        $this->zapCardStockNumber = $this->zapCardStockNumberRepository->findFromNumberAndCreater($this->zapCard->getNumber(), $this->zapCard->getCreater());

        $arr = [];
        foreach ($opts as $opt) {
            $profit = $this->profitFromPriceGroup($opt);
            if ($profit == null) $profit = $this->profitFromZapCard($opt);
            $price = $this->getPrice($profit);
            $arr[$opt->getId()] = $price;
        }
        return $arr;
    }

    public function profitsFromPriceGroupAllOpt(ZapCard $zapCard, array $opts): array
    {
        $this->zapCard = $zapCard;

        $arr = [];
        foreach ($opts as $opt) {
            $arr[$opt->getId()] = $this->profitFromPriceGroup($opt);
        }
        return $arr;
    }

    private function profitFromPriceGroup(Opt $opt): ?float
    {
        if ($this->zapCard->getPriceGroup() == null) return null;

        $profits = $opt->getPriceListProfits();
        foreach ($profits as $profit) {
            if ($profit->getPriceList()->getId() == $this->zapCard->getPriceGroup()->getPriceList()->getId()) {
                if ($profit->getProfit() == 0) return null;
                return $profit->getProfit();
            }
        }
        return null;
    }

    public function profitsFromZapCardAllOpt(ZapCard $zapCard, array $opts): array
    {
        $this->zapCard = $zapCard;

        $arr = [];
        foreach ($opts as $opt) {
            $arr[$opt->getId()] = $this->profitFromZapCard($opt);
        }
        return $arr;
    }

    private function profitFromZapCard(Opt $opt): ?float
    {
        $profits = $this->zapCard->getProfits();
        foreach ($profits as $profit) {
            if ($profit->getOpt()->getId() == $opt->getId()) {
                if ($profit->getProfit() == 0) return null;
                return $profit->getProfit();
            }
        }
        return null;
    }

    private function getPrice(?float $profit, ?float $priceZak = null, float $skladKoef = 1): float
    {
        $price = $priceZak ?: floatval($this->zapCard->getPrice());
        $priceResult = $price;

        if ($profit == null) {
            if ($this->zapCard->getPrice1() != 0) {
                $priceResult = floatval($this->zapCard->getPrice1());
            } else {
                $profit = $this->zapCard->getProfit() != 0 ? $this->zapCard->getProfit() : null;
            }
        }

        if ($profit != null) $priceResult = $price + $price * ($profit / 100);

        $priceResult = $priceResult * $skladKoef;

        if ($this->rounding > 0) $priceResult = ceil($priceResult / $this->rounding) * $this->rounding; else $priceResult = ceil($priceResult);

        if (isset($this->stocks[$this->zapCard->getCreater()->getId()][$this->zapCard->getNumber()->getValue()])) {
            $priceStock = $this->stocks[$this->zapCard->getCreater()->getId()][$this->zapCard->getNumber()->getValue()]['price_stock'];
            if ($priceStock > 0 && (($this->zapCard->getPrice1() == 0) || ($this->zapCard->getPrice1() != 0) && ($priceStock <= $this->zapCard->getPrice1())))
                $priceResult = $priceStock;
        }

        return $priceResult;
    }
}