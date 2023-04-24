<?php


namespace App\ReadModel\Detail;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class PartPriceFetcher
{
    private $connection;
    private ZapCardRepository $zapCardRepository;
    private DetailProviderExcludeFetcher $detailProviderExcludeFetcher;

    public function __construct(EntityManagerInterface $em, ZapCardRepository $zapCardRepository, DetailProviderExcludeFetcher $detailProviderExcludeFetcher)
    {
        $this->connection = $em->getConnection();
        $this->zapCardRepository = $zapCardRepository;
        $this->detailProviderExcludeFetcher = $detailProviderExcludeFetcher;
    }

    /**
     * @param array $arParts
     * @param int $isZamena
     * @param bool $isOriginal
     * @param DetailNumber $number
     * @param int|null $createrID
     * @return array
     * @throws Exception
     */
    public function sklad(array $arParts, int $isZamena, bool $isOriginal, DetailNumber $number, int $createrID = null): array
    {
        $zapCards = [];
        $number = $number->getValue();

        $isExcludeAll = 0;
        if ($createrID != 0)
            if (isset($arParts[$createrID]) && isset($arParts[$createrID][$number])) {
                foreach ($arParts[$createrID][$number] as $v) {
                    if ($v["isSklad"] == 1) $isExcludeAll = 1;
                }
            }
        if ($isExcludeAll == 0) {
            $qb = $this->connection->createQueryBuilder()
                ->select(
                    "a.price",
                    "a.zapCardID",
                    " a.number",
                    "a.createrID",
                    "aa.name AS createrName",
                    "aa.creater_weightID",
                    "d.zapSkladID",
                    "'наличие' AS srok",
                    "d.name_short AS postavka",
                    "d.name_short AS postavkaName",
                    "0 AS srokInDays",
                    "(ifNull(Sum(c.quantityIn), 0) - ifNull(Sum(if(c.quantityIn > 0, c.reserve, 0)), 0) + ifNull(Sum(if(c.quantityIn > 0, c.quantityPath, 0)), 0)) AS quantity",
                    "Sum(c.quantityPath) AS quantityPath",
                    "MAX(b.dateofinplan) AS dateofinplan"
                )
                ->from('zapCards', 'a')
                ->innerJoin('a', 'creaters', 'aa', 'a.createrID = aa.createrID')
                ->innerJoin('a', 'income', 'b', 'a.zapCardID = b.zapCardID')
                ->innerJoin('b', 'income_sklad', 'c', 'b.incomeID = c.incomeID')
                ->innerJoin('c', 'zapSklad', 'd', 'd.zapSkladID = c.zapSkladID')
                ->andWhere('a.isDeleted = 0')
                ->andWhere('a.number = :number')
                ->setParameter('number', $number)
                ->groupBy('a.zapCardID, c.zapSkladID');

            if ($createrID) {
                $qb
                    ->andWhere('a.createrID = :createrID')
                    ->setParameter('createrID', $createrID);
            }

            $result = $qb->executeQuery()->fetchAllAssociative();

            foreach ($result as $row) {
                if ($row["quantity"] > 0) {
                    if (!isset($zapCards[$row['zapCardID']])) $zapCards[$row['zapCardID']] = $this->zapCardRepository->findOneBy(['zapCardID' => $row['zapCardID']]);
                    $row["isZamena"] = $isZamena;
                    $row["isOriginal"] = $isOriginal;
                    $row["isSklad"] = 1;
                    $row['zapCard'] = $zapCards[$row['zapCardID']];
                    $row['name'] = $row['zapCard']->getDetailName();
                    $arParts[$row["createrID"]][$row["number"]][] = $row;
                }
            }
        }

        return $arParts;
    }

