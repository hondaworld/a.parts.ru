<?php


namespace App\ReadModel\Expense;


use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Income\Entity\Income\Income;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Expense\Filter\Income\Filter;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class ExpenseIncomeFetcher
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
                'es.expense_skladDocumentID',
                'es.zapCardID',
                'es.quantity',
                'es.quantityIncome',
                'es.goodID',
                'es.incomeID',
                'og.orderID',
                'u.name AS user_name',
                'og.providerPriceID',
                'pp.description AS provider_price_name',
                'es.status',
                'esd.document_num',
                'esd.dateofadded AS document_date',
            )
            ->from('expense_sklad', 'es')
            ->leftJoin('es', 'order_goods', 'og', 'es.goodID = og.goodID')
            ->leftJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->leftJoin('o', 'users', 'u', 'o.userID = u.userID')
            ->leftJoin('og', 'providerPrices', 'pp', 'og.providerPriceID = pp.providerPriceID')
            ->leftJoin('es', 'expense_skladDocuments', 'esd', 'es.expense_skladDocumentID = esd.expense_skladDocumentID')
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::SENT)
            ->andWhere('es.zapSkladID_to = :zapSkladID_to')
            ->setParameter('zapSkladID_to', $zapSklad->getId())
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
     * @param int $zapCardID
     * @param int $zapSkladID
     * @param int $zapSkladID_to
     * @return bool
     * @throws Exception
     */
    public function hasIncomeByZapCard(int $zapCardID, int $zapSkladID, int $zapSkladID_to): bool
    {
        $query = $this->connection->createQueryBuilder()
            ->select('SUM(es.quantity - es.quantityIncome)')
            ->from('expense_sklad', 'es')
            ->andWhere('es.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere('es.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSkladID)
            ->andWhere('es.zapSkladID_to = :zapSkladID_to')
            ->setParameter('zapSkladID_to', $zapSkladID_to)
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::SENT)
        ;

        return $query->executeQuery()->fetchOne() > 0;
    }

    /**
     * @param int $zapSkladID
     * @param int $zapSkladID_to
     * @return bool
     * @throws Exception
     */
    public function hasIncome(int $zapSkladID, int $zapSkladID_to): bool
    {
        $query = $this->connection->createQueryBuilder()
            ->select('SUM(es.quantity - es.quantityIncome)')
            ->from('expense_sklad', 'es')
            ->andWhere('es.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSkladID)
            ->andWhere('es.zapSkladID_to = :zapSkladID_to')
            ->setParameter('zapSkladID_to', $zapSkladID_to)
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::SENT)
        ;

        return $query->executeQuery()->fetchOne() > 0;
    }

    /**
     * @param ZapSklad $zapSklad
     * @param Filter $filter
     * @param array $settings
     * @return PaginationInterface
     */
    public function allIncome(ZapSklad $zapSklad, Filter $filter, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'es.zapCardID',
                'zc.number',
                'zc.createrID',
                'c.name AS creater_name',
                'SUM(es.quantity) AS quantity',
                'SUM(es.quantityIncome) AS quantityIncome',
                'es.zapSkladID',
                'zst.name_short AS zapSklad',
                "'' AS location",
            )
            ->from('expense_sklad', 'es')
            ->innerJoin('es', 'zapCards', 'zc', 'es.zapCardID = zc.zapCardID')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID')
            ->innerJoin('es', 'zapSklad', 'zst', 'es.zapSkladID = zst.zapSkladID')
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::SENT)
            ->andWhere('es.zapSkladID_to = :zapSkladID_to')
            ->setParameter('zapSkladID_to', $zapSklad->getId())
            ->groupBy('es.zapCardID, es.zapSkladID')
        ;

        if ($filter->createrID) {
            $qb->andWhere('zc.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        if ($filter->number) {
            $qb->andWhere($qb->expr()->like('zc.number', ':number'));
            $qb->setParameter('number', '%' . mb_strtolower($filter->number) . '%');
        }

        if ($filter->zapSkladID) {
            $qb->andWhere('es.zapSkladID = :zapSkladID');
            $qb->setParameter('zapSkladID', $filter->zapSkladID);
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
    public function allIncomeAndNotScanned(ZapSklad $zapSklad): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'es.zapCardID',
                'zc.number',
                'zc.createrID',
                'c.name AS creater_name',
                'SUM(es.quantity) AS quantity',
                'SUM(es.quantityIncome) AS quantityIncome',
                'es.zapSkladID',
                'zst.name_short AS zapSklad',
                "'' AS location",
            )
            ->from('expense_sklad', 'es')
            ->innerJoin('es', 'zapCards', 'zc', 'es.zapCardID = zc.zapCardID')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID')
            ->innerJoin('es', 'zapSklad', 'zst', 'es.zapSkladID = zst.zapSkladID')
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::SENT)
            ->andWhere('es.zapSkladID_to = :zapSkladID_to')
            ->setParameter('zapSkladID_to', $zapSklad->getId())
            ->groupBy('es.zapCardID, es.zapSkladID')
            ->orderBy('zc.number')
        ;

        return $qb->executeQuery()->fetchAllAssociative();
    }

}