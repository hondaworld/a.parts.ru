<?php


namespace App\ReadModel\Reports;


use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\Opt\Opt;
use App\ReadModel\Reports\Filter\ClientProfit\Filter;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ReportClientProfitFetcher extends ReportFetcher
{
    private Connection $connection;
    private array $arr = [];
    private array $prevArr = [];

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    /**
     * @param Filter $filter
     * @return array|null
     * @throws Exception
     */
    public function prevDates(Filter $filter): ?array
    {
        if (!$filter->dateofreport || !$filter->dateofreport['date_from'] || !$filter->dateofreport['date_till'] || !$filter->dateofprev) return null;

        $dateFrom = new DateTime($filter->dateofreport['date_from']);
        $dateTill = new DateTime($filter->dateofreport['date_till']);

        return $this->getPrevDates($dateFrom, $dateTill, $filter->dateofprev);
    }

    /**
     * @param Filter $filter
     * @return array|null
     * @throws Exception
     */
    public function dates(Filter $filter): ?array
    {
        if (!$filter->dateofreport || !$filter->dateofreport['date_from'] || !$filter->dateofreport['date_till']) return null;
        $date_from = new DateTime($filter->dateofreport['date_from']);
        $date_till = new DateTime($filter->dateofreport['date_till']);
        return $this->getDates($date_from, $date_till);
    }

    public function users(array $opts): array
    {
        $arr = $this->arr;
        $result = [];
        foreach ($opts as $optID => $optName) {
            $result[$optID] = [];
        }
        foreach ($arr as $item) {
            $profit = ($item['priceGood'] - $item['priceZak']) * $item['quantity'];
            $income = $item['priceGood'] * $item['quantity'];

            if (isset($result[$item['optID']][$item['userID']])) {
                $result[$item['optID']][$item['userID']]['profit'] += $profit;
                $result[$item['optID']][$item['userID']]['income'] += $income;
            } else {
                $result[$item['optID']][$item['userID']] = [
                    'userID' => $item['userID'],
                    'name' => $item['user_name'],
                    'profit' => $profit,
                    'income' => $income,
                ];
            }
        }

        foreach ($result as &$item) {
            uasort($item, function($a, $b) {
                return $b['profit'] <=> $a['profit'];
            });
        }
        return $result;
    }

    /**
     * @param array $opts
     * @param Filter $filter
     * @return array|null
     * @throws Exception
     */
    public function all(array $opts, Filter $filter): ?array
    {
        if (!$filter->dateofreport || !$filter->dateofreport['date_from'] || !$filter->dateofreport['date_till']) return null;

        $dateFrom = new DateTime($filter->dateofreport['date_from']);
        $dateTill = new DateTime($filter->dateofreport['date_till']);

        $sklad = $this->sklad($dateFrom, $dateTill);
        $zakaz = $this->zakaz($dateFrom, $dateTill);

        $this->arr = array_merge($sklad, $zakaz);

        return $this->generateArray($opts, $this->arr);
    }

    /**
     * @param array $opts
     * @param Filter $filter
     * @return array|null
     * @throws Exception
     */
    public function prev(array $opts, Filter $filter): ?array
    {
        if (!$filter->dateofreport || !$filter->dateofreport['date_from'] || !$filter->dateofreport['date_till'] || !$filter->dateofprev) return null;

        $dateFrom = new DateTime($filter->dateofreport['date_from']);
        $dateTill = new DateTime($filter->dateofreport['date_till']);

        $days = $dateFrom->diff($dateTill);

        $datePrev = clone($filter->dateofprev);
        $datePrevTill = clone($datePrev);
        $datePrevTill->modify('+' . $days->days . ' day');

        $sklad = $this->sklad($datePrev, $datePrevTill);
        $zakaz = $this->zakaz($datePrev, $datePrevTill);

        $this->prevArr = array_merge($sklad, $zakaz);

        return $this->generateArray($opts, $this->prevArr);
    }

    /**
     * @param array $opts
     * @param array $arr
     * @return array
     * @throws Exception
     */
    private function generateArray(array $opts, array $arr): array
    {
        $arrBlank = [
            'profit' => ['value' => 0, 'date' => []],
            'income' => ['value' => 0, 'date' => []],
            'mskProfit' => ['value' => 0, 'date' => []],
            'mskIncome' => ['value' => 0, 'date' => []],
            'mskServiceProfit' => ['value' => 0, 'date' => []],
            'mskServiceIncome' => ['value' => 0, 'date' => []],
            'spbProfit' => ['value' => 0, 'date' => []],
            'spbIncome' => ['value' => 0, 'date' => []],
            'spbServiceProfit' => ['value' => 0, 'date' => []],
            'spbServiceIncome' => ['value' => 0, 'date' => []],
            'serviceProfit' => ['value' => 0, 'date' => []],
            'serviceIncome' => ['value' => 0, 'date' => []],
        ];

        $result = [];
        foreach ($opts as $optID => $optName) {
            $result[$optID] = $arrBlank;
        }
        $result['opt'] = $arrBlank;
        foreach ($arr as $item) {
            $result = $this->generateResultItem($result, $item, $item['optID']);
            if ($item['optID'] != Opt::DEFAULT_OPT_ID) {
                $result = $this->generateResultItem($result, $item, 'opt');
            }
        }
        return $result;
    }

    private function generateResultItem(array $result, array $item, string $optID): array
    {
        $date = (new DateTime($item['dateofadded']))->format('Y-m-d');
        $profit = ($item['priceGood'] - $item['priceZak']) * $item['quantity'];
        $income = $item['priceGood'] * $item['quantity'];

        $result[$optID]['profit']['value'] += $profit;
        $result[$optID]['profit']['date'][$date] = ($result[$optID]['profit']['date'][$date] ?? 0) + $profit;
        $result[$optID]['income']['value'] += $income;
        $result[$optID]['income']['date'][$date] = ($result[$optID]['income']['date'][$date] ?? 0) + $income;

        if ($item['isService'] == 1) {
            $result[$optID]['serviceProfit']['value'] += $profit;
            $result[$optID]['serviceProfit']['date'][$date] = ($result[$optID]['serviceProfit']['date'][$date] ?? 0) + $profit;
            $result[$optID]['serviceIncome']['value'] += $income;
            $result[$optID]['serviceIncome']['date'][$date] = ($result[$optID]['serviceIncome']['date'][$date] ?? 0) + $income;
        }

        if ($item['zapSkladID'] == ZapSklad::MSK) {
            $result[$optID]['mskProfit']['value'] += $profit;
            $result[$optID]['mskProfit']['date'][$date] = ($result[$optID]['mskProfit']['date'][$date] ?? 0) + $profit;
            $result[$optID]['mskIncome']['value'] += $income;
            $result[$optID]['mskIncome']['date'][$date] = ($result[$optID]['mskIncome']['date'][$date] ?? 0) + $income;

            if ($item['isService'] == 1) {
                $result[$optID]['mskServiceProfit']['value'] += $profit;
                $result[$optID]['mskServiceProfit']['date'][$date] = ($result[$optID]['mskServiceProfit']['date'][$date] ?? 0) + $profit;
                $result[$optID]['mskServiceIncome']['value'] += $income;
                $result[$optID]['mskServiceIncome']['date'][$date] = ($result[$optID]['mskServiceIncome']['date'][$date] ?? 0) + $income;
            }
        }

        if ($item['zapSkladID'] == ZapSklad::SPB || $item['zapSkladID'] == ZapSklad::SPB2) {
            $result[$optID]['spbProfit']['value'] += $profit;
            $result[$optID]['spbProfit']['date'][$date] = ($result[$optID]['SpbProfit']['date'][$date] ?? 0) + $profit;
            $result[$optID]['spbIncome']['value'] += $income;
            $result[$optID]['spbIncome']['date'][$date] = ($result[$optID]['SpbIncome']['date'][$date] ?? 0) + $income;

            if ($item['isService'] == 1) {
                $result[$optID]['spbServiceProfit']['value'] += $profit;
                $result[$optID]['spbServiceProfit']['date'][$date] = ($result[$optID]['spbServiceProfit']['date'][$date] ?? 0) + $profit;
                $result[$optID]['spbServiceIncome']['value'] += $income;
                $result[$optID]['spbServiceIncome']['date'][$date] = ($result[$optID]['spbServiceIncome']['date'][$date] ?? 0) + $income;
            }
        }
        return $result;
    }

    /**
     * @param DateTime $dateFrom
     * @param DateTime $dateTill
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    private function sklad(DateTime $dateFrom, DateTime $dateTill): array
    {

//        SELECT ifnull(SUM((ROUND(a.price-a.price*a.discount/100) - d.price) * c.quantity), 0) AS summ, ifnull(SUM((ROUND(a.price-a.price*a.discount/100)) * c.quantity), 0) AS summ1, e.optID, a.zapSkladID, h.isService
//		FROM order_goods a
//		INNER JOIN orders b ON a.orderID = b.orderID
//		INNER JOIN expense c ON a.goodID = c.goodID
//		INNER JOIN income d ON c.incomeID = d.incomeID
//		INNER JOIN users e ON b.userID = e.userID
//		INNER JOIN expenseDocuments h ON a.expenseDocumentID = h.expenseDocumentID
//		WHERE a.expenseDocumentID <> 0 AND d.price > 0 AND a.incomeID = 0 AND a.number <> '15400PLMA03' $where
//		GROUP BY e.optID, a.zapSkladID, h.isService

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ROUND(og.price - og.price * og.discount / 100) AS priceGood',
                'i.price AS priceZak',
                "e.quantity",
                "u.optID",
                "u.userID",
                "u.name AS user_name",
                'ed.dateofadded',
                'ed.isService',
                'og.zapSkladID'
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->innerJoin('e', 'income', 'i', 'e.incomeID = i.incomeID')
            ->innerJoin('o', 'users', 'u', 'o.userID = u.userID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'og.expenseDocumentID = ed.expenseDocumentID')
            ->andWhere('i.price > 0')
            ->andWhere('og.incomeID IS NULL')
            ->andWhere("og.number <> '15400PLMA03'");


        $qb->andWhere($qb->expr()->gte('ed.dateofadded', ':date_from'));
        $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
        $qb->andWhere($qb->expr()->lte('ed.dateofadded', ':date_till'));
        $qb->setParameter('date_till', $dateTill->format('Y-m-d 23:59:59'));

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param DateTime $dateFrom
     * @param DateTime $dateTill
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    private function zakaz(DateTime $dateFrom, DateTime $dateTill): array
    {

//        SELECT ifnull(SUM((ROUND(a.price-a.price*a.discount/100) - d.price) * g.quantity), 0) AS summ, ifnull(SUM((ROUND(a.price-a.price*a.discount/100)) * g.quantity), 0) AS summ1, e.optID, g.zapSkladID, h.isService
//		FROM order_goods a
//		INNER JOIN orders b ON a.orderID = b.orderID
//		INNER JOIN income d ON a.incomeID = d.incomeID
//		INNER JOIN users e ON b.userID = e.userID
//		INNER JOIN income_sklad g ON a.incomeID = g.incomeID
//		INNER JOIN expenseDocuments h ON a.expenseDocumentID = h.expenseDocumentID
//		WHERE a.expenseDocumentID <> 0 AND d.price > 0 AND a.incomeID <> 0 AND a.number <> '15400PLMA03' $where
//		GROUP BY e.optID, g.zapSkladID, h.isService

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ROUND(og.price - og.price * og.discount / 100) AS priceGood',
                'i.price AS priceZak',
                "isk.quantity",
                "u.optID",
                "u.userID",
                "u.name AS user_name",
                'ed.dateofadded',
                'ed.isService',
                'isk.zapSkladID'
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'income', 'i', 'og.incomeID = i.incomeID')
            ->innerJoin('i', 'income_sklad', 'isk', 'i.incomeID = isk.incomeID')
            ->innerJoin('o', 'users', 'u', 'o.userID = u.userID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'og.expenseDocumentID = ed.expenseDocumentID')
            ->andWhere('i.price > 0')
            ->andWhere('isk.quantity > 0')
            ->andWhere('og.incomeID IS NOT NULL')
            ->andWhere("og.number <> '15400PLMA03'");

        $qb->andWhere($qb->expr()->gte('ed.dateofadded', ':date_from'));
        $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
        $qb->andWhere($qb->expr()->lte('ed.dateofadded', ':date_till'));
        $qb->setParameter('date_till', $dateTill->format('Y-m-d 23:59:59'));

        return $qb->executeQuery()->fetchAllAssociative();
    }
}