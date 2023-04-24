<?php


namespace App\Service\Price;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Card\Entity\Main\Main;
use App\Model\Card\Entity\Main\MainRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Income\Entity\Income\Income;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\Opt\Opt;
use App\ReadModel\Card\ZapCardStockNumberFetcher;
use App\ReadModel\Card\ZapCardStockView;
use App\ReadModel\Detail\CreaterFetcher;
use App\ReadModel\Detail\DetailProviderExcludeFetcher;
use App\ReadModel\Detail\PartPriceFetcher;
use App\ReadModel\Detail\ShopPriceDealerFetcher;
use App\ReadModel\Detail\ShopZamenaFetcher;
use App\ReadModel\Detail\WeightFetcher;
use App\ReadModel\Finance\CurrencyFetcher;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Provider\ProviderPriceFetcher;
use App\ReadModel\Provider\ProviderPriceOptFetcher;
use App\ReadModel\Sklad\ZapSkladFetcher;
use App\Service\Api\ApiJap;
use DateTime;
use Doctrine\DBAL\Exception;

class PartPriceService
{
    private PartPriceFetcher $fetcher;
    private WeightFetcher $weightFetcher;
    private CreaterFetcher $createrFetcher;
    private ShopZamenaFetcher $shopZamenaFetcher;
    private DetailProviderExcludeFetcher $detailProviderExcludeFetcher;
    private IncomeFetcher $incomeFetcher;
    private ZapCardPriceService $zapCardPriceService;
    private ProviderPriceFetcher $providerPriceFetcher;

    private array $arParts = [];
    private array $arPartsSort = [];
    private array $arCreaterData = [];
    private array $creaters;
    private array $createrAlternatives;
    private array $sklads;
    private array $arWeight = [];
    private array $arDealerPrices = [];
    private array $arStock = [];
    private array $providerPrices;
    private array $currencies;
    private ZapCardStockNumberFetcher $zapCardStockNumberFetcher;
    private Main $settings;
    private int $rounding;
    private ProviderPriceOptFetcher $providerPriceOptFetcher;
    private ShopPriceDealerFetcher $shopPriceDealerFetcher;
    private array $linkOptProfits;
    private array $providerPriceStocks;
    private ApiJap $apiJap;

    /**
     * PartPriceService constructor.
     * @param PartPriceFetcher $fetcher
     * @param WeightFetcher $weightFetcher
     * @param CreaterFetcher $createrFetcher
     * @param ShopZamenaFetcher $shopZamenaFetcher
     * @param DetailProviderExcludeFetcher $detailProviderExcludeFetcher
     * @param IncomeFetcher $incomeFetcher
     * @param ZapCardPriceService $zapCardPriceService
     * @param ZapSkladFetcher $zapSkladFetcher
     * @param ProviderPriceFetcher $providerPriceFetcher
     * @param CurrencyFetcher $currencyFetcher
     * @param ZapCardStockNumberFetcher $zapCardStockNumberFetcher
     * @param MainRepository $mainRepository
     * @param ProviderPriceOptFetcher $providerPriceOptFetcher
     * @param ShopPriceDealerFetcher $shopPriceDealerFetcher
     * @param ApiJap $apiJap
     */
    public function __construct(
        PartPriceFetcher             $fetcher,
        WeightFetcher                $weightFetcher,
        CreaterFetcher               $createrFetcher,
        ShopZamenaFetcher            $shopZamenaFetcher,
        DetailProviderExcludeFetcher $detailProviderExcludeFetcher,
        IncomeFetcher                $incomeFetcher,
        ZapCardPriceService          $zapCardPriceService,
        ZapSkladFetcher              $zapSkladFetcher,
        ProviderPriceFetcher         $providerPriceFetcher,
        CurrencyFetcher              $currencyFetcher,
        ZapCardStockNumberFetcher    $zapCardStockNumberFetcher,
        MainRepository               $mainRepository,
        ProviderPriceOptFetcher      $providerPriceOptFetcher,
        ShopPriceDealerFetcher       $shopPriceDealerFetcher,
        ApiJap                       $apiJap
    )
    {
        $this->fetcher = $fetcher;
        $this->weightFetcher = $weightFetcher;
        $this->createrFetcher = $createrFetcher;
        $this->shopZamenaFetcher = $shopZamenaFetcher;
        $this->detailProviderExcludeFetcher = $detailProviderExcludeFetcher;
        $this->incomeFetcher = $incomeFetcher;
        $this->zapCardPriceService = $zapCardPriceService;
        $this->providerPriceFetcher = $providerPriceFetcher;
        $this->zapCardStockNumberFetcher = $zapCardStockNumberFetcher;

        $this->creaters = $createrFetcher->allArray();
        $this->createrAlternatives = $createrFetcher->assocAlternatives();
        $this->sklads = $zapSkladFetcher->allSklads();
        $this->providerPrices = $providerPriceFetcher->allArray();

        foreach ($currencyFetcher->all() as $item) {
            $this->currencies[$item['currencyID']] = $item;
        }
        $this->settings = $mainRepository->getSettings();
        $this->rounding = $this->settings->getRounding() ?: 0;
        $this->providerPriceOptFetcher = $providerPriceOptFetcher;

        $this->shopPriceDealerFetcher = $shopPriceDealerFetcher;
        $this->apiJap = $apiJap;
    }