    /**
     * @param array $arParts
     * @param int $isZamena
     * @param bool $isOriginal
     * @param DetailNumber $number
     * @param int|null $createrID
     * @return array
     * @throws Exception
     */
    public function used(array $arParts, int $isZamena, bool $isOriginal, DetailNumber $number, int $createrID = null): array
    {
        $number = $number->getValue() . 'USED';

        $isExcludeAll = 0;
        if ($createrID != 0)
            if (isset($arParts[$createrID]) && isset($arParts[$createrID][$number])) {
                foreach ($arParts[$createrID][$number] as $v) {
                    if ($v["isSklad"]) $isExcludeAll = 1;
                }
            }
        if ($isExcludeAll == 0) {
            $qb = $this->connection->createQueryBuilder()
                ->select(
                    "a.price",
                    " a.zapCardID",
                    "a.number",
                    "a.createrID",
                    "aa.name AS createrName",
                    "aa.creater_weightID",
                    "1 AS zapSkladID",
                    "'заказ' AS srok",
                    "'заказ' AS postavka",
                    "'заказ' AS postavkaName",
                    "0 AS srokInDays",
                    "a.price1 AS price"
                )
                ->from('zapCards', 'a')
                ->innerJoin('a', 'creaters', 'aa', 'a.createrID = aa.createrID')
                ->andWhere('a.isDeleted = 0')
                ->andWhere('a.shop_typeID = 11')
                ->andWhere('a.number = :number')
                ->setParameter('number', $number);

            if ($createrID) {
                $qb
                    ->andWhere('a.createrID = :createrID')
                    ->setParameter('createrID', $createrID);
            }

            $result = $qb->executeQuery()->fetchAllAssociative();

            foreach ($result as $row) {
                if ($row["quantity"] > 0) {
                    $row["isZamena"] = $isZamena;
                    $row["isOriginal"] = $isOriginal;
                    $row["isSklad"] = 0;
                    $row['zapCard'] = $this->zapCardRepository->findOneBy(['zapCardID' => $row['zapCardID']]);
                    $arParts[$row["createrID"]][$row["number"]][] = $row;
                }
            }
        }

        return $arParts;
    }

    /**
     * @param DetailNumber $number
     * @param Creater $creater
     * @param ProviderPrice $providerPrice
     * @return float
     * @throws Exception
     */
    public function priceZak(DetailNumber $number, Creater $creater, ProviderPrice $providerPrice): float
    {
        $number = $number->getValue();

        $qb = $this->connection->createQueryBuilder()
            ->select("a.price")
            ->from($creater->isOriginal() ? $creater->getTableName() : 'shopPriceN', 'a')
            ->andWhere('a.number = :number')
            ->setParameter('number', $number)
            ->andWhere('a.createrID = :createrID')
            ->setParameter('createrID', $creater->getId())
            ->andWhere('a.providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPrice->getId());

        return $qb->executeQuery()->fetchOne() ?: 0;
    }

    /**
     * @param string $tableName
     * @param array $providerPrices
     * @return array
     * @throws Exception
     */
    public function priceZakForProviderPrices(string $tableName, array $providerPrices): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.number",
                "a.createrID",
                "a.providerPriceID",
                "a.price"
            )
            ->from($tableName, 'a');

        $qb->andWhere($qb->expr()->in('providerPriceID', $providerPrices));
        $arr = $qb->executeQuery()->fetchAllAssociative();
        $result = [];

        foreach ($arr as $item) {
            $result[$item['createrID']][$item['number']][$item['providerPriceID']] = $item['price'];
        }

