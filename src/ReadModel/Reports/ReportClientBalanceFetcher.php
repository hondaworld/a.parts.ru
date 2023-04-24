<?php


namespace App\ReadModel\Reports;


use App\ReadModel\Reports\Filter\ClientBalance\Filter;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ReportClientBalanceFetcher extends ReportFetcher
{
    public const DEFAULT_SORT_FIELD_NAME = 'userID';
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
     */
    public function all(Filter $filter, array $settings): PaginationInterface
    {

        if ($filter->finance_typeID) {
            $qb = $this->connection->createQueryBuilder()
                ->select(
                    'u.userID',
                    '(SELECT ifNull(SUM(balance), 0)
		                FROM userBalanceHistory
		                WHERE userID = u.userID AND finance_typeID = :finance_typeID
		                ) AS balance',
                    "u.name",
                    "s.name AS shop_pay_type",
                )
                ->from('users', 'u')
                ->leftJoin('u', 'shop_pay_types', 's', 'u.shop_pay_typeID = s.shop_pay_typeID')
                ->andWhere('u.userID <> 1')
                ->andWhere('(SELECT ifNull(SUM(balance), 0)
		                FROM userBalanceHistory
		                WHERE userID = u.userID AND finance_typeID = :finance_typeID
		                ) <> 0')
                ->setParameter('finance_typeID', $filter->finance_typeID)
            ;

        } else {
            $qb = $this->connection->createQueryBuilder()
                ->select(
                    'u.userID',
                    "u.balance",
                    "u.name",
                    "s.name AS shop_pay_type",
                )
                ->from('users', 'u')
                ->leftJoin('u', 'shop_pay_types', 's', 'u.shop_pay_typeID = s.shop_pay_typeID')
                ->andWhere('u.userID <> 1')
                ->andWhere('u.balance <> 0')
            ;
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['userID', 'u.name', 'balance', 'shop_pay_type'], true)) {
            $sort = 'userID';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, 10000000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}