    /**
     * @return array
     */
    public function getArParts(): array
    {
        return $this->arParts;
    }

    /**
     * @return array
     */
    public function getArPartsSort(): array
    {
        return $this->arPartsSort;
    }

    /**
     * @return array
     */
    public function getArCreaterData(): array
    {
        return $this->arCreaterData;
    }

    /**
     * Возвращает закупочные цены в виде массива
     * [
     *      priceZak - закупочная цена по прайс-листу
     *      priceWithDostUsd - закупочная цена с доставкой в валюте поставщика
     *      priceWithDostRub - закупочная цена с доставкой в рублях
     *      priceDostUsd - цена доставки в валюте поставщика
     * ]
     *
     * @param string $number
     * @param int $createrID
     * @param int $providerPriceID
     * @return array
     * @throws Exception
     */
    public function onePriceWithStringData(string $number, int $createrID, int $providerPriceID): array
    {
        return $this->onePrice(new DetailNumber($number), $this->createrFetcher->get($createrID), $this->providerPriceFetcher->get($providerPriceID));
    }

    /**
     * Возвращает клиентскую цену
     *
     *
     * @param DetailNumber $number
     * @param Creater $creater
     * @param ProviderPrice $providerPrice
     * @param Opt $opt
     * @return float
     * @throws Exception
     */
    public function onePriceClient(DetailNumber $number, Creater $creater, ProviderPrice $providerPrice, Opt $opt): float
    {
        $prices = $this->onePrice($number, $creater, $providerPrice);

        $stock = $this->getPriceStock($number, $creater, $providerPrice);

        return $this->getPricePart(
            $providerPrice->getId(),
            $opt->getId(),
            $prices['priceWithDostRub'],
            $stock->getPrice()
        );
    }

    /**
     * Возвращает закупочные цены в виде массива
     * [
     *      priceZak - закупочная цена по прайс-листу
     *      priceWithDostUsd - закупочная цена с доставкой в валюте поставщика
     *      priceWithDostRub - закупочная цена с доставкой в рублях
     *      priceDostUsd - цена доставки в валюте поставщика
     * ]
     *
     * @param DetailNumber $number
     * @param Creater $creater
     * @param ProviderPrice $providerPrice
     * @return array
     * @throws Exception
     */
    public function onePrice(DetailNumber $number, Creater $creater, ProviderPrice $providerPrice): array
    {
        $priceZak = $this->fetcher->priceZak($number, $creater, $providerPrice);
        return $this->onePriceWithPriceZak($number, $creater, $providerPrice, $priceZak);
    }

    /**
     * Возвращает закупочные цены в виде массива с указанием закупочной цены по прайс-листу
     * [
     *      priceZak - закупочная цена по прайс-листу
     *      priceWithDostUsd - закупочная цена с доставкой в валюте поставщика
     *      priceWithDostRub - закупочная цена с доставкой в рублях
     *      priceDostUsd - цена доставки в валюте поставщика
     * ]
     *
     * @param DetailNumber $number
     * @param Creater $creater
     * @param ProviderPrice $providerPrice
     * @param float $priceZak
     * @return array
     */
    public function onePriceWithPriceZak(DetailNumber $number, Creater $creater, ProviderPrice $providerPrice, float $priceZak): array
    {
        $result = [];
        $this->addWeight($number->getValue(), $creater->getId());
        $result['priceZak'] = $priceZak;
        $result['priceWithDostUsd'] = $this->getPriceZakWithDeliveryUsd($number->getValue(), $creater->getId(), $providerPrice->getId(), $priceZak);
        $result['priceWithDostRub'] = $this->getPriceZakWithDeliveryRub($number->getValue(), $creater->getId(), $providerPrice->getId(), $priceZak);
        $result['priceDostUsd'] = round($result['priceWithDostUsd'] - $result['priceZak'], 2);
        return $result;
    }

    /**
     * @param Opt $opt
     * @param array $providerPrices
     * @return array
     * @throws Exception
     */
    public function byProviderPrices(Opt $opt, array $providerPrices): array
    {
        $this->arParts = $this->fetcher->findByProviderPrices($this->arParts, $providerPrices);
        $this->addStocks();
        $this->getPrice($opt);
        $this->sortingSimple('number');
        return $this->arPartsSort;
    }

    /**
     * @param Opt $opt
     * @param ZapSklad|null $zapSklad
     * @param bool $isSimple
     * @return array
     * @throws Exception
     */
    public function allInWarehouse(Opt $opt, ?ZapSklad $zapSklad, bool $isSimple): array
    {
        $this->arParts = $this->fetcher->allInWarehouse($this->arParts, $zapSklad);
        $this->addStocks();
        $this->getPrice($opt);
        $this->sortingSimple('number');
        return $this->arPartsSort;
    }

