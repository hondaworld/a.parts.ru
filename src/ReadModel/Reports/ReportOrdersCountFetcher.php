<?php


namespace App\ReadModel\Reports;


use App\Model\Order\Entity\Order\Order;
use App\Model\User\Entity\Opt\Opt;
use App\ReadModel\Reports\Filter\OrdersCount\Filter;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ReportOrdersCountFetcher extends ReportFetcher
{
    private Connection $connection;
    private array $arr = [];

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
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
     * @param Filter $filter
     * @return array|null
     * @throws Exception
     */
    public function all(Filter $filter): ?array
    {
        if (!$filter->dateofreport || !$filter->dateofreport['date_from'] || !$filter->dateofreport['date_till']) return null;

        $dateFrom = new DateTime($filter->dateofreport['date_from']);
        $dateTill = new DateTime($filter->dateofreport['date_till']);

//        SELECT Count(DISTINCT h.orderID) AS c, ifnull(SUM(a.price * a.quantity), 0) AS sum, h.order_add_reasonID, h.managerID
//			FROM orders h
//			INNER JOIN order_goods a ON h.orderID = a.orderID
//			INNER JOIN users b ON h.userID = b.userID
//			WHERE h.status = 2 AND h.managerID <> 0 AND b.optID = 1 AND h.order_add_reasonID <> 0 AND h.dateofadded BETWEEN '".$row->dateofadded." 00:00:00' AND '".$row->dateofadded." 23:59:59'
//			GROUP BY h.managerID, h.order_add_reasonID

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ROUND(og.price - og.price * og.discount / 100) AS priceGood',
                "og.quantity",
                "og.number",
                "og.createrID",
                "u.optID",
                "u.userID",
                "o.orderID",
                "o.order_add_reasonID",
                'o.dateofadded',
                'o.managerID',
                'og.isDeleted',
                'og.deleteReasonID',
                'og.deleteManagerID',
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('o', 'users', 'u', 'o.userID = u.userID')
            ->andWhere("og.number <> '15400PLMA03'")
            ->andWhere("o.status = :status")
            ->setParameter('status', Order::ORDER_STATUS_WORK)
        ;


        $qb->andWhere($qb->expr()->gte('o.dateofadded', ':date_from'));
        $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
        $qb->andWhere($qb->expr()->lte('o.dateofadded', ':date_till'));
        $qb->setParameter('date_till', $dateTill->format('Y-m-d 23:59:59'));

        $arr = $qb->executeQuery()->fetchAllAssociative();

        return $this->generateArray($arr);
    }

    /**
     * @param array $arr
     * @return array
     * @throws Exception
     */
    private function generateArray(array $arr): array
    {
        $result = [];

        foreach ($arr as $item) {
            $date = (new DateTime($item['dateofadded']))->format('Y-m-d');
            $orderSum = $item['isDeleted'] == 1 ? 0 : $item['priceGood'] * $item['quantity'];

            if (!isset($result[$date])) {
                $result[$date] = [
                    'date' => new DateTime($item['dateofadded']),
                    'managers' => [],
                    'countOpt' => [],
                    'countNotOpt' => [],
                    'countDeleted' => []
                ];
            }

            if ($item['managerID']) {
                if ($item['optID'] == Opt::DEFAULT_OPT_ID) {
                    if (!isset($result[$date]['managers'][$item['managerID']])) {
                        $result[$date]['managers'][$item['managerID']] = [
                            'sum' => 0,
                            'reasons' => []
                        ];
                    }

                    if ($item['order_add_reasonID']) {
                        if (!isset($result[$date]['managers'][$item['managerID']]['reasons'][$item['order_add_reasonID']])) {
                            $result[$date]['managers'][$item['managerID']]['reasons'][$item['order_add_reasonID']] = [];
                        }

                        if (!isset($result[$date]['managers'][$item['managerID']]['reasons'][$item['order_add_reasonID']][$item['orderID']])) {
                            $result[$date]['managers'][$item['managerID']]['reasons'][$item['order_add_reasonID']][$item['orderID']] = [
                                'sum' => 0,
                                'userID' => $item['userID']
                            ];
                        }

                        $result[$date]['managers'][$item['managerID']]['sum'] += $orderSum;

                        $result[$date]['managers'][$item['managerID']]['reasons'][$item['order_add_reasonID']][$item['orderID']]['sum'] += $orderSum;
                    }
                }
            } else {
                if ($item['optID'] == Opt::DEFAULT_OPT_ID) {
                    if (!isset($result[$date]['countNotOpt'][$item['orderID']])) {
                        $result[$date]['countNotOpt'][$item['orderID']] = [
                            'sum' => 0,
                            'userID' => $item['userID']
                        ];
                    }
                    $result[$date]['countNotOpt'][$item['orderID']]['sum'] += $orderSum;
                } else {
                    if (!isset($result[$date]['countOpt'][$item['orderID']])) {
                        $result[$date]['countOpt'][$item['orderID']] = [
                            'sum' => 0,
                            'userID' => $item['userID']
                        ];
                    }
                    $result[$date]['countOpt'][$item['orderID']]['sum'] += $orderSum;
                }
            }

            if ($item['isDeleted'] == 1) {
                if (!isset($result[$date]['countDeleted'][$item['orderID']])) {
                    $result[$date]['countDeleted'][$item['orderID']] = [];
                }
                $result[$date]['countDeleted'][$item['orderID']][] = [
                    'userID' => $item['userID'],
                    'createrID' => $item['createrID'],
                    'number' => $item['number'],
                    'deleteReasonID' => $item['deleteReasonID'],
                    'deleteManagerID' => $item['deleteManagerID']
                ];
            }

        }
        return $result;
    }
}