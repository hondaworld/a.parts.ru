<?php


namespace App\ReadModel\Reports;


use App\ReadModel\Card\ZapCardAbcFetcher;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Reports\Filter\NumbersNotSale\Filter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ReportNumbersNotSaleFetcher extends ReportFetcher
{
    private Connection $connection;
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'days';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    private IncomeFetcher $incomeFetcher;
    private ZapCardAbcFetcher $zapCardAbcFetcher;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator, IncomeFetcher $incomeFetcher, ZapCardAbcFetcher $zapCardAbcFetcher)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
        $this->incomeFetcher = $incomeFetcher;
        $this->zapCardAbcFetcher = $zapCardAbcFetcher;
    }

    /**
     * @param Filter $filter
     * @param array $settings
     * @return PaginationInterface
     * @throws Exception
     */
    public function all(Filter $filter, array $settings): PaginationInterface
    {
        $zapCardDays = $this->connection->createQueryBuilder()
            ->select(
                "i.zapCardID",
                "Min(TO_DAYS(NOW()) - TO_DAYS(i.dateofin)) AS days",
            )
            ->from('expenseDocuments', 'ed')
            ->innerJoin('ed', 'order_goods', 'og', 'ed.expenseDocumentID = og.expenseDocumentID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->innerJoin('e', 'income', 'i', 'e.incomeID = i.incomeID')
            ->andWhere('og.incomeID IS NULL')
            ->groupBy('i.zapCardID')
            ->executeQuery()
            ->fetchAllKeyValue();

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ifNull(Sum(i.price * i.quantity), 0) AS sum',
                "i.zapCardID",
                "zc.number",
                "c.createrID",
                "c.name AS creater_name",
                "SUM(i.quantityIn) AS quantity",
                "Min(TO_DAYS(NOW()) - TO_DAYS(i.dateofin)) AS days",
                "null as income_incomeID",
                "null as income_dateofin",
                "null as income_price",
            )
            ->from('income', 'i')
            ->innerJoin('i', 'zapCards', 'zc', 'i.zapCardID = zc.zapCardID')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID')
            ->andWhere('i.quantityIn > 0')
            ->andWhere("zc.number <> '15400PLMA03'")
//            ->andWhere("i.incomeID NOT IN (SELECT incomeID FROM order_goods WHERE incomeID IS NOT NULL)")
            ->groupBy('i.zapCardID');

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['days', 'creater_name', 'number', 'quantity', 'sum', 'income_incomeID', 'income_dateofin', 'income_price'], true)) {
            $sort = 'days';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        $pagination = $this->paginator->paginate($qb, 1, 10000000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);

        $items = $pagination->getItems();
        foreach ($items as $k => &$item) {
            if (isset($zapCardDays[$item['zapCardID']]) && $item['days'] > $zapCardDays[$item['zapCardID']]) {
                $item['days'] = $zapCardDays[$item['zapCardID']];
            }

            $item['abc'] = $this->zapCardAbcFetcher->assocByZapCardID($item['zapCardID']);

            if ($filter->days) {
                if ($filter->days != 'all') {
                    if ($filter->days >= $item['days']) unset($items[$k]);
                } else {
                    if (isset($zapCardDays[$item['zapCardID']])) unset($items[$k]);
                }
            }
            $income = $this->incomeFetcher->getFirstIncomeByZapCardID($item['zapCardID']);
            if ($income) {
                $item['income_dateofin'] = $income['dateofin'];
                $item['income_incomeID'] = $income['incomeID'];
                $item['income_price'] = $income['price'];
            } else {
                unset($items[$k]);
            }

        }

        if (in_array($sort, ['days', 'income_incomeID', 'income_dateofin', 'income_price'], true)) {
            usort($items, function ($a, $b) use ($sort, $direction) {
                if ($direction == 'asc')
                    return $a[$sort] <=> $b[$sort];
                else
                    return $b[$sort] <=> $a[$sort];
            });
        }


        $pagination->setItems($items);

        return $pagination;
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
            ->groupBy('p.providerID, pp.providerPriceID, i.status');

        $qb->andWhere($qb->expr()->in('i.status', array_keys($statuses)));

//        if ($filter->sklad) {
//            if ($filter->sklad == 'zakaz') {
//                $qb->andWhere('(i.incomeID IN (SELECT incomeID FROM order_goods WHERE incomeID IS NOT NULL))');
//            }
//
//            if ($filter->sklad == 'sklad') {
//                $qb->andWhere('(i.incomeID NOT IN (SELECT incomeID FROM order_goods WHERE incomeID IS NOT NULL))');
//            }
//        }

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