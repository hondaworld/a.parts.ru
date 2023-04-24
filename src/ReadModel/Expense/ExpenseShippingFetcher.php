<?php


namespace App\ReadModel\Expense;


use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Income\Entity\Income\Income;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Expense\Filter\Shipping\Filter;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class ExpenseShippingFetcher
{
    private $connection;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'location';
    public const DEFAULT_SORT_DIRECTION = 'asc';
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

    public function findByZapCards(ZapSklad $zapSklad, array $zapCards): array
    {
        if (!$zapCards) return [];
        $arr = [];

        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'es.expenseID',
                'es.zapCardID',
                'es.quantity',
                'es.quantityPicking',
                'es.goodID',
                'es.incomeID',
                'og.orderID',
                'og.providerPriceID',
                'pp.description AS provider_price_name',
                'es.managerID',
                'm.name AS manager_name',
                'es.status',
            )
            ->from('expense_sklad', 'es')
            ->leftJoin('es', 'order_goods', 'og', 'es.goodID = og.goodID')
            ->leftJoin('og', 'providerPrices', 'pp', 'og.providerPriceID = pp.providerPriceID')
            ->leftJoin('es', 'managers', 'm', 'es.managerID = m.managerID')
            ->andWhere('es.status IN (:statusAdded, :statusScanned)')
            ->setParameter('statusAdded', ExpenseSklad::ADDED)
            ->setParameter('statusScanned', ExpenseSklad::PACKED)
            ->andWhere('es.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSklad->getId())
            ->orderBy('es.incomeID')
        ;

        $stmt->andWhere($stmt->expr()->in('es.zapCardID', $zapCards));

        $items = $stmt->executeQuery()->fetchAllAssociative();
        if ($items) {
            foreach ($items as $item) {
                $arr[$item['zapCardID']][] = $item;
            }
        }

        return $arr;
    }

    /**
     * @param int $zapSkladID
     * @param int $zapSkladID_to
     * @return bool
     * @throws Exception
     */
    public function hasPicking(int $zapSkladID, int $zapSkladID_to): bool
    {
        $query = $this->connection->createQueryBuilder()
            ->select('SUM(es.quantity - es.quantityPicking)')
            ->from('expense_sklad', 'es')
            ->andWhere('es.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSkladID)
            ->andWhere('es.zapSkladID_to = :zapSkladID_to')
            ->setParameter('zapSkladID_to', $zapSkladID_to)
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::PACKED)
        ;

        return $query->executeQuery()->fetchOne() > 0;
    }

    /**
     * @param ZapSklad $zapSklad
     * @param Filter $filter
     * @param array $settings
     * @return PaginationInterface
     */
    public function allShipping(ZapSklad $zapSklad, Filter $filter, array $settings): PaginationInterface
    {

    /*
SELECT a.zapCardID, b.number, b.createrID, c.price, SUM(a.quantity), SUM(a.quantityPicking), a.zapSkladID_to, a.goodID, a.incomeID, a.status, a.managerID
FROM expense_sklad a
INNER JOIN zapCards b ON a.zapCardID = b.zapCardID
INNER JOIN income c ON a.incomeID = c.incomeID
WHERE a.zapSkladID = '".AddSlashes($zapSkladID)."' AND a.status IN (0,3) $where
GROUP BY a.zapCardID, a.zapSkladID_to
ORDER BY b.number
    */
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'es.zapCardID',
                'zc.number',
                'zc.createrID',
                'c.name AS creater_name',
                'SUM(es.quantity) AS quantity',
                'SUM(es.quantityPicking) AS quantityPicking',
                'es.zapSkladID_to',
                'zst.name_short AS zapSklad_to',
                "'' AS location",
            )
            ->from('expense_sklad', 'es')
            ->innerJoin('es', 'zapCards', 'zc', 'es.zapCardID = zc.zapCardID')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID')
            ->innerJoin('es', 'zapSklad', 'zst', 'es.zapSkladID_to = zst.zapSkladID')
            ->andWhere('es.status IN (:statusAdded, :statusScanned)')
            ->setParameter('statusAdded', ExpenseSklad::ADDED)
            ->setParameter('statusScanned', ExpenseSklad::PACKED)
            ->andWhere('es.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSklad->getId())
            ->groupBy('es.zapCardID, es.zapSkladID_to')
        ;

        if ($filter->managerID) {
            $qb->andWhere('es.managerID = :managerID');
            $qb->setParameter('managerID', intval($filter->managerID));
        }

        if ($filter->createrID) {
            $qb->andWhere('zc.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        if ($filter->number) {
            $qb->andWhere($qb->expr()->like('zc.number', ':number'));
            $qb->setParameter('number', '%' . mb_strtolower($filter->number) . '%');
        }

        if ($filter->zapSkladID_to) {
            $qb->andWhere('es.zapSkladID_to = :zapSkladID_to');
            $qb->setParameter('zapSkladID_to', $filter->zapSkladID_to);
        }

        if ($filter->isPacked) {
            $qb->andWhere('es.status = :status');
            $qb->setParameter('status', ExpenseSklad::PACKED);
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = 1000000;

        if (!in_array($sort, ['location', 'number', 'creater_name'], true)) {
            $sort = 'location';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param ZapSklad $zapSklad
     * @return array
     * @throws Exception
     */
    public function allShippingAndPacked(ZapSklad $zapSklad): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'es.zapCardID',
                'zc.number',
                'zc.createrID',
                'c.name AS creater_name',
                'SUM(es.quantity) AS quantity',
                'SUM(es.quantityPicking) AS quantityPicking',
                'es.zapSkladID_to',
                'zst.name_short AS zapSklad_to',
                "'' AS location",
            )
            ->from('expense_sklad', 'es')
            ->innerJoin('es', 'zapCards', 'zc', 'es.zapCardID = zc.zapCardID')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID')
            ->innerJoin('es', 'zapSklad', 'zst', 'es.zapSkladID_to = zst.zapSkladID')
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::PACKED)
            ->andWhere('es.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSklad->getId())
            ->groupBy('es.zapCardID, es.zapSkladID_to')
            ->orderBy('zc.number')
        ;

        return $qb->executeQuery()->fetchAllAssociative();
    }

}