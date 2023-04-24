<?php


namespace App\ReadModel\Income;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Order\IncomeOrder;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\ReadModel\Income\Filter\Income\Filter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class IncomeFetcher
{
    private Connection $connection;
    private $repository;
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME_ORDERS = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION_ORDERS = 'asc';


    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Income::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): Income
    {
        return $this->repository->get($id);
    }

    /**
     * Получение количества, находящегося в статусе "В пути" и "Выкуплено" и идущего на склады.
     * Группировка по дате планируемого прихода.
     *
     * @param int $zapCardID
     * @return array
     * @throws Exception
     */
    public function getDateOfInPlanByZapCard(int $zapCardID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("Sum(quantityPath - reserve) AS quantityPath, dateofinplan")
            ->from('income', 'i')
            ->andWhere('status IN (6,7)')
            ->andWhere('quantityPath - reserve > 0')
            ->andWhere('i.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->groupBy('dateofinplan');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * Количество деталей, находящихся в пути, доступных для будущего резерва.
     *
     * @param int $zapCardID
     * @param int $zapSkladID
     * @return int
     * @throws Exception
     */
    public function getQuantityInPathByZapCardAndZapSklad(int $zapCardID, int $zapSkladID): int
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("ifnull(Sum(s.quantityIn + s.quantityPath - s.reserve), 0) AS inPath")
            ->from('income_sklad', 's')
            ->innerJoin('s', 'income', 'i', 's.incomeID = i.incomeID')
            ->andWhere('s.quantityPath > 0')
            ->andWhere('i.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere('s.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSkladID);

        return $qb->executeQuery()->fetchOne();
    }

    /**
     * @param int $zapCardID
     * @param int $zapSkladID
     * @return float
     * @throws Exception
     */
    public function getPriceZakByZapCardAndZapSklad(int $zapCardID, int $zapSkladID): float
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("price")
            ->from('income_sklad', 's')
            ->innerJoin('s', 'income', 'i', 's.incomeID = i.incomeID')
            ->andWhere('s.quantityPath - s.reserve > 0')
            ->andWhere('i.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere('s.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSkladID)
            ->orderBy('i.dateofadded', 'DESC')
            ->setMaxResults(1);

        return $qb->executeQuery()->fetchOne();
    }

    /**
     * Получение среднее количество дней прихода детали на склад
     *
     * @param string $number
     * @param int $createrID
     * @param int $providerPriceID
     * @return float|null
     * @throws Exception
     */
    public function getAverageDaysIncome(string $number, int $createrID, int $providerPriceID): ?float
    {
        $average = [];

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.dateofzakaz",
                "(TO_DAYS(a.dateofin) - TO_DAYS(a.dateofzakaz)) AS daysZakaz",
                "(TO_DAYS(a.dateofin) - TO_DAYS(a.dateofadded)) AS daysAdd",
            )
            ->from('income', 'a')
            ->innerJoin('a', 'zapCards', 'b', 'a.zapCardID = b.zapCardID')
            ->andWhere('a.status = 8')
            ->andWhere('a.dateofin <> 0')
            ->andWhere('providerPriceID <> 0')
            ->andWhere('b.number = :number')
            ->setParameter('number', $number)
            ->andWhere('b.createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->andWhere('a.providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPriceID);
        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            if ($item['dateofzakaz'] == 0 || !$item['dateofzakaz']) {
                $average[] = $item['daysAdd'];
            } else {
                $average[] = $item['daysZakaz'];
            }
        }

        if ($average) {
            return round(array_sum($average) / count($average) * 100) / 100;
        }

        return null;
    }

    /**
     * @param string $number
     * @param int $createrID
     * @param int $providerPriceID
     * @return int
     * @throws Exception
     */
    public function getPercentIncome(string $number, int $createrID, int $providerPriceID): int
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("Count(*)")
            ->from('income', 'a')
            ->innerJoin('a', 'zapCards', 'b', 'a.zapCardID = b.zapCardID')
            ->andWhere('a.status = 8')
            ->andWhere('providerPriceID <> 0')
            ->andWhere('b.number = :number')
            ->setParameter('number', $number)
            ->andWhere('b.createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->andWhere('a.providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPriceID);
        $countIncome = $qb->executeQuery()->fetchOne();

        $qb = $this->connection->createQueryBuilder()
            ->select("Count(*)")
            ->from('income', 'a')
            ->innerJoin('a', 'zapCards', 'b', 'a.zapCardID = b.zapCardID')
            ->andWhere('(a.status IN (5,10) OR a.status = 4 AND a.deleteReasonID IN (2,3))')
            ->andWhere('providerPriceID <> 0')
            ->andWhere('b.number = :number')
            ->setParameter('number', $number)
            ->andWhere('b.createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->andWhere('a.providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPriceID);
        $countDecline = $qb->executeQuery()->fetchOne();

        if ($countIncome + $countDecline == 0) return 0;

        return round($countIncome / ($countIncome + $countDecline) * 100);
    }

    /**
     * @param array $arZapCards
     * @return array
     * @throws Exception
     */
    public function assocOrderedByZapCards(array $arZapCards): array
    {
        if (!$arZapCards) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.zapCardID",
                "ifNull(Sum(a.quantity), 0)",
            )
            ->from('income', 'a')
            ->leftJoin('a', 'order_goods', 'og', 'a.incomeID = og.incomeID')
            ->andWhere('og.goodID IS NULL')
