<?php


namespace App\ReadModel\Reports;


use App\ReadModel\Reports\Filter;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ReportManagerMoneyFetcher extends ReportFetcher
{
    private Connection $connection;
    private PaginatorInterface $paginator;

    private string $minDate = '';
    private string $maxDate = '';

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 50;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
    }

    /**
     * @param Filter\ManagerMoney\Filter $filter
     * @return array|null
     * @throws Exception
     */
    public function dates(Filter\ManagerMoney\Filter $filter): array
    {
        $date_from = new DateTime($this->minDate);
        $date_till = new DateTime($this->maxDate);

        if ($filter->dateofreport) {
            if ($filter->dateofreport['date_from']) {
                $date_from = new DateTime($filter->dateofreport['date_from']);
            }
            if ($filter->dateofreport['date_till']) {
                $date_till = new DateTime($filter->dateofreport['date_till']);
            }
        }

        return $this->getDates($date_from, $date_till, $filter->period);
    }

    /**
     * @param Filter\ManagerMoney\Filter $filter
     * @return array|null
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function all(Filter\ManagerMoney\Filter $filter): ?array
    {
//        SELECT ifnull(Sum(balance), 0) AS summ, c.name AS finance_type
//		FROM userBalanceHistory a
//		INNER JOIN finance_types c ON a.finance_typeID = c.finance_typeID
//		WHERE a.expenseDocumentID = 0 $where
//		GROUP BY a.finance_typeID

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'b.finance_typeID',
                'b.managerID',
                "b.balance",
                "b.dateofadded",
            )
            ->from('userBalanceHistory', 'b')
            ->andWhere('b.expenseDocumentID IS NULL')
            ->orderBy('b.dateofadded');

        if ($filter->dateofreport) {
            if ($filter->dateofreport['date_from']) {
                $dateFrom = new DateTime($filter->dateofreport['date_from']);
                $qb->andWhere($qb->expr()->gte('b.dateofadded', ':date_from'));
                $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
            }

            if ($filter->dateofreport['date_till']) {
                $dateTill = new DateTime($filter->dateofreport['date_till']);
                $qb->andWhere($qb->expr()->lte('b.dateofadded', ':date_till'));
                $qb->setParameter('date_till', $dateTill->format('Y-m-d 23:59:59'));
            }
        }

        $arr = $qb->executeQuery()->fetchAllAssociative();

        $this->minDate = $arr[0]['dateofadded'];
        $this->maxDate = $arr[count($arr) - 1]['dateofadded'];


        return $this->generateArray($arr, $filter->period);
    }

    /**
     * @param Filter\ManagerMoneyView\Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     * @throws Exception
     */
    public function view(Filter\ManagerMoneyView\Filter $filter, int $page, array $settings): ?PaginationInterface
    {
        if (!$filter->finance_typeID || !$filter->managerID) return null;

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;
        return $this->paginator->paginate($this->queryView($filter, $settings), $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param Filter\ManagerMoneyView\Filter $filter
     * @param array $settings
     * @return array
     * @throws Exception
     */
    public function viewAll(Filter\ManagerMoneyView\Filter $filter, array $settings): ?array
    {
        if (!$filter->finance_typeID || !$filter->managerID) return null;

        return $this->queryView($filter, $settings)->executeQuery()->fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    private function queryView(Filter\ManagerMoneyView\Filter $filter, array $settings): QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'b.finance_typeID',
                'b.managerID',
                'b.userID',
                "b.balance",
                "b.dateofadded",
                "u.name AS user_name",
            )
            ->from('userBalanceHistory', 'b')
            ->innerJoin('b', 'users', 'u', 'b.userID = u.userID')
            ->andWhere('b.expenseDocumentID IS NULL');

        $qb->andWhere('b.finance_typeID = :finance_typeID');
        $qb->setParameter('finance_typeID', $filter->finance_typeID);

        $qb->andWhere('b.managerID = :managerID');
        $qb->setParameter('managerID', $filter->managerID);

        if ($filter->dateofreport) {
            if ($filter->dateofreport['date_from']) {
                $dateFrom = new DateTime($filter->dateofreport['date_from']);
                $qb->andWhere($qb->expr()->gte('b.dateofadded', ':date_from'));
                $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
            }

            if ($filter->dateofreport['date_till']) {
                $dateTill = new DateTime($filter->dateofreport['date_till']);
                $qb->andWhere($qb->expr()->lte('b.dateofadded', ':date_till'));
                $qb->setParameter('date_till', $dateTill->format('Y-m-d 23:59:59'));
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['dateofadded', 'user_name', 'balance'], true)) {
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $qb;
    }

    /**
     * @param array $arr
     * @param string $period
     * @return array
     * @throws Exception
     */
    private function generateArray(array $arr, string $period): array
    {
        $result = [];

        foreach ($arr as $item) {
            $date = $this->getDateByPeriod($item['dateofadded'], $period);
            if (isset($result[$item['managerID']])) {
                $result[$item['managerID']]['value'] += $item['balance'];
            } else {
                $result[$item['managerID']] = [
                    'value' => $item['balance'],
                    'financeTypes' => [],
                    'date' => []
                ];
            }

            $result[$item['managerID']]['financeTypes'][$item['finance_typeID']] = ($result[$item['managerID']]['financeTypes'][$item['finance_typeID']] ?? 0) + $item['balance'];
            $result[$item['managerID']]['date'][$date] = ($result[$item['managerID']]['date'][$date] ?? 0) + $item['balance'];
        }
        return $result;
    }

    /**
     * @param string $dateofadded
     * @param string $period
     * @return string
     * @throws Exception
     */
    private function getDateByPeriod(string $dateofadded, string $period): string
    {
        $date = (new DateTime($dateofadded));
        switch ($period) {
            case 'year':
                return $date->format('Y');
            case 'month':
                return $date->format('Y-m');
            case 'day':
                return $date->format('Y-m-d');
        }
        return $date->format('Y-m-d');
    }
}