        return $result;
    }

    /**
     * @param DetailNumber $number
     * @param Creater $creater
     * @param ProviderPrice $providerPrice
     * @return int
     * @throws Exception
     */
    public function quantityInPrice(DetailNumber $number, Creater $creater, ProviderPrice $providerPrice): int
    {
        $number = $number->getValue();

        $qb = $this->connection->createQueryBuilder()
            ->select("a.quantity")
            ->from($creater->isOriginal() ? $creater->getTableName() : 'shopPriceN', 'a')
            ->andWhere('a.number = :number')
            ->setParameter('number', $number)
            ->andWhere('a.createrID = :createrID')
            ->setParameter('createrID', $creater->getId())
            ->andWhere('a.providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPrice->getId());

        return $qb->executeQuery()->fetchOne() ?: 0;
    }

    /**
     * @param array $arParts
     * @param array $isProvider
     * @param DetailNumber $number
     * @param int|null $createrID
     * @return array
     * @throws Exception
     */
    public function neorig(array $arParts, array $isProvider, DetailNumber $number, int $createrID = null): array
    {
        $number = $number->getValue();

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.price",
                " a.number",
                "a.createrID",
                "if (a.creater_add <> '', CONCAT(aa.name, ' ', a.creater_add), aa.name) AS createrName",
                "aa.creater_weightID",
                "b.providerID",
                "b.providerPriceID",
                "b.srok",
                "b.description AS postavka",
                "b.name AS postavkaName",
                "b.srokInDays",
                "a.quantity",
                "a.name",
                "a.dateOfChanged AS dateofchanged_number",
                "b.dateofchanged",
                "b.daysofchanged"
            )
            ->from('shopPriceN', 'a')
            ->innerJoin('a', 'creaters', 'aa', 'a.createrID = aa.createrID')
            ->innerJoin('a', 'providerPrices', 'b', 'a.providerPriceID = b.providerPriceID')
            ->andWhere('b.isHide = 0')
            ->andWhere('a.providerPriceID NOT IN (SELECT providerPriceID FROM numberPricesExclude WHERE number = a.number AND createrID = a.createrID)')
            ->andWhere('a.number = :number')
            ->setParameter('number', $number);

        if ($createrID) {
            $qb->andWhere('a.createrID = :createrID')->setParameter('createrID', $createrID);
        }

        $result = $qb->executeQuery()->fetchAllAssociative();

        foreach ($result as $row) {
            if ($row["price"] > 0) {
                $isExclude = 0;
                if (isset($isProvider[$row["createrID"]])) {
                    if (isset($isProvider[$row["createrID"]]["isProvider"]) && ($isProvider[$row["createrID"]]["isProvider"] == 1)) $isExclude = 1;
                    if (isset($isProvider[$row["createrID"]]["disableDays"]) && (in_array($row["providerID"], $isProvider[$row["createrID"]]["disableDays"]))) $isExclude = 1;
                }

                if ($isExclude == 0) {
                    $row["isZamena"] = 0;
                    $row["isOriginal"] = false;
                    $row["isSklad"] = 0;
//                    $row['zapCard'] = $this->zapCardRepository->findOneBy(['number' => $row['number'], 'createrID' => $row['createrID']]);
                    $arParts[$row["createrID"]][$row["number"]][] = $row;
                }
            }
        }
        return $arParts;
    }

    /**
     * @param array $arParts
     * @param array $isProvider
     * @param int $isZamena
     * @param bool $isOriginal
     * @param string $tableName
     * @param DetailNumber $number
     * @param int|null $createrID
     * @return array
     * @throws Exception
     */
    public function zakaz(array $arParts, array $isProvider, int $isZamena, bool $isOriginal, string $tableName, DetailNumber $number, int $createrID = null): array
    {
        $number = $number->getValue();


        $createrName = "aa.name";
        if (!$isOriginal) {
            $createrName = "if (a.creater_add <> '', CONCAT(aa.name, ' ', a.creater_add), aa.name)";
            $tableName = "shopPriceN";
        }

        $isExcludeAll = 0;
        if (isset($arParts[$createrID]) && isset($arParts[$createrID][$number])) {
            foreach ($arParts[$createrID][$number] as $v) {
                if ($v["isSklad"] == 0) $isExcludeAll = 1;
            }
        }

        if ($isExcludeAll == 0) {
            $qb = $this->connection->createQueryBuilder()
                ->select(
                    "a.price",
                    " a.number",
                    "a.createrID",
                    $createrName . " AS createrName",
                    "aa.creater_weightID",
                    "b.providerID",
                    "b.providerPriceID",
                    "CONCAT(b.name, ' ', b.description) AS providerPriceName",
                    "b.srok",
                    "b.description AS postavka",
                    "b.name AS postavkaName",
                    "b.srokInDays",
                    "a.quantity",
                    "a.name",
                    "a.dateOfChanged AS dateofchanged_number",
                    "b.dateofchanged",
                    "b.daysofchanged"
                )
                ->from($tableName, 'a')
                ->innerJoin('a', 'creaters', 'aa', 'a.createrID = aa.createrID')
                ->innerJoin('a', 'providerPrices', 'b', 'a.providerPriceID = b.providerPriceID')
                ->andWhere('b.isHide = 0')
                ->andWhere('a.providerPriceID NOT IN (SELECT providerPriceID FROM numberPricesExclude WHERE number = a.number AND createrID = a.createrID)')
                ->andWhere('a.number = :number')
                ->setParameter('number', $number)
                ->orderBy('a.price');

            if ($createrID) {
                $qb->andWhere('a.createrID = :createrID')->setParameter('createrID', $createrID);
            }

            $result = $qb->executeQuery()->fetchAllAssociative();

            foreach ($result as $row) {
                if ($row["price"] > 0) {
                    $isExclude = 0;
                    if (isset($isProvider[$row["createrID"]])) {
                        if (isset($isProvider[$row["createrID"]]["isProvider"]) && ($isProvider[$row["createrID"]]["isProvider"] == 1)) $isExclude = 1;
                        if (isset($isProvider[$row["createrID"]]["disableDays"]) && (in_array($row["providerID"], $isProvider[$row["createrID"]]["disableDays"]))) $isExclude = 1;
                    }

                    if ($isExclude == 0) {
                        $row["isZamena"] = $isZamena;
                        $row["isOriginal"] = $isOriginal;
                        $row["isSklad"] = 0;
//                    $row['zapCard'] = $this->zapCardRepository->findOneBy(['number' => $row['number'], 'createrID' => $row['createrID']]);
                        $arParts[$row["createrID"]][$row["number"]][] = $row;
                    }
                }
            }
        }

        return $arParts;
    }

    /**
     * @param array $arParts
     * @param ZapSklad|null $zapSklad
     * @param array $creaters
     * @return array
     * @throws Exception
     */
    public function allInWarehouse(array $arParts, ?ZapSklad $zapSklad, array $creaters = []): array
    {
        if ($zapSklad) $where = " AND bb.zapSkladID = :zapSkladID"; else $where = '';

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.zapCardID",
                "a.price",
                "a.number",
                "a.createrID",
                "aa.name AS createrName",
                "aa.creater_weightID",
                "a.shop_typeID",
                "(SELECT ifNull(Sum(bb.quantityIn - bb.reserve),0) FROM income aa INNER JOIN income_sklad bb ON aa.incomeID = bb.incomeID WHERE aa.zapCardID = a.zapCardID AND aa.status = :status $where) AS quantity",
            )
            ->from('zapCards', 'a')
            ->innerJoin('a', 'creaters', 'aa', 'a.createrID = aa.createrID')
            ->andWhere('a.isDeleted = 0')
            ->andWhere("(SELECT ifNull(Sum(bb.quantityIn - bb.reserve),0) FROM income aa INNER JOIN income_sklad bb ON aa.incomeID = bb.incomeID WHERE aa.zapCardID = a.zapCardID AND aa.status = :status $where) > 0")
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE);

        if ($zapSklad) {
            $qb->setParameter('zapSkladID', $zapSklad->getId());
        }

        if (!empty($creaters)) {
            $qb->andWhere($qb->expr()->or(
                $qb->expr()->in('aa.createrID', $creaters),
                $qb->expr()->eq('aa.isOriginal', 0)
            ));
        }

        $result = $qb->executeQuery()->fetchAllAssociative();

        $zapCardId = array_map(function ($item) { return $item['zapCardID']; }, $result);

        $zapCards = $this->zapCardRepository->findByZapCardsWithProfits($zapCardId);

        foreach ($result as $row) {
            if ($row["quantity"] > 0) {
                $row["isSklad"] = 1;
                $row['zapCard'] = $zapCards[$row['zapCardID']];
                $row['name'] = $row['zapCard']->getDetailName();
                $arParts[$row["createrID"]][$row["number"]][] = $row;
            }
        }