    /**
     * @param Opt $opt
     * @param ZapSklad|null $zapSklad
     * @return array
     * @throws Exception
     */
    public function hondaInWarehouse(Opt $opt, ?ZapSklad $zapSklad): array
    {
        $this->arParts = $this->fetcher->allInWarehouse($this->arParts, $zapSklad, [1,2]);
        $this->addStocks();
        $this->getPrice($opt);
        $this->sortingSimple('number');
        return $this->arPartsSort;
    }

    /**
     * @param DetailNumber $number
     * @param Opt $opt
     * @param array $sort
     * @throws Exception
     */
    public function fullPrice(DetailNumber $number, Opt $opt, array $sort): void
    {
        $this->addPart($number);
        $this->addZamena($number);
        $this->addZamenaAbc($number);
        $this->addWeights();
        $this->addDealerPrices();
        $this->addStocks();
        $this->addIncomeData();
        $this->getPrice($opt);
        $this->addDateOfChanged();
        $this->sorting($sort['sort'], $sort['direction']);
    }

    /**
     * @param DetailNumber $number
     * @param Opt $opt
     * @return array
     * @throws Exception
     */
    public function fullPriceForKit(DetailNumber $number, Opt $opt): array
    {
        $this->arParts = [];
        $this->addPartOriginalNotCheck($number);
        $this->addZamena($number, true);
        $this->addStocks();
        $this->getPrice($opt);
        $this->sortingSimple();
        return $this->arPartsSort;
    }

    /**
     * @param DetailNumber $number
     * @param Opt $opt
     * @return array
     * @throws Exception
     */
    public function fullPriceForOrder(DetailNumber $number, Opt $opt): array
    {
        $this->addPart($number);
        $this->addWeights();
        $this->addPercentIncomeData();
        $this->addStocks();
        $this->getPrice($opt);
        $this->sortingSimple();
        return $this->arPartsSort;
    }

    /**
     *
     * Получение акции по номеру, производителю и поставщику
     *
     * @param DetailNumber $number
     * @param Creater $creater
     * @param ProviderPrice|null $providerPrice
     * @return ZapCardStockView
     */
    public function getPriceStock(DetailNumber $number, Creater $creater, ?ProviderPrice $providerPrice = null): ZapCardStockView
    {
        $number = $number->getValue();
        $createrID = $creater->getId();

        $zapCardStockView = $this->zapCardStockNumberFetcher->findByNumberAndCreater($number, $createrID);
        if (
            $zapCardStockView->isStock() &&
            (
                !$providerPrice ||
                $this->isProviderPriceStock($providerPrice->getId(), $zapCardStockView->stockID)
            )
        ) {
            return $zapCardStockView;
        }

        return new ZapCardStockView();
    }

    /**
     * @param DetailNumber $number
     * @param Creater $creater
     * @param array $excludeProviderPrices
     * @return array|null
     * @throws Exception
     */
    public function onePriceByNumberAndCreaterIDBetterPriceWithClear(DetailNumber $number, Creater $creater, array $excludeProviderPrices = []): ?array
    {
        $this->arParts = [];
        $this->addPartByNumberAndCreater($number, $creater);
        $arr = $this->fillPrices($excludeProviderPrices);
        return $arr ? $arr[0] : null;
    }

    /**
     * @param DetailNumber $number
     * @param Creater $creater
     * @param array $excludeProviderPrices
     * @return array|null
     * @throws Exception
     */
    public function onePriceByNumberAndCreaterIDBetterPrice(DetailNumber $number, Creater $creater, array $excludeProviderPrices = []): ?array
    {
        $arr = $this->fullPriceByNumberAndCreater($number, $creater, $excludeProviderPrices);
        return $arr ? $arr[0] : null;
    }

    /**
     * Получение оптимального региона поставки
     *
     * @param ZapCard $zapCard
     * @return array|null
     * @throws Exception
     */
    public function getOptimalProviderPrice(ZapCard $zapCard): ?array
    {
        $lastProviderPrice = $this->incomeFetcher->getLastIncomeInByZapCardID($zapCard->getId());
        if ($lastProviderPrice) {
            return array_merge($lastProviderPrice, $this->onePrice($zapCard->getNumber(), $zapCard->getCreater(), $this->providerPriceFetcher->get($lastProviderPrice['providerPriceID'])));
        } else {
            $optimal = $this->onePriceByNumberAndCreaterIDBetterPriceWithClear($zapCard->getNumber(), $zapCard->getCreater());
            if ($optimal) return $optimal;
        }
        return null;
    }

    /**
     * @param DetailNumber $number
     * @param Creater $creater
     * @param array $excludeProviderPrices
     * @return array
     * @throws Exception
     */
    public function fullPriceByNumberAndCreater(DetailNumber $number, Creater $creater, array $excludeProviderPrices = []): array
    {
        $this->addPartByNumberAndCreater($number, $creater);
        $this->addPercentIncomeData();
        return $this->fillPrices($excludeProviderPrices);
    }

    /**
     * @param Income $income
     * @param array $excludeProviderPrices
     * @return array|null
     * @throws Exception
     */
    public function onePriceByIncomeBetterPrice(Income $income, array $excludeProviderPrices = []): ?array
    {
        $this->arParts = [];
        $arr = $this->fullPriceByIncome($income, $excludeProviderPrices);
        return $arr ? $arr[0] : null;
    }

