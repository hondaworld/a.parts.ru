<?php


namespace App\ReadModel\Reports;


use App\ReadModel\Reports\Filter\IncomeGood\Filter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ReportIncomeGoodFetcher extends ReportFetcher
{
    private Connection $connection;
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'name';
    public const DEFAULT_SORT_DIRECTION = 'asc';

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
    }

    /**
     * @param Filter $filter
     * @param array $statuses
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(Filter $filter, array $statuses, array $settings): PaginationInterface
    {
//        SELECT ifnull(Sum(a.price * a.quantity), 0) AS summ, c.providerID, c.name AS provider, c.isIncomeOrder
//	FROM income a
//	INNER JOIN providerPrices b ON a.providerPriceID = b.providerPriceID
//	INNER JOIN providers c ON b.providerID = c.providerID
//	WHERE a.status IN (1,2,6,7,9) $where
//	GROUP BY c .providerID

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ifNull(Sum(i.price * i.quantity), 0) AS sum',
                "p.providerID",
                "p.name",
                "p.isIncomeOrder",
                "if (LOCATE('SPB', p.name) = 0, 0, 1) AS type"
            )
            ->from('income', 'i')
            ->innerJoin('i', 'providerPrices', 'pp', 'i.providerPriceID = pp.providerPriceID')
            ->innerJoin('pp', 'providers', 'p', 'pp.providerID = p.providerID')
            ->groupBy('p.providerID')
        ;

        $qb->andWhere($qb->expr()->in('i.status', array_keys($statuses)));

        if ($filter->sklad) {
            if ($filter->sklad == 'zakaz') {
//                $qb->andWhere('(i.incomeID IN (SELECT incomeID FROM order_goods WHERE incomeID IS NOT NULL))');
                $qb->innerJoin('i', 'order_goods', 'og', 'i.incomeID = og.incomeID');
            }

            if ($filter->sklad == 'sklad') {
//                $qb->andWhere('(i.incomeID NOT IN (SELECT incomeID FROM order_goods WHERE incomeID IS NOT NULL))');
                $qb->leftJoin('i', 'order_goods', 'og', 'i.incomeID = og.incomeID');
                $qb->andWhere('og.goodID IS NULL');
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['name', 'sum'], true)) {
            $sort = 'name';
        }

        if ($sort == 'name') {
            $qb->addOrderBy('type', $direction === 'desc' ? 'desc' : 'asc');
        }

        $qb->addOrderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, 10000000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param Filter $filter
     * @param array $statuses
     * @return array|null
     * @throws Exception
     */
    public function allWithStatuses(Filter $filter, array $statuses): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ifNull(Sum(i.price * i.quantity), 0) AS sum',
                "p.providerID",
                "pp.providerPriceID",
                "i.status",
            )
            ->from('income', 'i')
            ->innerJoin('i', 'providerPrices', 'pp', 'i.providerPriceID = pp.providerPriceID')
            ->innerJoin('pp', 'providers', 'p', 'pp.providerID = p.providerID')
            ->groupBy('p.providerID, pp.providerPriceID, i.status')
        ;

        $qb->andWhere($qb->expr()->in('i.status', array_keys($statuses)));

        if ($filter->sklad) {
            if ($filter->sklad == 'zakaz') {
//                $qb->andWhere('(i.incomeID IN (SELECT incomeID FROM order_goods WHERE incomeID IS NOT NULL))');
                $qb->innerJoin('i', 'order_goods', 'og', 'i.incomeID = og.incomeID');
            }

            if ($filter->sklad == 'sklad') {
//                $qb->andWhere('(i.incomeID NOT IN (SELECT incomeID FROM order_goods WHERE incomeID IS NOT NULL))');
                $qb->leftJoin('i', 'order_goods', 'og', 'i.incomeID = og.incomeID');
                $qb->andWhere('og.goodID IS NULL');
            }
        }

        $arr = $qb->executeQuery()->fetchAllAssociative();

        return $this->generateArray($arr);
    }

    /**
     * @param array $arr
     * @return array
     */
    private function generateArray(array $arr): array
    {
        $result = [];
        foreach ($arr as $item) {
            if (!isset($result[$item['providerID']])) {
                $result[$item['providerID']] = [
                    'statuses' => [],
                    'providerPrices' => []
                ];
            }
            $result[$item['providerID']]['statuses'][$item['status']] = ($result[$item['providerID']]['statuses'][$item['status']] ?? 0) + $item['sum'];
            if (!in_array($item['providerPriceID'], $result[$item['providerID']]['providerPrices'])) $result[$item['providerID']]['providerPrices'][] = $item['providerPriceID'];
        }
        return $result;
    }
}