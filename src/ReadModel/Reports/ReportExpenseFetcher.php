<?php


namespace App\ReadModel\Reports;


use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ReportExpenseFetcher extends ReportFetcher
{
    public const DEFAULT_SORT_FIELD_NAME = 'name';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    private Connection $connection;
    private PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
    }

    /**
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(array $settings): PaginationInterface
    {
//        SELECT SUM((ROUND(a.price-a.price*a.discount/100)) * c.quantity) AS summ, e.name, e.userID
//	FROM order_goods a
//	INNER JOIN orders b ON a.orderID = b.orderID
//	INNER JOIN expense c ON a.goodID = c.goodID
//	INNER JOIN users e ON b.userID = e.userID
//	WHERE a.expenseDocumentID = 0 AND e.optID <> 1 $where
//	GROUP BY e.userID

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'u.userID',
                'u.name',
                "SUM((ROUND(og.price - og.price * og.discount/100)) * e.quantity) AS sum",
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->innerJoin('o', 'users', 'u', 'o.userID = u.userID')
            ->andWhere('u.optID <> 1')
            ->andWhere('og.expenseDocumentID IS NULL')
            ->groupBy('u.userID')
        ;


        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['name', 'sum'], true)) {
            $sort = 'name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, 10000000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}