    /**
     * @param Income $income
     * @param array $excludeProviderPrices
     * @return array
     * @throws Exception
     */
    public function fullPriceByIncome(Income $income, array $excludeProviderPrices = []): array
    {
        $this->addPartByIncome($income);
        $this->addPercentIncomeData();
        return $this->fillPrices($excludeProviderPrices);
    }

    /**
     * @param array $excludeProviderPrices
     * @return array
     * @throws Exception
     */
    private function fillPrices(array $excludeProviderPrices = []): array
    {
        $this->addWeights();
        $arr = [];
        foreach ($this->arParts as $createrID => &$arPartsNumbers) {
            foreach ($arPartsNumbers as $number => &$arPartsGroups) {
                foreach ($arPartsGroups as &$arPartsItems) {
                    if (!in_array($arPartsItems['providerPriceID'], $excludeProviderPrices)) {
                        $arPartsItems['priceZak'] = $arPartsItems['price'];
                        $arPartsItems['priceWithDostUsd'] = $this->getPriceZakWithDeliveryUsd($number, $createrID, $arPartsItems['providerPriceID'], $arPartsItems['priceZak']);
                        $arPartsItems['priceWithDostRub'] = $this->getPriceZakWithDeliveryRub($number, $createrID, $arPartsItems['providerPriceID'], $arPartsItems['priceZak']);
                        $arPartsItems['priceDostUsd'] = round($arPartsItems['priceWithDostUsd'] - $arPartsItems['priceZak'], 2);
                        $arr[] = $arPartsItems;
                    }
                }
            }
        }
        usort($arr, function ($a, $b) {
            return $a['priceWithDostRub'] <=> $b['priceWithDostRub'];
        });
        return $arr;
    }

    /**
     * @param OrderGood $orderGood
     * @return array
     * @throws Exception
     */
    public function fullPriceByOrderGood(OrderGood $orderGood): array
    {
        $this->addPartByOrderGood($orderGood);
        $this->addWeights();
        $this->addPercentIncomeData();
        $this->addStocks();
        $this->getPrice($orderGood->getOrder()->getUser()->getOpt());
        $this->sortingSimple();
        return $this->arPartsSort;
    }

    /**
     * @param DetailNumber $number
     * @throws Exception
     */
    public function addPart(DetailNumber $number): void
    {
//        $this->apiJap->get($number);
        $isProvider = $this->detailProviderExcludeFetcher->findByNumber($number->getValue());

        $this->arParts = $this->fetcher->sklad($this->arParts, 0, true, $number);
//        $this->arParts = $this->fetcher->used($this->arParts, 0, true, $number);
        $this->arParts = $this->fetcher->neorig($this->arParts, $isProvider, $number);

        $tableNames = $this->createrFetcher->getTableNamesAndOriginal();
        foreach ($tableNames as $tableName) {
            $this->arParts = $this->fetcher->zakaz($this->arParts, $isProvider, 0, $tableName['isOriginal'], $tableName['tableName'], $number);
        }
    }

    /**
     * @param DetailNumber $number
     * @throws Exception
     */
    public function addPartOriginalNotCheck(DetailNumber $number): void
    {
        $this->arParts = $this->fetcher->sklad($this->arParts, 0, true, $number);

        $tableNames = $this->createrFetcher->getTableNamesAndOriginal();
        foreach ($tableNames as $tableName) {
            if ($tableName['isOriginal'] == 1)
                $this->arParts = $this->fetcher->zakaz($this->arParts, [], 0, $tableName['isOriginal'], $tableName['tableName'], $number);
        }
    }

    /**
     * @param DetailNumber $number
     * @param Creater $creater
     * @throws Exception
     */
    private function addPartByNumberAndCreater(DetailNumber $number, Creater $creater): void
    {
        $isProvider = $this->detailProviderExcludeFetcher->findByNumberAndCreater($number->getValue(), $creater->getId());
        $this->arParts = $this->fetcher->zakaz($this->arParts, $isProvider, 0, $creater->isOriginal(), $creater->isOriginal() ? $creater->getTableName() : 'shopPriceN', $number, $creater->getId());
    }

    /**
     * @param Income $income
     * @throws Exception
     */
    private function addPartByIncome(Income $income): void
    {
        $number = $income->getZapCard()->getNumber();
        $creater = $income->getZapCard()->getCreater();

        $isProvider = $this->detailProviderExcludeFetcher->findByNumberAndCreater($number->getValue(), $creater->getId());
        $this->arParts = $this->fetcher->zakaz($this->arParts, $isProvider, 0, $creater->isOriginal(), $creater->isOriginal() ? $creater->getTableName() : 'shopPriceN', $number, $creater->getId());
    }

