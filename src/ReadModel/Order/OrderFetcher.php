<?php


namespace App\ReadModel\Order;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Order\Order;
use App\Model\Shop\Entity\PayMethod\PayMethod;
use App\ReadModel\Order\Filter\Order\Filter;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PDO;
use function Doctrine\DBAL\Query\QueryBuilder;

class OrderFetcher
{
    private Connection $connection;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    public function getByOrder(int $orderID): array
    {
        $orders = [];
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                "b.userID",
                "b.orderID",
                "Max(b.dateofadded) AS dateofadded",
                "SUM(a.quantity) AS quantity",
                "SUM(ROUND(a.price - a.price * a.discount / 100) * a.quantity) AS sum"
            )
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->andWhere('a.orderID = :orderID')
            ->setParameter('orderID', $orderID)
            ->groupBy('b.userID, b.orderID')
            ->orderBy('b.dateofadded');

        $items = $stmt->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $orders[$item['userID']] = [
                'orderID' => $item['orderID'],
                'dateofadded' => $item['dateofadded'],
                'quantity' => $item['quantity'],
                'sum' => $item['sum']
            ];
        }

        return $orders;
    }

    public function findPricesByIncomes(array $incomes): array
    {
        if (!$incomes) return [];
        $arr = [];
        $stmt = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('order_goods', 'og')
            ->where('incomeID in (' . implode(',', $incomes) . ')');

        $items = $stmt->executeQuery()->fetchAllAssociative();
        if ($items) {
            foreach ($items as $item) {
                $arr[$item['incomeID']] = round($item["price"] - $item["price"] * $item["discount"] / 100);
            }
        }
        return $arr;
    }

    public function getNewOrders(Filter $filter, array $settings, int $isFromSite): array
    {
        $orders = [];

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.goodID",
                "a.zapSkladID",
                "a.incomeID",
                "a.schetID",
                "b.userID",
                "b.dateofadded",
                "a.quantity",
                "ROUND(a.price - a.price * a.discount / 100) AS price",
                "r.quantity as reserve"
            )
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->leftJoin('a', 'zapCardReserve', 'r', 'a.goodID = r.goodID')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->andWhere('a.expenseDocumentID IS NULL AND a.isDeleted = 0 AND isFromSite = :isFromSite')
            ->setParameter('isFromSite', $isFromSite)
            ->orderBy('b.dateofadded');


        if ($filter->number) {
            $qb->andWhere('a.number = :number');
            $qb->setParameter('number', (new DetailNumber($filter->number))->getValue());
        }

        if ($filter->createrID) {
            $qb->andWhere('a.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        $items = $qb->executeQuery()->fetchAllAssociative();
        foreach ($items as $item) {
            $dateofadded = new \DateTime($item['dateofadded']);

            if ($item['zapSkladID'] && !$item['reserve']) {
                $orders[$item['userID']]['sum_sklad_price'] = $item['quantity'] * $item['price'];
                $orders[$item['userID']]['sum_sklad_quantity'] = $item['quantity'];
            } elseif (!$item['zapSkladID'] && !$item['incomeID']) {
                $orders[$item['userID']]['sum_zakaz_price'] = $item['quantity'] * $item['price'];
                $orders[$item['userID']]['sum_zakaz_quantity'] = $item['quantity'];
            } elseif ($isFromSite == 2) {
                $orders[$item['userID']] = [];
            }

            if (isset($orders[$item['userID']])) {
                $orders[$item['userID']]['dateofadded'] = isset($orders[$item['userID']]['dateofadded']) ? (
                $dateofadded > $orders[$item['userID']]['dateofadded'] ? $dateofadded : $orders[$item['userID']]['dateofadded']
                ) : $dateofadded;
            }

        }

        return $orders;
    }

    public function getNotSent(Filter $filter, array $settings): array
    {
        $orders = [];

        $qb = $this->connection->createQueryBuilder()
            ->select("b.userID", "Max(b.dateofadded) AS dateofadded")
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->innerJoin('a', 'zapCardReserve', 'r', 'a.goodID = r.goodID')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->andWhere('a.expenseDocumentID IS NULL AND a.isDeleted = 0')
            ->groupBy('b.userID')
            ->orderBy('b.dateofadded');


        if ($filter->number) {
            $qb->andWhere('a.number = :number');
            $qb->setParameter('number', (new DetailNumber($filter->number))->getValue());
        }

        if ($filter->createrID) {
            $qb->andWhere('a.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $qb = $this->connection->createQueryBuilder()
                ->select(
                    "a.goodID",
                    "a.zapSkladID",
                    "a.incomeID",
                    "a.schetID",
                    "b.userID",
                    "a.isFromSite",
                    "a.quantity",
                    "r.quantity as reserve",
                    "ROUND(a.price - a.price * a.discount / 100) AS price",
                    "i.status as income_status",
                    'e.expenseID'
                )
                ->from('order_goods', 'a')
                ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
                ->leftJoin('a', 'zapCardReserve', 'r', 'a.goodID = r.goodID')
                ->leftJoin('r', 'income', 'i', 'r.incomeID = i.incomeID')
                ->leftJoin('a', 'expense_sklad', 'e', 'a.goodID = e.goodID AND e.status IN (:es_status)')
                ->setParameter('es_status', implode(',', [ExpenseSklad::ADDED, ExpenseSklad::PACKED]))
                ->andWhere('b.status = :status')
                ->setParameter('status', Order::ORDER_STATUS_WORK)
                ->andWhere('b.userID = :userID')
                ->setParameter('userID', $item['userID'])
                ->andWhere('a.expenseDocumentID IS NULL AND a.isDeleted = 0')
                ->orderBy('b.dateofadded');

            if ($filter->number) {
                $qb->andWhere('a.number = :number');
                $qb->setParameter('number', (new DetailNumber($filter->number))->getValue());
            }

            if ($filter->createrID) {
                $qb->andWhere('a.createrID = :createrID');
                $qb->setParameter('createrID', $filter->createrID);
            }

            $goods = $qb->executeQuery()->fetchAllAssociative();
            $is_user = true;
            $reserve = [
                'quantity' => 0,
                'sum' => 0
            ];
            foreach ($goods as $good) {
                if (
                    $good['reserve'] && $good['income_status'] && $good['income_status'] != IncomeStatus::IN_WAREHOUSE ||
                    !$good['reserve'] ||
                    $good['expenseID']
                ) {
                    $is_user = false;
                    break;
                }
                $reserve['quantity'] += $good['reserve'];
                $reserve['sum'] += $good['reserve'] * $good['price'];
            }
            if ($is_user) {
                $orders[$item['userID']] = $reserve + ['dateofadded' => $item['dateofadded']];
            }
        }

        return $orders;
    }

    public function getByFilter(Filter $filter, array $settings): array
    {
        $orders = [];

        $qb = $this->connection->createQueryBuilder()
            ->select("b.userID", "Max(b.dateofadded) AS dateofadded", "SUM(a.quantity) AS quantity", "SUM(ROUND(a.price - a.price * a.discount / 100) * a.quantity) AS sum")
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->innerJoin('a', 'income', 'c', 'a.incomeID = c.incomeID')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->groupBy('b.userID')
            ->orderBy('b.dateofadded');

        if ($filter->number) {
            $qb->andWhere('a.number = :number');
            $qb->setParameter('number', (new DetailNumber($filter->number))->getValue());
        }

        if ($filter->createrID) {
            $qb->andWhere('a.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $orders[$item['userID']] = [
                'dateofadded' => $item['dateofadded'],
                'quantity' => $item['quantity'],
                'sum' => $item['sum']
            ];
        }

        return $orders;
    }

    public function getInWork(Filter $filter, array $settings): array
    {
        $orders = [];

        $qb = $this->connection->createQueryBuilder()
            ->select("b.userID", "Max(b.dateofadded) AS dateofadded", "SUM(a.quantity) AS quantity", "SUM(ROUND(a.price - a.price * a.discount / 100) * a.quantity) AS sum")
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->innerJoin('a', 'income', 'c', 'a.incomeID = c.incomeID')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->andWhere('a.expenseDocumentID IS NULL AND a.isDeleted = 0')
            ->andWhere('c.status IN (' . implode(',', IncomeStatus::ARR_IN_PATH) . ')')
            ->groupBy('b.userID')
            ->orderBy('b.dateofadded');

        if ($filter->number) {
            $qb->andWhere('a.number = :number');
            $qb->setParameter('number', (new DetailNumber($filter->number))->getValue());
        }

        if ($filter->createrID) {
            $qb->andWhere('a.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $orders[$item['userID']] = [
                'dateofadded' => $item['dateofadded'],
                'quantity' => $item['quantity'],
                'sum' => $item['sum']
            ];
        }

        return $orders;
    }

    public function getNotPaid(Filter $filter, array $settings): array
    {
        $orders = [];

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "b.userID",
                'b.dateofadded',
                'ROUND(a.price - a.price * a.discount / 100) AS price',
                'a.quantity',
                'c.schetID',
                'c.schet_num',
                '(SELECT SUM(price * quantity) FROM schet_goods WHERE schetID = c.schetID) AS sum',
                'c.dateofadded AS dateofschet'
            )
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->innerJoin('a', 'schet', 'c', 'a.schetID = c.schetID')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->andWhere('a.expenseDocumentID IS NULL AND a.isDeleted = 0')
            ->andWhere('c.status = :schet_status')
            ->setParameter('schet_status', Schet::NOT_PAID)
            ->orderBy('b.dateofadded');

        if ($filter->number) {
            $qb->andWhere('a.number = :number');
            $qb->setParameter('number', (new DetailNumber($filter->number))->getValue());
        }

        if ($filter->createrID) {
            $qb->andWhere('a.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $dateofadded = new \DateTime($item['dateofadded']);

            if (isset($orders[$item['userID']])) {
                $orders[$item['userID']]['dateofadded'] = $dateofadded > $orders[$item['userID']]['dateofadded'] ? $dateofadded : $orders[$item['userID']]['dateofadded'];
                $orders[$item['userID']]['quantity'] += $item['quantity'];
                $orders[$item['userID']]['sum'] += $item['quantity'] * $item['price'];
                if (!isset($orders[$item['userID']]['schets'][$item['schetID']])) {
                    $orders[$item['userID']]['schets'][$item['schetID']] = [
                        'schet_num' => $item['schet_num'],
                        'dateofschet' => $item['dateofschet'],
                        'sum' => $item['sum']
                    ];
                }
            } else {
                $orders[$item['userID']] = [
                    'dateofadded' => $dateofadded,
                    'quantity' => $item['quantity'],
                    'sum' => $item['quantity'] * $item['price'],
                    'schets' => [
                        $item['schetID'] => [
                            'schet_num' => $item['schet_num'],
                            'dateofschet' => $item['dateofschet'],
                            'sum' => $item['sum']
                        ]
                    ]
                ];

            }
        }

        return $orders;
    }

    public function getPick(Filter $filter, array $settings, int $isShipping): array
    {
        $orders = [];

//        $qb = $this->connection->createQueryBuilder()
//            ->select(
//                "a.userID",
//                "MAX(o.dateofadded) AS dateofadded",
//                "SUM(b.quantity) AS quantity",
//                "SUM(ROUND(b.price - b.price * b.discount / 100) * b.quantity) AS sum"
//            )
//            ->from('expenseDocuments', 'a')
//            ->innerJoin('a', 'orders', 'o', 'a.userID = o.userID')
//            ->innerJoin('o', 'order_goods', 'b', 'o.orderID = b.orderID')
//            ->andWhere('a.status = 0')
//            ->andWhere('a.isShipping = :isShipping')
//            ->setParameter('isShipping', $isShipping)
//            ->andWhere('b.expenseDocumentID IS NULL AND b.isDeleted = 0')
//            ->andWhere('o.status = :status')
//            ->setParameter('status', Order::ORDER_STATUS_WORK)
//            ->groupBy('a.userID')
//            ->orderBy('o.dateofadded');

        $users = $this->connection->createQueryBuilder()
            ->select(
                "a.userID"
            )
            ->from('expenseDocuments', 'a')
            ->andWhere('a.status = 0')
            ->andWhere('a.isShipping = :isShipping')
            ->setParameter('isShipping', $isShipping)
            ->groupBy('a.userID')
            ->executeQuery()
            ->fetchFirstColumn();

        if ($users) {
            $qb = $this->connection->createQueryBuilder()
                ->select(
                    "o.userID",
                    "MAX(o.dateofadded) AS dateofadded",
                    "SUM(b.quantity) AS quantity",
                    "SUM(ROUND(b.price - b.price * b.discount / 100) * b.quantity) AS sum"
                )
                ->from('orders', 'o')
                ->innerJoin('o', 'order_goods', 'b', 'o.orderID = b.orderID')
                ->andWhere('b.expenseDocumentID IS NULL AND b.isDeleted = 0')
                ->andWhere('o.status = :status')
                ->setParameter('status', Order::ORDER_STATUS_WORK)
                ->groupBy('o.userID')
                ->orderBy('o.dateofadded');

            $qb->andWhere($qb->expr()->in('o.userID', $users));

            if ($filter->number) {
                $qb->andWhere('b.number = :number');
                $qb->setParameter('number', (new DetailNumber($filter->number))->getValue());
            }

            if ($filter->createrID) {
                $qb->andWhere('b.createrID = :createrID');
                $qb->setParameter('createrID', $filter->createrID);
            }

            $items = $qb->executeQuery()->fetchAllAssociative();

            foreach ($items as $item) {
                $orders[$item['userID']] = [
                    'dateofadded' => $item['dateofadded'],
                    'quantity' => $item['quantity'],
                    'sum' => $item['sum']
                ];
            }
        }

        return $orders;
    }

    public function getExpired(Filter $filter, array $settings): array
    {
        $orders = [];

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "b.userID",
                "MAX(b.dateofadded) AS dateofadded",
                "SUM(a.quantity) AS quantity",
                "SUM(ROUND(a.price - a.price * a.discount / 100) * a.quantity) AS sum"
            )
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->innerJoin('a', 'income', 'c', 'a.incomeID = c.incomeID')
            ->innerJoin('c', 'providerPrices', 'd', 'd.providerPriceID = c.providerPriceID')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->andWhere('a.expenseDocumentID IS NULL AND a.isDeleted = 0')
            ->andWhere('c.status IN (' . implode(',', array_merge(IncomeStatus::ARR_IN_PATH, [IncomeStatus::DEFAULT_STATUS])) . ')')
            ->andWhere('TO_DAYS(NOW()) - TO_DAYS(c.dateofadded) > d.srokInDays')
            ->groupBy('b.userID')
            ->orderBy('b.dateofadded');

        if ($filter->number) {
            $qb->andWhere('a.number = :number');
            $qb->setParameter('number', (new DetailNumber($filter->number))->getValue());
        }

        if ($filter->createrID) {
            $qb->andWhere('a.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $orders[$item['userID']] = [
                'dateofadded' => $item['dateofadded'],
                'quantity' => $item['quantity'],
                'sum' => $item['sum']
            ];
        }

        return $orders;
    }

    public function getAlerts(Filter $filter, array $settings, int $typeID): array
    {
        $orders = [];

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "b.userID",
                "MAX(b.dateofadded) AS dateofadded",
                "SUM(a.quantity) AS quantity",
                "SUM(ROUND(a.price - a.price * a.discount / 100) * a.quantity) AS sum"
            )
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->innerJoin('a', 'order_alerts', 'c', 'a.goodID = c.goodID')
            ->andWhere('c.typeID = :typeID')
            ->setParameter('typeID', $typeID)
            ->groupBy('b.userID')
            ->orderBy('b.dateofadded');

        if ($filter->number) {
            $qb->andWhere('a.number = :number');
            $qb->setParameter('number', (new DetailNumber($filter->number))->getValue());
        }

        if ($filter->createrID) {
            $qb->andWhere('a.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $orders[$item['userID']] = [
                'dateofadded' => $item['dateofadded'],
                'quantity' => $item['quantity'],
                'sum' => $item['sum']
            ];
        }
        return $orders;
    }

    public function getWithResellers(Filter $filter, array $settings): array
    {
        $orders = [];

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "ed.userID",
                "ed.dateofadded",
                "r.name AS reseller_name",
                "(SELECT MAX(dateofadded) FROM orders o WHERE o.userID = ed.userID AND o.status = 2) AS dateofadded"
            )
            ->from('expenseDocuments', 'ed')
            ->innerJoin('ed', 'resellers', 'r', 'ed.reseller_id = r.id')
            ->andWhere('ed.status = :ed_status')
            ->setParameter('ed_status', ExpenseDocument::STATUS_NEW)
            ->orderBy('dateofadded');

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $orders[$item['userID']] = [
                'reseller_name' => $item['reseller_name'],
                'dateofadded' => $item['dateofadded']
            ];
        }

        return $orders;
    }

    public function getPaidCreditCard(): array
    {
        $orders = [];

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "b.userID",
                "b.dateofadded",
                "b.orderID"
            )
            ->from('orders', 'b')
            ->andWhere('b.userID IS NOT NULL AND b.userID <> 0')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_NEW)
            ->andWhere('b.payMethodID = :payMethodID')
            ->setParameter('payMethodID', PayMethod::CREDIT_CARD)
            ->orderBy('b.dateofadded');

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $orders[$item['userID']]['orders'][$item['orderID']] = [
                'dateofadded' => $item['dateofadded'],
                'orderID' => $item['orderID']
            ];
        }
        return $orders;
    }

    public function getNotOrdered(): array
    {
        $orders = [];

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "b.userID",
                "b.dateofadded",
                "b.orderID",
                "b.lastOrderPage",
                "CONCAT(c.name, ' ', a.number, ' - ', a.quantity, ' шт.', ' - ', if (a.zapSkladID is not null, zs.name_short, pp.description)) AS goods")
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->innerJoin('a', 'creaters', 'c', 'a.createrID = c.createrID')
            ->leftJoin('a', 'zapSklad', 'zs', 'a.zapSkladID = zs.zapSkladID')
            ->leftJoin('a', 'providerPrices', 'pp', 'a.providerPriceID = pp.providerPriceID')
            ->andWhere('b.userID IS NOT NULL')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_NEW)
            ->andWhere("b.lastOrderPage <> ''")
            ->andWhere("b.userID <> 0 AND b.userID is not null")
            ->orderBy('b.dateofadded');

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $orders[$item['userID']]['orders'][$item['orderID']] = [
                'dateofadded' => $item['dateofadded'],
                'orderID' => $item['orderID'],
                'lastOrderPage' => $item['lastOrderPage'],
                'goods' => array_merge($orders[$item['userID']]['orders'][$item['orderID']]['goods'] ?? [], [
                    $item['goods']
                ])
            ];
        }
        return $orders;
    }


    /**
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    private function sklad(Manager $manager): array
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
                'ed.dateofadded',
                'ed.isService',
                'og.zapSkladID'
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->innerJoin('e', 'income', 'i', 'e.incomeID = i.incomeID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'og.expenseDocumentID = ed.expenseDocumentID')
            ->andWhere('i.price > 0')
            ->andWhere("og.number <> '15400PLMA03'")
            ->orderBy('o.dateofadded');


        $today = new \DateTime();

        $qb->andWhere($qb->expr()->gte('ed.dateofadded', ':date_from'));
        $qb->setParameter('date_from', $today->format('Y-m-d 00:00:00'));
        $qb->andWhere($qb->expr()->lte('ed.dateofadded', ':date_till'));
        $qb->setParameter('date_till', $today->format('Y-m-d 23:59:59'));

        return $qb->executeQuery()->fetchAllAssociative();
    }

}