//        if (!$isSimple && $zapSklad && $zapSklad->getId() == ZapSklad::MSK) {
//            $providerPrices = [277];
//
//            $arParts = $this->findByProviderPrices($arParts, $providerPrices, true);
//        }

        return $arParts;
    }

    public function findByProviderPrices(array $arParts, array $providerPrices, bool $isNal = false): array
    {
        $isProvider = $this->detailProviderExcludeFetcher->findByProviderPrices($providerPrices);

        $result = [];
        for ($i = 0; $i <= 10; $i++) {
            $qb = $this->connection->createQueryBuilder()
                ->select(
                    "a.price",
                    " a.number",
                    "a.createrID",
                    "a.name",
                    "aa.name AS createrName",
                    "aa.creater_weightID",
                    "a.providerPriceID",
                    "a.quantity",
                    "b.providerID"
                )
                ->from('shopPrice' . ($i == 0 ? 'N' : $i), 'a')
                ->innerJoin('a', 'creaters', 'aa', 'a.createrID = aa.createrID')
                ->innerJoin('a', 'providerPrices', 'b', 'a.providerPriceID = b.providerPriceID')
                ->andWhere('a.providerPriceID NOT IN (SELECT providerPriceID FROM numberPricesExclude WHERE number = a.number AND createrID = a.createrID)');
            if ($isNal) {
                $qb->andWhere('a.quantity > 0');
            }

            $qb->andWhere($qb->expr()->in('a.providerPriceID', $providerPrices));

            $result = array_merge($qb->executeQuery()->fetchAllAssociative(), $result);
        }
        foreach ($result as $row) {
            $isExclude = 0;
            if (isset($isProvider[$row["createrID"]][$row["number"]])) {
                if (isset($isProvider[$row["createrID"]][$row["number"]]["isProvider"]) && ($isProvider[$row["createrID"]][$row["number"]]["isProvider"] == 1)) $isExclude = 1;
                if (isset($isProvider[$row["createrID"]][$row["number"]]["disableDays"]) && (in_array($row["providerID"], $isProvider[$row["createrID"]][$row["number"]]["disableDays"]))) $isExclude = 1;
            }

            if ($isExclude == 0) {
                if ($row["price"] > 0) {
                    $row["isSklad"] = 0;
                    $arParts[$row["createrID"]][$row["number"]][] = $row;
                }
            }
        }
        return $arParts;
    }

    public function existNeorig(DetailNumber $number): bool
    {
        $result = $this->connection->createQueryBuilder()
            ->select("Count(*)")
            ->from('shopPriceN', 'a')
            ->andWhere('a.number = :number')
            ->setParameter('number', $number->getValue())
            ->executeQuery()
            ->fetchOne();

        return $result > 0;
    }

    public function existOriginal(DetailNumber $number, int $providerPriceID): bool
    {
        for ($i = 1; $i <= 10; $i++) {
            $result = $this->connection->createQueryBuilder()
                ->select("Count(*)")
                ->from('shopPrice' . $i, 'a')
                ->andWhere('a.number = :number')
                ->setParameter('number', $number->getValue())
                ->andWhere('a.providerPriceID = :providerPriceID')
                ->setParameter('providerPriceID', $providerPriceID)
                ->andWhere('TO_DAYS (NOW()) - TO_DAYS(dateOfChanged) <= 7 AND dateOfChanged <> 0')
                ->executeQuery()
                ->fetchOne();

            if ($result > 0) return true;
        }
        return false;
    }
}