    /**
     * @param OrderGood $orderGood
     * @throws Exception
     */
    private function addPartByOrderGood(OrderGood $orderGood): void
    {
        $number = $orderGood->getNumber();
        $creater = $orderGood->getCreater();

        $isProvider = $this->detailProviderExcludeFetcher->findByNumberAndCreater($number->getValue(), $creater->getId());
        $this->arParts = $this->fetcher->sklad($this->arParts, 0, $creater->isOriginal(), $number, $creater->getId());
        $this->arParts = $this->fetcher->zakaz($this->arParts, $isProvider, 0, $creater->isOriginal(), $creater->isOriginal() ? $creater->getTableName() : 'shopPriceN', $number, $creater->getId());
    }

    /**
     * @param DetailNumber $number
     * @param bool $isOnlySklad
     * @throws Exception
     */
    public function addZamena(DetailNumber $number, bool $isOnlySklad = false): void
    {
        $arZamena = $this->shopZamenaFetcher->allByNumber($number->getValue());
        foreach ($arZamena as $zamena) {
            $number2 = new DetailNumber($zamena['number2']);
            $isProvider = $this->detailProviderExcludeFetcher->findByNumber($number2->getValue());

            $this->arParts = $this->fetcher->sklad($this->arParts, 1, $zamena['isOriginal'], $number2, $zamena['createrID2']);
            if (!$isOnlySklad) $this->arParts = $this->fetcher->zakaz($this->arParts, $isProvider, 1, $zamena['isOriginal'], $zamena['tableName'], $number2, $zamena['createrID2']);

            if (isset($this->createrAlternatives[$zamena['createrID2']])) {
                $this->arParts = $this->fetcher->sklad($this->arParts, 1, $zamena['isOriginal'], $number2, $this->createrAlternatives[$zamena['createrID2']]);
                if (!$isOnlySklad) $this->arParts = $this->fetcher->zakaz($this->arParts, $isProvider, 1, $zamena['isOriginal'], $zamena['tableName'], $number2, $this->createrAlternatives[$zamena['createrID2']]);
            }
        }
    }

