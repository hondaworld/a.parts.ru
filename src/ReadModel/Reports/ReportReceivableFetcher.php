<?php


namespace App\ReadModel\Reports;


use App\ReadModel\Reports\Filter\FinanceType\Filter;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ReportReceivableFetcher extends ReportFetcher
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
//        SELECT *, -balance AS balance_summ
//	FROM users a
//	WHERE optID <> 1 AND balance < 0 AND debts_date <> 0

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'u.userID',
                'u.name',
                "-u.balance AS balance",
                "u.debts_date",
            )
            ->from('users', 'u')
            ->andWhere('u.optID <> 1')
            ->andWhere('u.balance < 0')
            ->andWhere('u.debts_date IS NOT NULL')
        ;


        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['name', 'debts_date', 'balance'], true)) {
            $sort = 'name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, 10000000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}