<?php


namespace App\ReadModel\Expense;


use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Income\Entity\Income\Income;
use App\ReadModel\Expense\Filter\ExpenseSklad\Filter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class ExpenseSkladFetcher
{
    private Connection $connection;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 50;
    private PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ExpenseSklad::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ExpenseSklad
    {
        return $this->repository->get($id);
    }

    /**
     * @param array $arZapCards
     * @return array
     * @throws Exception
     */
    public function findQuantityOrderedFromSkladsByZapCards(array $arZapCards): array
    {
        $arr = [];

        if (!$arZapCards) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "zapCardID",
                "zapSkladID",
                "ifnull(Sum(quantity), 0) AS quantity",
            )
            ->from('expense_sklad', 'a')
            ->groupBy("zapCardID, zapSkladID");

        $qb->andWhere($qb->expr()->in('status', [ExpenseSklad::ADDED, ExpenseSklad::SENT, ExpenseSklad::PACKED]));
        $qb->andWhere($qb->expr()->in('zapCardID', $arZapCards));

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $arr[$item['zapCardID']][$item['zapSkladID']] = $item['quantity'];
        }

        return $arr;
    }

    /**
     * @param array $arZapCards
     * @return array
     * @throws Exception
     */
    public function findQuantityOrderedToSkladsByZapCards(array $arZapCards): array
    {
        $arr = [];

        if (!$arZapCards) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "zapCardID",
                "zapSkladID_to",
                "ifnull(Sum(quantity), 0) AS quantity",
            )
            ->from('expense_sklad', 'a')
            ->groupBy("zapCardID, zapSkladID_to");

        $qb->andWhere($qb->expr()->in('status', [ExpenseSklad::ADDED, ExpenseSklad::SENT, ExpenseSklad::PACKED]));
        $qb->andWhere($qb->expr()->in('zapCardID', $arZapCards));

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $arr[$item['zapCardID']][$item['zapSkladID_to']] = $item['quantity'];
        }

        return $arr;
    }

    /**
     * @param Income $income
     * @return array
     * @throws Exception
     */
    public function findByIncomeWithDocument(Income $income): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'esd.expense_skladDocumentID',
                'esd.doc_typeID',
                'esd.document_num',
                'esd.dateofadded',
                'es.zapSkladID_to AS zapSkladID',
                'esd.firmID',
                'es.quantity'
            )
            ->from('expense_sklad', 'es')
            ->innerJoin('es', 'expense_skladDocuments', 'esd', 'esd.expense_skladDocumentID = es.expense_skladDocumentID')
            ->where('es.incomeID = :incomeID')
            ->setParameter('incomeID', $income->getId());

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param array $expense_skladDocuments
     * @return array
     * @throws Exception
     */
    public function findByDocuments(array $expense_skladDocuments): array
    {
        if (!$expense_skladDocuments) return [];
        $arr = [];

        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'es.expense_skladDocumentID',
                'es.expenseID',
                'es.dateofadded',
                'es.zapCardID',
                'es.quantity',
                'es.goodID',
                'es.incomeID',
                'og.orderID',
                'og.providerPriceID'
            )
            ->from('expense_sklad', 'es')
            ->leftJoin('es', 'order_goods', 'og', 'es.goodID = og.goodID')
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::INCOME)
            ->orderBy('es.incomeID')
        ;

        $stmt->andWhere($stmt->expr()->in('es.expense_skladDocumentID', $expense_skladDocuments));

        $items = $stmt->executeQuery()->fetchAllAssociative();
        if ($items) {
            foreach ($items as $item) {
                $arr[$item['expense_skladDocumentID']][$item['zapCardID']][] = $item;
            }
        }

        return $arr;
    }

    /**
     * Получение складов и статуса детали, если она находится в перемещении
     *
     * @param int $goodID
     * @return array
     * @throws Exception
     */
    public function getNotIncomeByOrderGood(int $goodID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'zsf.name_short AS sklad_name_from',
                'zst.name_short AS sklad_name_to',
                'es.status'
            )
            ->from('expense_sklad', 'es')
            ->innerJoin('es', 'zapSklad', 'zsf', 'es.zapSkladID = zsf.zapSkladID')
            ->innerJoin('es', 'zapSklad', 'zst', 'es.zapSkladID_to = zst.zapSkladID')
            ->where('es.goodID = :goodID')
            ->setParameter('goodID', $goodID)
        ;
        $qb->andWhere($qb->expr()->in('es.status', [ExpenseSklad::ADDED, ExpenseSklad::SENT, ExpenseSklad::PACKED]));

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function allGroupByDocument(Filter $filter, int $page, array $settings): PaginationInterface
    {

    /*
        SELECT a.expenseID, a.expense_skladDocumentID, a.dateofadded, a.zapCardID, b.number, b.createrID, d.name AS creater, c.price, SUM(a.quantity), a.zapSkladID, a.zapSkladID_to, a.goodID, a.incomeID, a.status
        FROM expense_sklad a
        INNER JOIN zapCards b ON a.zapCardID = b.zapCardID
        INNER JOIN creaters d ON b.createrID = d.createrID
        INNER JOIN income c ON a.incomeID = c.incomeID
        WHERE a.status = 2 $where
        GROUP BY a.zapCardID, a.expense_skladDocumentID
    */
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'es.expense_skladDocumentID',
                'esd.document_num',
                'esd.dateofadded',
                'es.zapCardID',
                'zc.number',
                'zc.createrID',
                'c.name AS creater_name',
                'esd.zapSkladID',
                'esd.zapSkladID_to',
                'zs.name_short AS zapSklad',
                'zst.name_short AS zapSklad_to',
            )
            ->from('expense_sklad', 'es')
            ->innerJoin('es', 'expense_skladDocuments', 'esd', 'es.expense_skladDocumentID = esd.expense_skladDocumentID')
            ->innerJoin('es', 'zapCards', 'zc', 'es.zapCardID = zc.zapCardID')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID')
            ->innerJoin('esd', 'zapSklad', 'zs', 'esd.zapSkladID = zs.zapSkladID')
            ->innerJoin('esd', 'zapSklad', 'zst', 'esd.zapSkladID_to = zst.zapSkladID')
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::INCOME)
            ->groupBy('es.expense_skladDocumentID, es.zapCardID')
        ;

        if ($filter->document_num) {
            $qb->andWhere('esd.document_num = :document_num');
            $qb->setParameter('document_num', intval($filter->document_num));
        }

        if ($filter->createrID) {
            $qb->andWhere('zc.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        if ($filter->number) {
            $qb->andWhere($qb->expr()->like('zc.number', ':number'));
            $qb->setParameter('number', '%' . mb_strtolower($filter->number) . '%');
        }

        if ($filter->zapSkladID) {
            $qb->andWhere('esd.zapSkladID = :zapSkladID');
            $qb->setParameter('zapSkladID', $filter->zapSkladID);
        }

        if ($filter->zapSkladID_to) {
            $qb->andWhere('esd.zapSkladID_to = :zapSkladID_to');
            $qb->setParameter('zapSkladID_to', $filter->zapSkladID_to);
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['document_num', 'dateofadded', 'creater_name', 'number'], true)) {
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

}