    /**
     * @param DetailNumber $number
     * @throws Exception
     */
    public function addZamenaAbc(DetailNumber $number): void
    {
        $key = md5('bpcu75bBiZH9ghlrRWBf');
        $html = file_get_contents("http://new.parts.ru/api/getZamena/?number={$number->getValue()}&key=$key");
        if ($html) {
            $arZamenaAbcp = json_decode($html);
            if ($arZamenaAbcp) {
                foreach ($arZamenaAbcp as $createrID2 => $arZamenaAbcpNumbers) {
                    if (isset($this->creaters[$createrID2])) {
                        foreach ($arZamenaAbcpNumbers as $number2) {
                            $number2 = new DetailNumber($number2);
                            $isProvider = $this->detailProviderExcludeFetcher->findByNumber($number2->getValue());

                            $this->arParts = $this->fetcher->sklad($this->arParts, 21, $this->creaters[$createrID2]['isOriginal'], $number2, $createrID2);
                            $this->arParts = $this->fetcher->zakaz($this->arParts, $isProvider, 21, $this->creaters[$createrID2]['isOriginal'], $this->creaters[$createrID2]['tableName'], $number2, $createrID2);
                        }
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function addIncomeData(): void
    {
        foreach ($this->arParts as $createrID => &$arPartsNumbers) {
            foreach ($arPartsNumbers as $number => &$arPartsGroups) {
                foreach ($arPartsGroups as $k => &$arPartsItems) {
                    if (isset($arPartsItems["zapCardID"])) {
                        $this->arParts[$createrID][$number][$k]["quantity"] = $this->incomeFetcher->getQuantityByZapCardAndZapSklad($arPartsItems["zapCardID"], $arPartsItems["zapSkladID"]);
                        $dateofinplan = null;
                        $inPath = 0;
                        if ($arPartsItems["zapSkladID"] == 1) {
                            $dateofinplan = $this->incomeFetcher->getDateOfInPlanByZapCard($arPartsItems["zapCardID"]);
                            $inPath = $this->incomeFetcher->getQuantityInPathByZapCardAndZapSklad($arPartsItems["zapCardID"], $arPartsItems['zapSkladID']);
                        }
                        $this->arParts[$createrID][$number][$k]["dateofinplan"] = $dateofinplan ?? null;
                        $this->arParts[$createrID][$number][$k]["inPath"] = $inPath ?? 0;
                    } else {
                        $arPartsItems['averageIncome'] = $this->incomeFetcher->getAverageDaysIncome($number, $createrID, $arPartsItems["providerPriceID"]);
                        $arPartsItems['percentIncome'] = $this->incomeFetcher->getPercentIncome($number, $createrID, $arPartsItems["providerPriceID"]);
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    private function addPercentIncomeData(): void
    {
        foreach ($this->arParts as $createrID => &$arPartsNumbers) {
            foreach ($arPartsNumbers as $number => &$arPartsGroups) {
                foreach ($arPartsGroups as &$arPartsItems) {
                    if (isset($arPartsItems["providerPriceID"])) {
                        $arPartsItems['percentIncome'] = $this->incomeFetcher->getPercentIncome($number, $createrID, $arPartsItems["providerPriceID"]);
                    }
                }
            }
        }
    }

    /**
     * @param Opt $opt
     * @throws Exception
     */
    public function getPrice(Opt $opt): void
    {
        $this->arCreaterData = [];
        $this->arPartsSort = [];
        foreach ($this->arParts as $createrID => &$arPartsNumbers) {
            foreach ($arPartsNumbers as $number => &$arPartsGroups) {
                foreach ($arPartsGroups as &$arPartsItems) {
                    if (!isset($arPartsItems["providerPriceID"])) {
                        $priceZak = $arPartsItems["price"];
                        $priceIncome = null;
                        if (($arPartsItems["quantity"] == 0) && ($arPartsItems["inPath"] > 0)) {
                            $priceZak = $this->incomeFetcher->getPriceZakByZapCardAndZapSklad($arPartsItems["zapCardID"], $arPartsItems["zapSkladID"]);
                            $priceIncome = $priceZak ?: null;
                        }
                        $arPartsItems["price1"] = $this->zapCardPriceService->priceOpt($arPartsItems['zapCard'], $opt, $priceIncome, $this->sklads[$arPartsItems["zapSkladID"] ?? ZapSklad::OSN_SKLAD_ID]['koef']);
                        $arPartsItems["price"] = $priceZak;
                    } else {
//                        $price1 = CZapCard::getPrice($number, $createrID, $arPartsItems["providerPriceID"], 1, $arPartsItems["price"]);
//                        $arPrice = CZapCard::getPriceZak($number, $createrID, $arPartsItems["providerPriceID"], $arPartsItems["price"]);
//                        $price = $arPrice["rub"]["priceZakDost"];
                        $arPartsItems["priceZak"] = $arPartsItems['price'];
                        $arPartsItems["price"] = $this->getPriceZakWithDeliveryRub($number, $createrID, $arPartsItems['providerPriceID'], $arPartsItems['price']);
                        $arPartsItems["price1"] = $this->getPricePart(
                            $arPartsItems['providerPriceID'],
                            $opt->getId(),
                            $arPartsItems["price"],
                            $arPartsItems["price_stock"] ?? 0
                        );
                    }
                    if ($arPartsItems["price"] != 0) {
                        $arPartsItems["profit"] = round($arPartsItems["price1"] / $arPartsItems["price"] * 100 - 100);
                    } else {
                        $arPartsItems["price"] = null;
                        $arPartsItems["profit"] = null;
                        $arPartsItems["price1"] = null;
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function addDateOfChanged(): void
    {
        foreach ($this->arParts as &$arPartsNumbers) {
            foreach ($arPartsNumbers as &$arPartsGroups) {
                foreach ($arPartsGroups as &$arPartsItems) {

                    $dateofchanged = $arPartsItems['dateofchanged'] ?? 0;
                    if (isset($arPartsItems['dateofchanged_number']) && $arPartsItems['dateofchanged_number'] != 0) $dateofchanged = $arPartsItems['dateofchanged_number'];
                    if ($dateofchanged != 0) {
                        $arPartsItems['dateofchanged'] = new DateTime($dateofchanged);
                        if ($arPartsItems['daysofchanged'] > 0) {
                            $diff = date_diff($arPartsItems['dateofchanged'], new DateTime());
                            if ($diff->format('%a') > $arPartsItems['daysofchanged']) $arPartsItems['class_dateofchanged'] = 'text-danger';
                        }
                    } else {
                        $arPartsItems['dateofchanged'] = null;
                    }


                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function addDealerPrices(): void
    {
        foreach ($this->arParts as $createrID => &$arPartsNumbers) {
            foreach ($arPartsNumbers as $number => &$arPartsGroups) {
                foreach ($arPartsGroups as &$arPartsItems) {
                    if (!isset($this->arDealerPrices[$createrID][$number])) {
                        $arDealerPrices = $this->shopPriceDealerFetcher->allByNumberAndCreater($number, $createrID);
                        if (!$arDealerPrices && isset($this->createrAlternatives[$createrID])) {
                            $arDealerPrices = $this->shopPriceDealerFetcher->allByNumberAndCreater($number, $this->createrAlternatives[$createrID]);
                        }
                        $this->arDealerPrices[$createrID][$number] = $arDealerPrices ? $arDealerPrices[0] : [];
                    }
                    if ($this->arDealerPrices[$createrID][$number]) {
                        $arPartsItems["priceDealer"] = $this->arDealerPrices[$createrID][$number]["price"];
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    private function addWeights(): void
    {
        foreach ($this->arParts as $createrID => &$arPartsNumbers) {
            foreach ($arPartsNumbers as $number => &$arPartsGroups) {
                foreach ($arPartsGroups as &$arPartsItems) {
                    if (!isset($this->arWeight[$createrID][$number])) {
                        $arWeight = $this->weightFetcher->allByNumberAndCreater($number, $createrID);
                        if (!$arWeight && isset($this->createrAlternatives[$createrID])) {
                            $arWeight = $this->weightFetcher->allByNumberAndCreater($number, $this->createrAlternatives[$createrID]);
                        }
                        $this->arWeight[$createrID][$number] = $arWeight ? $arWeight[0] : [];
                    }
                    if ($this->arWeight[$createrID][$number]) {
                        $arPartsItems["weight"] = $this->arWeight[$createrID][$number]["weight"];
                        $arPartsItems["weightIsReal"] = $this->arWeight[$createrID][$number]["weightIsReal"];
                    }
                }
            }
        }
    }

    /**
     * @param string $number
     * @param int $createrID
     */
    public function addWeight(string $number, int $createrID): void
    {
        $arWeight = $this->weightFetcher->allByNumberAndCreater($number, $createrID);
        if (empty($arWeight) && isset($this->createrAlternatives[$createrID])) {
            $arWeight = $this->weightFetcher->allByNumberAndCreater($number, $this->createrAlternatives[$createrID]);
        }
        $this->arWeight[$createrID][$number] = $arWeight ? $arWeight[0] : [];
    }

    /**
     * @throws Exception
     */
    public function addStocks(): void
    {
        $this->arStock = $this->zapCardStockNumberFetcher->findAllWithNumberAndCreater();
        foreach ($this->arParts as $createrID => &$arPartsNumbers) {
            foreach ($arPartsNumbers as $number => &$arPartsGroups) {
                foreach ($arPartsGroups as &$arPartsItems) {
//                    if (!isset($this->arStock[$createrID][$number])) {
//                        $arStock = $this->zapCardStockNumberFetcher->findByNumberAndCreater($number, $createrID);
//                        $this->arStock[$createrID][$number] = $arStock ? $arStock[0] : [];
//                    }
                    if (
                        isset($this->arStock[$createrID][$number]["stockID"]) &&
                        (
                            isset($arPartsItems['zapSkladID']) ||
                            isset($arPartsItems['providerPriceID']) &&
                            $this->isProviderPriceStock($arPartsItems['providerPriceID'], $this->arStock[$createrID][$number]["stockID"])
                        )
                    ) {
                        $arPartsItems["stock_name"] = $this->arStock[$createrID][$number]["name"];
                        $arPartsItems["stock_text"] = $this->arStock[$createrID][$number]["text"];
                        $arPartsItems["stockID"] = $this->arStock[$createrID][$number]["stockID"];
                        $arPartsItems["price_stock"] = $this->arStock[$createrID][$number]["price_stock"];
                    }
                }
            }
        }
    }

    /**
     * @param string $sort
     * @param string $direction
     */
    public function sorting(string $sort = 'price1', string $direction = 'ASC'): void
    {
        foreach ($this->arParts as $arPartsNumbers) {
            foreach ($arPartsNumbers as $arPartsGroups) {
                foreach ($arPartsGroups as $arPartsItems) {

                    if (($arPartsItems["isZamena"] == 1 || $arPartsItems["isZamena"] == 21) && $arPartsItems['srok'] != 'наличие') {
                        if ($arPartsItems["isZamena"] == 1) {
                            $this->arPartsSort[2][] = $arPartsItems;
                        } else {
                            $this->arPartsSort[22][] = $arPartsItems;
                        }

                        if (isset($this->arCreaterData[$arPartsItems["isZamena"]][$arPartsItems['createrID']])) {
                            $this->arCreaterData[$arPartsItems["isZamena"]][$arPartsItems['createrID']]['srok'] = min($this->arCreaterData[$arPartsItems["isZamena"]][$arPartsItems['createrID']]['srok'], $arPartsItems['srokInDays']);
                            $this->arCreaterData[$arPartsItems["isZamena"]][$arPartsItems['createrID']]['price'] = min($this->arCreaterData[$arPartsItems["isZamena"]][$arPartsItems['createrID']]['price'], $arPartsItems['price1']);
                        } else {
                            $this->arCreaterData[$arPartsItems["isZamena"]][$arPartsItems['createrID']]['srok'] = $arPartsItems['srokInDays'];
                            $this->arCreaterData[$arPartsItems["isZamena"]][$arPartsItems['createrID']]['price'] = $arPartsItems['price1'];
                        }
                        $this->arCreaterData[$arPartsItems["isZamena"]][$arPartsItems['createrID']]['createrName'] = $arPartsItems['createrName'];
                    } else {
                        $this->arPartsSort[$arPartsItems["isZamena"]][] = $arPartsItems;
                    }
                }
            }
        }

        ksort($this->arPartsSort);
        $sortKoef = ($direction == "asc" ? 1 : -1);

        foreach ($this->arPartsSort as $isZamena => &$item) {
            if ($isZamena != 2 && $isZamena != 22) {
                uasort($item, function ($a, $b) use ($sort, $sortKoef) {
                    if ($a['isSklad'] == $b['isSklad']) {
                        if ($a[$sort] > $b[$sort]) return $sortKoef;
                        elseif ($b[$sort] > $a[$sort]) return -1 * $sortKoef;
                        else return 0;
                    } else {
                        if ($b['isSklad'] > $a['isSklad']) return 1;
                        elseif ($a['isSklad'] > $b['isSklad']) return -1;
                        else return 0;
                    }
                });
            } else {
                uasort($item, function ($a, $b) use ($sort, $sortKoef) {
                    if ($a['createrName'] == $b['createrName']) {
                        if ($a[$sort] > $b[$sort]) return $sortKoef;
                        elseif ($b[$sort] > $a[$sort]) return -1 * $sortKoef;
                        else return 0;
                    } else
                        return strcmp($a['createrName'], $b['createrName']);
                });
            }
        }
    }

    /**
     * @param string $sort
     * @param string $direction
     */
    private function sortingSimple(string $sort = 'price1', string $direction = 'ASC'): void
    {
        foreach ($this->arParts as $arPartsNumbers) {
            foreach ($arPartsNumbers as $arPartsGroups) {
                foreach ($arPartsGroups as $arPartsItems) {
                    $this->arPartsSort[] = $arPartsItems;
                }
            }
        }

        $sortKoef = (strtoupper($direction) == "ASC" ? 1 : -1);

        usort($this->arPartsSort, function ($a, $b) use ($sort, $sortKoef) {
            if ($a['isSklad'] == $b['isSklad']) {
                if ($sortKoef > 0) {
                    return $a[$sort] <=> $b[$sort];
                } else {
                    return $b[$sort] <=> $a[$sort];
                }
            } else {
                if ($b['isSklad'] > $a['isSklad']) return 1;
                elseif ($a['isSklad'] > $b['isSklad']) return -1;
                else return 0;
            }
        });
    }

    /**
     * @param string $number
     * @param int $createrID
     * @param int $providerPriceID
     * @param float $price
     * @return float
     */
    private function getDelivery(string $number, int $createrID, int $providerPriceID, float $price): float
    {
        if (isset($this->arWeight[$createrID][$number]) && isset($this->arWeight[$createrID][$number]["weight"])) {
            return $this->arWeight[$createrID][$number]["weight"] * $this->providerPrices[$providerPriceID]["forWeight"];
        } else {
            return $price * $this->providerPrices[$providerPriceID]["delivery"] / 100;
        }
    }

    /**
     * @param int $providerPriceID
     * @return float
     */
    private function getCurrency(int $providerPriceID): float
    {
        if ($this->providerPrices[$providerPriceID]['currency'] > 0) return $this->providerPrices[$providerPriceID]['currency'];

        $currency = 1;
        if (!isset($this->currencies[$this->providerPrices[$providerPriceID]['currencyID']])) return $currency;

        $arCurrency = $this->currencies[$this->providerPrices[$providerPriceID]['currencyID']];

        if ($arCurrency["isNational"]) return 1;

        $fix_rate = $arCurrency["fix_rate"];
        $is_fix_rate = $arCurrency["is_fix_rate"];
//        $koef = $arCurrency["koef"];

        if (($fix_rate != 0) && ($is_fix_rate == 1)) {
            $currency = $fix_rate;
        } else {
            $currency = $arCurrency['last_rate'];
        }

        return $currency;
    }

    /**
     * @param string $number
     * @param int $createrID
     * @param int $providerPriceID
     * @param float $price
     * @return float
     */
    public function getPriceZakWithDeliveryRub(string $number, int $createrID, int $providerPriceID, float $price): float
    {
        return round(($price + $this->getDelivery($number, $createrID, $providerPriceID, $price)) * $this->providerPrices[$providerPriceID]['koef'] * $this->getCurrency($providerPriceID) * 100) / 100;
    }

    /**
     * @param string $number
     * @param int $createrID
     * @param int $providerPriceID
     * @param float $price
     * @return float
     */
    public function getPriceZakWithDeliveryUsd(string $number, int $createrID, int $providerPriceID, float $price): float
    {
        return $price + $this->getDelivery($number, $createrID, $providerPriceID, $price);
    }

    /**
     * @param int $providerPriceID
     * @param int $optID
     * @param float $price
     * @param float $price_stock
     * @return float
     */
    public function getPricePart(int $providerPriceID, int $optID, float $price, float $price_stock): float
    {
        $profit = $this->getLinkOptProfit($providerPriceID, $optID);
        if (!$profit) $profit = 0;

        $priceResult = $price * ($profit / 100 + 1);

        if ($this->rounding > 0) $priceResult = ceil($priceResult / $this->rounding) * $this->rounding; else $priceResult = ceil($priceResult);

        if (
            $price_stock > 0 &&
            ($price == 0 || $price_stock <= $price)
        )
            $priceResult = $price_stock;

        return $priceResult;
    }

    private function getLinkOptProfit(int $providerPriceID, int $optID): float
    {
        if (!isset($this->linkOptProfits[$providerPriceID][$optID])) {
            $this->linkOptProfits[$providerPriceID][$optID] = $this->providerPriceOptFetcher->findProfitByProviderPriceAndOpt($providerPriceID, $optID);
        }
        return $this->linkOptProfits[$providerPriceID][$optID];
    }

    private function isProviderPriceStock(int $providerPriceID, int $stockID): bool
    {
        if (!isset($this->providerPriceStocks[$providerPriceID][$stockID])) {
            $this->providerPriceStocks[$providerPriceID][$stockID] = $this->providerPriceFetcher->isProviderPriceStock($providerPriceID, $stockID);
        }
        return $this->providerPriceStocks[$providerPriceID][$stockID];
    }
}