//            ->andWhere("a.incomeID NOT IN (SELECT incomeID FROM order_goods WHERE incomeID IS NOT NULL)")
            ->groupBy("zapCardID");

        $qb->andWhere($qb->expr()->in('zapCardID', $arZapCards));
        $qb->andWhere($qb->expr()->in('a.status', [IncomeStatus::ORDERED, IncomeStatus::IN_PATH, IncomeStatus::PURCHASED, IncomeStatus::INCOME_IN_WAREHOUSE]));

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    /**
     * Получение количества детали на складе, доступного для резерва.
     *
     * @param int $zapCardID
     * @param int $zapSkladID
     * @return int
     * @throws Exception
     */
    public function getQuantityByZapCardAndZapSklad(int $zapCardID, int $zapSkladID): int
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("ifNull(Sum(s.quantityIn + s.quantityPath - s.reserve), 0)")
            ->from('income_sklad', 's')
            ->innerJoin('s', 'income', 'i', 's.incomeID = i.incomeID')
            ->andWhere('i.status = :status')
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
//            ->andWhere('s.quantityIn > 0')
            ->andWhere('i.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere('s.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSkladID);
        return $qb->executeQuery()->fetchOne();
    }

    /**
     * Получение массива карточек деталей и складов с количеством деталей, находящихся на складе и доступных для резерва.
     *
     * $arr[zapCardID][zapSkladID] = quantity;
     *
     * @param array $arZapCards
     * @return array
     * @throws Exception
     */
    public function findQuantityInWarehouseByZapCards(array $arZapCards): array
    {
        $arr = [];

        if (!$arZapCards) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.zapCardID",
                "b.zapSkladID",
                "ifNull(Sum(b.quantityIn + b.quantityPath - b.reserve), 0) AS quantity",
            )
            ->from('income', 'a')
            ->innerJoin('a', 'income_sklad', 'b', 'a.incomeID = b.incomeID')
            ->andWhere('a.status = :status')
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
//            ->andWhere('b.quantityIn > 0')
            ->groupBy("a.zapCardID, b.zapSkladID");

        $qb->andWhere($qb->expr()->in('a.zapCardID', $arZapCards));

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $arr[$item['zapCardID']][$item['zapSkladID']] = $item['quantity'];
        }

        return $arr;
    }

    /**
     * Получение самой ранней цены закупки из приходов, где есть детали, находящихся на складе и доступные для резерва.
     *
     * @param int $zapCardID
     * @return float|null
     * @throws Exception
     */
    public function findPriceInWarehouseByZapCard(int $zapCardID): ?float
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("a.price")
            ->from('income', 'a')
            ->andWhere('a.status = :status')
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
            ->andWhere('a.quantityIn + a.quantityPath - a.reserve > 0')
            ->andWhere("a.zapCardID = :zapCardID")
            ->setParameter('zapCardID', $zapCardID)
            ->orderBy('a.dateofadded')
            ->setMaxResults(1);
        return $qb->executeQuery()->fetchOne() ?: null;
    }

    /**
     * Получение массива складов с количеством деталей, находящихся на складе и доступных для резерва.
     *
     * $arr[zapSkladID] = quantity;
     *
     * @param int $zapCardID
     * @return array
     * @throws Exception
     */
    public function findQuantityInWarehouseByZapCard(int $zapCardID): array
    {
        $arr = [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.zapCardID",
                "b.zapSkladID",
                "ifnull(Sum(b.quantityIn + b.quantityPath - b.reserve), 0) AS quantity",
            )
            ->from('income', 'a')
            ->innerJoin('a', 'income_sklad', 'b', 'a.incomeID = b.incomeID')
            ->andWhere('a.status = :status')
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
//            ->andWhere('b.quantityIn > 0')
            ->andWhere("a.zapCardID = :zapCardID")
            ->setParameter('zapCardID', $zapCardID)
            ->groupBy("a.zapCardID, b.zapSkladID");
        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $arr[$item['zapSkladID']] = $item['quantity'];
        }

        return $arr;
    }

    /**
     * Получение массива карточек деталей и складов с количеством деталей, находящихся в пути.
     *
     * $arr[zapCardID][zapSkladID] = quantity;
     *
     * @param array $arZapCards
     * @return array
     * @throws Exception
     */
    public function findQuantityOrderedByZapCards(array $arZapCards): array
    {
        $arr = [];

        if (!$arZapCards) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.zapCardID",
                "b.zapSkladID",
                "ifnull(Sum(b.quantity), 0) AS quantity",
            )
            ->from('income', 'a')
            ->innerJoin('a', 'income_sklad', 'b', 'a.incomeID = b.incomeID')
            ->groupBy("a.zapCardID, b.zapSkladID");

        $qb->andWhere($qb->expr()->in('a.status', [IncomeStatus::ORDERED, IncomeStatus::IN_PATH, IncomeStatus::PURCHASED, IncomeStatus::INCOME_IN_WAREHOUSE]));
        $qb->andWhere($qb->expr()->in('a.zapCardID', $arZapCards));

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $arr[$item['zapCardID']][$item['zapSkladID']] = $item['quantity'];
        }

        return $arr;
    }

    /**
     * Получение количества деталей, находящихся на складе и доступные для резерва.
     *
     * @param int $zapCardID
     * @param int $zapSkladID
     * @return int
     * @throws Exception
     */
    public function findQuantityInWarehouseByZapCardAndZapSklad(int $zapCardID, int $zapSkladID): int
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "ifnull(Sum(b.quantityIn + b.quantityPath - b.reserve), 0) AS quantity",
            )
            ->from('income', 'a')
            ->innerJoin('a', 'income_sklad', 'b', 'a.incomeID = b.incomeID')
            ->andWhere('a.status = :status')
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
//            ->andWhere('b.quantityIn > 0')
            ->andWhere("a.zapCardID = :zapCardID")
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere("b.zapSkladID = :zapSkladID")
            ->setParameter('zapSkladID', $zapSkladID);
        return $qb->executeQuery()->fetchOne();
    }

    /**
     * Получение количества "В наличии", "В пути", "В резерве" детали на складах
     *
     * $arr[zapSkladID] = [
     *      zapCardID,
     *      zapSkladID,
     *      quantityIn,
     *      reserve,
     *      quantityPath
     * ];
     *
     * @param int $zapCardID
     * @return array
     * @throws Exception
     */
    public function findAllQuantitiesByZapCard(int $zapCardID): array
    {
        $arr = [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.zapCardID",
                "b.zapSkladID",
                "ifnull(Sum(b.quantityIn),0) AS quantityIn",
                "ifnull(Sum(b.reserve),0) AS reserve",
                "ifnull(Sum(b.quantityPath),0) AS quantityPath",
            )
            ->from('income', 'a')
            ->innerJoin('a', 'income_sklad', 'b', 'a.incomeID = b.incomeID')
            ->andWhere("a.zapCardID = :zapCardID")
            ->setParameter('zapCardID', $zapCardID)
            ->groupBy("b.zapSkladID");
        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $arr[$item['zapSkladID']] = $item;
        }

        return $arr;
    }

    /**
     * Получение количества "В наличии", "В пути", "В резерве" деталей на складах
     *
     * $arr[zapCardID][zapSkladID] = [
     *      zapCardID,
     *      zapSkladID,
     *      quantityIn,
     *      reserve,
     *      quantityPath
     * ];
     *
     * @param array $arZapCards
     * @return array
     * @throws Exception
     */
    public function findAllQuantitiesByZapCards(array $arZapCards): array
    {
        $arr = [];
        if (!$arZapCards) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.zapCardID",
                "b.zapSkladID",
                "ifnull(Sum(b.quantityIn),0) AS quantityIn",
                "ifnull(Sum(b.reserve),0) AS reserve",
                "ifnull(Sum(b.quantityPath),0) AS quantityPath",
            )
            ->from('income', 'a')
            ->innerJoin('a', 'income_sklad', 'b', 'a.incomeID = b.incomeID')
            ->groupBy("a.zapCardID, b.zapSkladID");

        $qb->andWhere($qb->expr()->in('a.zapCardID', $arZapCards));
        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $arr[$item['zapCardID']][$item['zapSkladID']] = $item;
        }

        return $arr;
    }

    /**
     * Получение прихода с приходной накладной
     *
     * @param Income $income
     * @return array
     * @throws Exception
     */
    public function findPNByIncomeWithDocument(Income $income): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'id.incomeDocumentID',
                'id.doc_typeID',
                'id.document_num',
                'id.dateofadded',
                '1 AS zapSkladID',
                'id.firmID',
                'i.quantity'
            )
            ->from('income', 'i')
            ->innerJoin('i', 'incomeDocuments', 'id', 'id.incomeDocumentID = i.incomeDocumentID')
            ->where('i.incomeID = :incomeID')
            ->setParameter('incomeID', $income->getId())
            ->andWhere('id.doc_typeID = :doc_typeID')
            ->setParameter('doc_typeID', DocumentType::PN);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * Получение прихода с приходной накладной
     *
     * @param ZapCard $zapCard
     * @return array
     * @throws Exception
     */
    public function findPNByZapCardWithDocument(ZapCard $zapCard): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'id.incomeDocumentID',
                'id.doc_typeID',
                'id.document_num',
                'id.dateofadded',
                '1 AS zapSkladID',
                'id.firmID',
                'i.quantity'
            )
            ->from('income', 'i')
            ->innerJoin('i', 'incomeDocuments', 'id', 'id.incomeDocumentID = i.incomeDocumentID')
            ->where('i.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCard->getId())
            ->andWhere('id.doc_typeID = :doc_typeID')
            ->setParameter('doc_typeID', DocumentType::PN);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * Получение прихода с возвратной накладной
     *
     * @param Income $income
     * @return array
     * @throws Exception
     */
    public function findVZByIncomeWithDocument(Income $income): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'id.incomeDocumentID',
                'id.doc_typeID',
                'id.document_num',
                'id.dateofadded',
                'og.zapSkladID',
                'id.firmID',
                'i.quantity'
            )
            ->from('income', 'i')
            ->innerJoin('i', 'incomeDocuments', 'id', 'id.incomeDocumentID = i.incomeDocumentID')
            ->innerJoin('id', 'order_goods', 'og', 'id.incomeDocumentID = og.incomeDocumentID')
            ->where('i.incomeID = :incomeID')
            ->setParameter('incomeID', $income->getId())
            ->andWhere('id.doc_typeID = :doc_typeID')
            ->setParameter('doc_typeID', DocumentType::VZ);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * Получение прихода с возвратной накладной
     *
     * @param ZapCard $zapCard
     * @return array
     * @throws Exception
     */
    public function findVZByZapCardWithDocument(ZapCard $zapCard): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'id.incomeDocumentID',
                'id.doc_typeID',
                'id.document_num',
                'id.dateofadded',
                'og.zapSkladID',
                'id.firmID',
                'i.quantity'
            )
            ->from('income', 'i')
            ->innerJoin('i', 'incomeDocuments', 'id', 'id.incomeDocumentID = i.incomeDocumentID')
            ->innerJoin('id', 'order_goods', 'og', 'id.incomeDocumentID = og.incomeDocumentID')
            ->where('i.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCard->getId())
            ->andWhere('id.doc_typeID = :doc_typeID')
            ->setParameter('doc_typeID', DocumentType::VZ);

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * Количество приходов, имеющих закупочную отпускную нулевую цену, находящихся в статусе "Оприходуется офисом"
     *
     * @param int $providerID
     * @return bool
     * @throws Exception
     */
    public function isExistNotPriceZakIncomeInWarehouse(int $providerID): bool
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'Count(i.incomeID)'
            )
            ->from('income', 'i')
            ->innerJoin('i', 'providerPrices', 'p', 'p.providerPriceID = i.providerPriceID')
            ->where('p.providerID = :providerID')
            ->setParameter('providerID', $providerID)
            ->andWhere('i.status = :status')
            ->setParameter('status', IncomeStatus::INCOME_IN_WAREHOUSE)
            ->andWhere('i.price = 0 OR i.priceZak = 0');
        return $qb->executeQuery()->fetchOne() > 0;
    }

    /**
     * Есть ли неотсканированные детали со статусом "Оприходуется офисом"
     *
     * @param int $providerID
     * @return bool
     * @throws Exception
     */
    public function isExistUnPackIncomeInWarehouse(int $providerID): bool
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'Count(i.incomeID)'
            )
            ->from('income', 'i')
            ->innerJoin('i', 'providerPrices', 'p', 'p.providerPriceID = i.providerPriceID')
            ->where('p.providerID = :providerID')
            ->setParameter('providerID', $providerID)
            ->andWhere('i.status = :status')
            ->setParameter('status', IncomeStatus::INCOME_IN_WAREHOUSE)
            ->andWhere('i.quantityUnPack <> i.quantity');
        return $qb->executeQuery()->fetchOne() > 0;
    }

    /**
     * Есть ли приходы с непроверенной суммой у поставщика в статусе "Оприходуется офисом"
     *
     * @param int $providerID
     * @return bool
     */
    public function isExistNotSumDoneUnPackIncomeInWarehouse(int $providerID): bool
    {
        try {
            $qb = $this->connection->createQueryBuilder()
                ->select('Count(i.incomeID)')
                ->from('income', 'i')
                ->innerJoin('i', 'providerPrices', 'p', 'p.providerPriceID = i.providerPriceID')
                ->where('p.providerID = :providerID')
                ->setParameter('providerID', $providerID)
                ->andWhere('i.status = :status')
                ->setParameter('status', IncomeStatus::INCOME_IN_WAREHOUSE)
                ->andWhere('i.isSummDone = 0');
            return $qb->executeQuery()->fetchOne() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Сумма товаров поставщика проверена
     *
     * @param int $providerID
     * @throws Exception
     */
    public function updateSumDoneUnPackIncomeInWarehouse(int $providerID)
    {
        $qb = $this->connection->createQueryBuilder()
            ->update('income', 'i')
            ->set('i.isSummDone', 'true')
            ->andWhere('i.providerPriceID IN (SELECT providerPriceID FROM providerPrices WHERE providerID = :providerID)')
            ->setParameter('providerID', $providerID)
            ->andWhere('i.status = :status')
            ->setParameter('status', IncomeStatus::INCOME_IN_WAREHOUSE);
        $qb->executeQuery();
    }

    /**
     * Сумма товаров поставщика со статусом "Оприходуется офисом"
     *
     * @param int $providerID
     * @return float
     */
    public function getSumUnPackIncomeInWarehouse(int $providerID): float
    {
        try {

            $qb = $this->connection->createQueryBuilder()
                ->select(
                    'ifNull(Sum(i.price * i.quantity), 0)'
                )
                ->from('income', 'i')
                ->innerJoin('i', 'providerPrices', 'p', 'p.providerPriceID = i.providerPriceID')
                ->where('p.providerID = :providerID')
                ->setParameter('providerID', $providerID)
                ->andWhere('i.status = :status')
                ->setParameter('status', IncomeStatus::INCOME_IN_WAREHOUSE);
            return $qb->executeQuery()->fetchOne();
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Получить приход заказной детали
     *
     * @param int|null $incomeID
     * @return array|null
     * @throws Exception
     */
    public function getIncomeForOrderGood(?int $incomeID): ?array
    {
        if (!$incomeID) return null;
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'status',
                'dateofzakaz',
                'dateofout',
                'dateofinplan'
            )
            ->from('income', 'i')
            ->where('i.incomeID = :incomeID')
            ->setParameter('incomeID', $incomeID);
        return $qb->executeQuery()->fetchAssociative() ?: null;
    }

    /**
     * Получить первый приход детали
     *
     * @param int $zapCardID
     * @return array|null
     * @throws Exception
     */
    public function getFirstIncomeByZapCardID(int $zapCardID): ?array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'i.incomeID',
                'i.dateofin',
                'i.price'
            )
            ->from('income', 'i')
            ->where('i.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere('i.quantityIn > 0')
            ->leftJoin('i', 'order_goods', 'og', 'i.incomeID = og.incomeID')
            ->andWhere('og.goodID IS NULL')
//            ->andWhere('incomeID NOT IN (SELECT incomeID FROM order_goods WHERE incomeID IS NOT NULL)')
            ->orderBy('i.dateofadded')
            ->setMaxResults(1);
        return $qb->executeQuery()->fetchAssociative() ?: null;
    }

    /**
     * Получить последней цены прихода
     *
     * @param int $zapCardID
     * @return array|null
     * @throws Exception
     */
    public function getLastIncomeInByZapCardID(int $zapCardID): ?array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'i.incomeID',
                'i.providerPriceID',
                'pp.description AS postavka',
                'i.price'
            )
            ->from('income', 'i')
            ->innerJoin('i', 'providerPrices', 'pp', 'i.providerPriceID = pp.providerPriceID')
            ->andWhere('i.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere('i.status = :status')
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
            ->andWhere('pp.isHide = 0')
            ->orderBy('i.dateofin', 'desc')
            ->setMaxResults(1);
        return $qb->executeQuery()->fetchAssociative() ?: null;
    }

    /**
     * Получить реальное количество складских деталей
     *
     * @param array $zapCards
     * @return array
     * @throws Exception
     */
    public function getQuantityFormZakPostMinByZapCards(array $zapCards): array
    {
        if (!$zapCards) return [];
        $quantities = [];

        // SELECT ifnull(Sum(quantityIn + quantityPath), 0) AS q FROM income WHERE zapCardID = '".AddSlashes($zapCardID)."' AND status = 8 AND incomeID NOT IN (SELECT incomeID FROM order_goods WHERE incomeID <> 0)

        $qb = $this->connection->createQueryBuilder()
            ->select('i.zapCardID, Sum(quantityIn + quantityPath)')
            ->from('income', 'i')
            ->andWhere('i.status = :status')
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
            ->andWhere('NOT EXISTS(SELECT incomeID FROM order_goods WHERE incomeID = i.incomeID)')
            ->groupBy('i.zapCardID');

        $qb->andWhere($qb->expr()->in('i.zapCardID', $zapCards));

        $q1 = $qb->executeQuery()->fetchAllKeyValue();

//        SELECT ifnull(Sum(quantity), 0) AS q FROM income WHERE zapCardID = '".AddSlashes($zapCardID)."' AND status IN (1,2,6,7,9,11) AND incomeID NOT IN (SELECT incomeID FROM order_goods WHERE incomeID <> 0)
        $qb = $this->connection->createQueryBuilder()
            ->select('i.zapCardID, Sum(quantity)')
            ->from('income', 'i')
            ->andWhere('NOT EXISTS(SELECT incomeID FROM order_goods WHERE incomeID = i.incomeID)')
            ->groupBy('i.zapCardID');

        $qb->andWhere($qb->expr()->in('i.status', [
            IncomeStatus::DEFAULT_STATUS,
            IncomeStatus::ORDERED,
            IncomeStatus::IN_PATH,
            IncomeStatus::PURCHASED,
            IncomeStatus::PURCHASED,
            IncomeStatus::IN_WORK,
            IncomeStatus::INCOME_IN_WAREHOUSE
        ]));

        $qb->andWhere($qb->expr()->in('i.zapCardID', $zapCards));

        $q2 = $qb->executeQuery()->fetchAllKeyValue();

//        SSELECT ifnull(Sum(a.quantity), 0) AS q FROM zapCardReserve a INNER JOIN income b ON a.incomeID = b.incomeID WHERE b.zapCardID = '".AddSlashes($zapCardID)."' AND a.dateofclosed = 0 AND b.incomeID NOT IN (SELECT incomeID FROM order_goods WHERE incomeID <> 0)
        $qb = $this->connection->createQueryBuilder()
            ->select('i.zapCardID, Sum(zcr.quantity)')
            ->from('income', 'i')
            ->innerJoin('i', 'zapCardReserve', 'zcr', 'i.incomeID = zcr.incomeID')
            ->andWhere('zcr.dateofclosed IS NULL')
            ->andWhere('NOT EXISTS(SELECT incomeID FROM order_goods WHERE incomeID = i.incomeID)')
            ->groupBy('i.zapCardID');

        $qb->andWhere($qb->expr()->in('i.zapCardID', $zapCards));

        $q3 = $qb->executeQuery()->fetchAllKeyValue();

        foreach ($zapCards as $zapCardID) {
            $quantities[$zapCardID] = ($q1[$zapCardID] ?? 0) + ($q2[$zapCardID] ?? 0) - ($q3[$zapCardID] ?? 0);
        }

        return $quantities;
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface|null
     */
    public function all(Filter $filter, int $page, array $settings): ?PaginationInterface
    {
        if (
            !$filter->number &&
            !$filter->dateofadded &&
            !$filter->createrID &&
            !$filter->isShowLessSpbMax &&
            !$filter->isShowLessMskMax &&
            !$filter->isShowQuantitySpbNull &&
            !$filter->isShowQuantitySrvNull &&
            !$filter->isShowQuantityMskNull &&
            !$filter->incomeOrder &&
            !$filter->isUnpack &&
            !$filter->dateofinplan &&
            !$filter->dateofzakaz &&
            !$filter->incomeID &&
            !$filter->status &&
            !$filter->gtd &&
            !$filter->abc &&
            !$filter->orderID &&
            !$filter->incomeDocument &&
            !$filter->providerPriceID &&
            $filter->managerID === null
        ) return null;

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'i.incomeID',
                'i.incomeDocumentID',
                'i.incomeOrderID',
                'i.providerPriceID',
                'i.dateofadded',
                'i.zapCardID',
                'i.quantity',
                'i.quantityIn',
                'i.quantityPath',
                'i.quantityUnPack',
                'i.reserve',
                'i.quantityReturn',
                'if(i.dateofzakaz = 0, null, i.dateofzakaz) AS dateofzakaz',
                'if(i.dateofin = 0, null, i.dateofin) AS dateofin',
                'if(i.dateofinplan = 0, null, i.dateofinplan) AS dateofinplan',
                'z.createrID',
                'z.number',
                'c.name AS creater',
                "if (z.zapGroupID, CONCAT(zg.name, ' ', z.name, ' ', z.description), z.name_big) AS detail_name",
                'z.name_big',
                'z.name',
                'z.description',
                'z.zapGroupID',
                'z.nameEng',
                'zg.name AS zapGroup',
                'z.countryID',
                'co.name AS country_name',
                'i.status',
                'ist.name AS status_name',
                "ifNull((SELECT orderID FROM order_goods WHERE incomeID = i.incomeID LIMIT 1), 'Склад') AS orderID",
                "(SELECT orders.userID FROM order_goods INNER JOIN orders ON order_goods.orderID = orders.orderID WHERE incomeID = i.incomeID LIMIT 1) AS orderUserID",
                "i.shop_gtdID",
                "gtd.name AS gtd",
                'i.priceZak',
                'i.priceDost',
                'i.price',
                'm.nick AS manager_nick',
            )
            ->from('income', 'i')
            ->innerJoin('i', 'income_statuses', 'ist', 'i.status = ist.status')
            ->innerJoin('i', 'zapCards', 'z', 'z.zapCardID = i.zapCardID')
            ->leftJoin('z', 'managers', 'm', 'z.managerID = m.managerID')
            ->leftJoin('z', 'zapGroup', 'zg', 'z.zapGroupID = zg.zapGroupID')
            ->leftJoin('z', 'countries', 'co', 'z.countryID = co.countryID')
            ->leftJoin('i', 'shop_gtd', 'gtd', 'i.shop_gtdID = gtd.shop_gtdID')
            ->innerJoin('z', 'creaters', 'c', 'c.createrID = z.createrID');


        if ($filter->managerID !== '' && $filter->managerID !== null) {
            if ($filter->managerID == 0) {
                $qb->andWhere('z.managerID IS NULL');
            } else {
                $qb->andWhere('z.managerID = :managerID');
                $qb->setParameter('managerID', $filter->managerID);
            }
        }

        if ($filter->dateofadded) {
            $dateofadded_from = $filter->dateofadded;
            $dateofadded_till = clone($filter->dateofadded);
            $dateofadded_till = $dateofadded_till->modify('+1 day');

            $qb->andWhere($qb->expr()->gte('i.dateofadded', ':dateofadded_from'));
            $qb->setParameter('dateofadded_from', $dateofadded_from->format('Y-m-d H:i:s'));

            $qb->andWhere($qb->expr()->lt('i.dateofadded', ':dateofadded_till'));
            $qb->setParameter('dateofadded_till', $dateofadded_till->format('Y-m-d H:i:s'));
        }

        if ($filter->dateofzakaz) {
            $dateofzakaz_from = $filter->dateofzakaz;
            $dateofzakaz_till = clone($filter->dateofzakaz);
            $dateofzakaz_till = $dateofzakaz_till->modify('+1 day');

            $qb->andWhere($qb->expr()->gte('i.dateofzakaz', ':dateofzakaz_from'));
            $qb->setParameter('dateofzakaz_from', $dateofzakaz_from->format('Y-m-d H:i:s'));

            $qb->andWhere($qb->expr()->lt('i.dateofzakaz', ':dateofzakaz_till'));
            $qb->setParameter('dateofzakaz_till', $dateofzakaz_till->format('Y-m-d H:i:s'));
        }

        if ($filter->dateofin) {
            $dateofin_from = $filter->dateofin;
            $dateofin_till = clone($filter->dateofin);
            $dateofin_till = $dateofin_till->modify('+1 day');

            $qb->andWhere($qb->expr()->gte('i.dateofin', ':dateofin_from'));
            $qb->setParameter('dateofin_from', $dateofin_from->format('Y-m-d H:i:s'));

            $qb->andWhere($qb->expr()->lt('i.dateofin', ':dateofin_till'));
            $qb->setParameter('dateofin_till', $dateofin_till->format('Y-m-d H:i:s'));
        }

        if ($filter->dateofinplan) {
            $dateofinplan_from = $filter->dateofinplan;
            $dateofinplan_till = clone($filter->dateofinplan);
            $dateofinplan_till = $dateofinplan_till->modify('+1 day');

            $qb->andWhere($qb->expr()->gte('i.dateofinplan', ':dateofinplan_from'));
            $qb->setParameter('dateofinplan_from', $dateofinplan_from->format('Y-m-d H:i:s'));

            $qb->andWhere($qb->expr()->lt('i.dateofinplan', ':dateofinplan_till'));
            $qb->setParameter('dateofinplan_till', $dateofinplan_till->format('Y-m-d H:i:s'));
        }

        if ($filter->abc) {
            if ($filter->abc != 'blank') {
                $qb->andWhere('z.zapCardID IN (SELECT zapCardID FROM zapCard_abc WHERE abc = :abc)');
                $qb->setParameter('abc', $filter->abc);
            } else {
                $qb->andWhere('z.zapCardID NOT IN (SELECT zapCardID FROM zapCard_abc)');
            }
        }

        if ($filter->number) {
            $number = new DetailNumber($filter->number);
            $qb->andWhere($qb->expr()->like('z.number', ':number'));
            $qb->setParameter('number', $number->getValue() . '%');
        }

        if ($filter->gtd) {
            $qb->andWhere($qb->expr()->like('REPLACE(gtd.name, "/", "")', ':gtd'));
            $qb->setParameter('gtd', '%' . mb_strtolower(str_replace('/', '', $filter->gtd)) . '%');
        }

        if ($filter->orderID) {
            if (intval(trim($filter->orderID)) > 0) {
                $qb->innerJoin('i', 'order_goods', 'og', 'i.incomeID = og.incomeID');
//                $qb->andWhere('i.incomeID IN (SELECT incomeID FROM order_goods WHERE orderID = :orderID)');
                $qb->andWhere('og.orderID = :orderID');
                $qb->setParameter('orderID', trim($filter->orderID));
            } elseif (mb_strtolower($filter->orderID) == 'склад') {
                $qb->leftJoin('i', 'order_goods', 'og', 'i.incomeID = og.incomeID');
                $qb->andWhere('og.goodID IS NULL');
//                $qb->andWhere('i.incomeID NOT IN (SELECT incomeID FROM order_goods WHERE incomeID IS NOT NULL)');
            } elseif (mb_strtolower($filter->orderID) == 'заказ') {
                $qb->leftJoin('i', 'order_goods', 'og', 'i.incomeID = og.incomeID');
                $qb->andWhere('og.goodID IS NOT NULL');
//                $qb->andWhere('i.incomeID NOT IN (SELECT incomeID FROM order_goods WHERE incomeID IS NOT NULL)');
            }
        }

        if ($filter->createrID) {
            $qb->andWhere('z.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        if ($filter->status) {
            $qb->andWhere('i.status = :status');
            $qb->setParameter('status', $filter->status);
        }

        if ($filter->incomeID) {
            $qb->andWhere('i.incomeID = :incomeID');
            $qb->setParameter('incomeID', $filter->incomeID);
        }

        if ($filter->isUnpack !== null && $filter->isUnpack !== '') {
            if ($filter->isUnpack)
                $qb->andWhere('i.quantity = i.quantityUnPack');
            else
                $qb->andWhere('i.quantity <> i.quantityUnPack');
        }

        if ($filter->incomeOrder) {
            $qb->andWhere('i.incomeOrderID IN (SELECT incomeOrderID FROM incomeOrders WHERE document_num = :incomeOrder AND incomeOrderID IS NOT NULL)');
            $qb->setParameter('incomeOrder', $filter->incomeOrder);
        }

        if ($filter->incomeDocument) {
            $qb->andWhere('i.incomeDocumentID IN (SELECT incomeDocumentID FROM incomeDocuments WHERE document_num = :incomeDocument AND incomeDocumentID IS NOT NULL)');
            $qb->setParameter('incomeDocument', $filter->incomeDocument);
        }

        if ($filter->isShowQuantityMskNull) {
            $qb->andWhere('i.zapCardID IN (SELECT bb.zapCardID FROM income_sklad aa INNER JOIN income bb ON aa.incomeID = bb.incomeID WHERE aa.zapSkladID = 1 GROUP BY bb.zapCardID HAVING Sum(aa.quantityIn) = 0) AND (IfNull((SELECT quantityMin FROM zapSkladLocation cc WHERE cc.zapSkladID = 1 AND cc.zapCardID = i.zapCardID LIMIT 1), NULL)) > 0');
        }

        if ($filter->isShowQuantitySpbNull) {
            $qb->andWhere('i.zapCardID IN (SELECT bb.zapCardID FROM income_sklad aa INNER JOIN income bb ON aa.incomeID = bb.incomeID WHERE aa.zapSkladID = 5 GROUP BY bb.zapCardID HAVING Sum(aa.quantityIn) = 0) AND (IfNull((SELECT quantityMin FROM zapSkladLocation cc WHERE cc.zapSkladID = 5 AND cc.zapCardID = i.zapCardID LIMIT 1), NULL)) > 0');
        }

        if ($filter->isShowQuantitySrvNull) {
            $qb->andWhere('i.zapCardID IN (SELECT bb.zapCardID FROM income_sklad aa INNER JOIN income bb ON aa.incomeID = bb.incomeID WHERE aa.zapSkladID = 6 GROUP BY bb.zapCardID HAVING Sum(aa.quantityIn) = 0) AND (IfNull((SELECT quantityMin FROM zapSkladLocation cc WHERE cc.zapSkladID = 6 AND cc.zapCardID = i.zapCardID LIMIT 1), NULL)) > 0');
        }

        if ($filter->isShowLessMskMax) {
            $qb->andWhere('(
SELECT IfNull(
	IfNull((SELECT quantityMax FROM zapSkladLocation cc WHERE cc.zapSkladID = 1 AND cc.zapCardID = i.zapCardID AND quantityMax > 0 LIMIT 1), 0)
	 - 
	IfNull(Sum(aa.quantityIn), 0)
	, -1) 
FROM income_sklad aa INNER JOIN income bb ON aa.incomeID = bb.incomeID WHERE bb.zapCardID = i.zapCardID AND aa.zapSkladID = 1 HAVING Sum(aa.quantityIn) > 0
) >= 0');
        }

        if ($filter->isShowLessSpbMax) {
            $qb->andWhere('(
SELECT IfNull(
	IfNull((SELECT quantityMax FROM zapSkladLocation cc WHERE cc.zapSkladID = 5 AND cc.zapCardID = i.zapCardID AND quantityMax > 0 LIMIT 1), 0)
	 - 
	IfNull(Sum(aa.quantityIn), 0)
	, -1) 
FROM income_sklad aa INNER JOIN income bb ON aa.incomeID = bb.incomeID WHERE bb.zapCardID = i.zapCardID AND aa.zapSkladID = 5 HAVING Sum(aa.quantityIn) > 0
) >= 0');
        }

        if ($filter->providerPriceID) {
            $qb->andWhere('i.providerPriceID IN (' . implode(',', $filter->providerPriceID) . ')');
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['number', 'dateofadded', 'creater', 'status_name', 'dateofzakaz', 'dateofin', 'dateofinplan', 'incomeID', 'providerPriceID', 'manager_nick'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param IncomeOrder $incomeOrder
     * @param array $settings
     * @return PaginationInterface|null
     */
    public function allByIncomeOrder(IncomeOrder $incomeOrder, array $settings): ?PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'i.incomeID',
                'i.incomeOrderID',
                'i.providerPriceID',
                'i.zapCardID',
                'i.price',
                'i.quantity',
                'z.createrID',
                'z.number',
                'c.name AS creater',
                "if (z.zapGroupID, CONCAT(zg.name, ' ', z.name, ' ', z.description), z.name_big) AS detail_name",
                'z.name_big',
                'z.name',
                'z.description',
                'z.zapGroupID',
                'z.nameEng',
                'zg.name AS zapGroup',
                'pp.description AS providerPrice'
            )
            ->from('income', 'i')
            ->innerJoin('i', 'zapCards', 'z', 'z.zapCardID = i.zapCardID')
            ->leftJoin('z', 'zapGroup', 'zg', 'z.zapGroupID = zg.zapGroupID')
            ->innerJoin('z', 'creaters', 'c', 'c.createrID = z.createrID')
            ->innerJoin('i', 'providerPrices', 'pp', 'i.providerPriceID = pp.providerPriceID');

        $qb->andWhere('i.incomeOrderID = :incomeOrderID');
        $qb->setParameter('incomeOrderID', $incomeOrder->getId());

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME_ORDERS;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION_ORDERS;

        if (!in_array($sort, ['number', 'dateofadded', 'creater', 'incomeID', 'providerPrice'], true)) {
            $sort = 'number';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, 10000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param string $number
     * @param int $createrID
     * @return array
     * @throws Exception
     */
    public function allByNumberAndCreater(string $number, int $createrID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                's.shopZamenaID',
                's.createrID2',
                's.number2',
                'c2.name AS creater2',
                'c2.tableName',
                'c2.isOriginal'
            )
            ->from('shopZamena', 's')
            ->innerJoin('s', 'creaters', 'c2', 'c2.createrID = s.createrID2')
            ->andWhere('s.number = :number')
            ->setParameter('number', $number)
            ->andWhere('s.createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->orderBy('s.number2')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}