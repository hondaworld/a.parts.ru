<?php


namespace App\ReadModel\Reports;


use App\ReadModel\Reports\Filter\Turnover\Filter;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ReportTurnoverFetcher extends ReportFetcher
{
    private string $minDate = '';
    private string $maxDate = '';

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
     * @param Filter $filter
     * @param array $settings
     * @return PaginationInterface
     * @throws Exception
     */
    public function all(Filter $filter, array $settings): PaginationInterface
    {
//        SELECT SUM((ROUND(a.price-a.price*a.discount/100)) * c.quantity) AS summ, SUM((ROUND(a.price-a.price*a.discount/100) - d.price) * c.quantity) AS summ1, e.name, e.userID, balanceLimit
//	FROM order_goods a
//	INNER JOIN orders b ON a.orderID = b.orderID
//	INNER JOIN expense c ON a.goodID = c.goodID
//	INNER JOIN income d ON c.incomeID = d.incomeID
//	INNER JOIN users e ON b.userID = e.userID
//	INNER JOIN expenseDocuments h ON a.expenseDocumentID = h.expenseDocumentID
//	WHERE a.expenseDocumentID <> 0 AND e.optID <> 1 $where
//	GROUP BY e.userID

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'u.userID',
                'u.name',
                "u.balanceLimit",
                " SUM((ROUND(og.price - og.price * og.discount / 100)) * e.quantity) AS income",
                " SUM((ROUND(og.price - og.price * og.discount / 100) - i.price) * e.quantity) AS profit",
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->innerJoin('e', 'income', 'i', 'e.incomeID = i.incomeID')
            ->innerJoin('o', 'users', 'u', 'o.userID = u.userID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'og.expenseDocumentID = ed.expenseDocumentID')
            ->andWhere('u.optID <> 1')
            ->andWhere('og.expenseDocumentID IS NOT NULL')
            ->groupBy('u.userID')
        ;

        if ($filter->dateofreport) {
            if ($filter->dateofreport['date_from']) {
                $dateFrom = new DateTime($filter->dateofreport['date_from']);
                $qb->andWhere($qb->expr()->gte('ed.dateofadded', ':date_from'));
                $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
            }

            if ($filter->dateofreport['date_till']) {
                $dateTill = new DateTime($filter->dateofreport['date_till']);
                $qb->andWhere($qb->expr()->lte('ed.dateofadded', ':date_till'));
                $qb->setParameter('date_till', $dateTill->format('Y-m-d 23:59:59'));
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['name', 'income', 'profit', 'balanceLimit'], true)) {
            $sort = 'name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, 10000000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}