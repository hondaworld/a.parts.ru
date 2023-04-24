<?php


namespace App\ReadModel\Order;


use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Order\Entity\Order\Order;
use App\Model\Shop\Entity\PayMethod\PayMethod;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class OrderListFetcher
{
    private Connection $connection;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    public function getNewOrders(): array
    {
        $orders = [
            'newByUser' => [],
            'newByManager' => [],
            'newByCron' => []
        ];

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.goodID",
                "a.zapSkladID",
                "a.incomeID",
                "a.schetID",
                "b.userID",
                "a.isFromSite",
                "a.quantity",
                "r.quantity as reserve"
            )
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->leftJoin('a', 'zapCardReserve', 'r', 'a.goodID = r.goodID')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->andWhere('a.expenseDocumentID IS NULL AND a.isDeleted = 0');

        $items = $qb->executeQuery()->fetchAllAssociative();
        foreach ($items as $item) {
            if ($item['isFromSite'] == 2) {
                if (!in_array($item['userID'], $orders['newByCron'])) $orders['newByCron'][] = $item['userID'];
            } else {
                if ($item['zapSkladID'] && !$item['reserve']) {
                    if ($item['isFromSite'] == 1) {
                        if (!in_array($item['userID'], $orders['newByUser'])) $orders['newByUser'][] = $item['userID'];
                    } else {
                        if (!in_array($item['userID'], $orders['newByManager'])) $orders['newByManager'][] = $item['userID'];
                    }
                } elseif (!$item['zapSkladID'] && !$item['incomeID']) {
                    if ($item['isFromSite'] == 1) {
                        if (!in_array($item['userID'], $orders['newByUser'])) $orders['newByUser'][] = $item['userID'];
                    } else {
                        if (!in_array($item['userID'], $orders['newByManager'])) $orders['newByManager'][] = $item['userID'];
                    }
                }
            }
        }

        return $orders;
    }

    public function getNotSent(): array
    {
        $orders = [
            'not_sent' => []
        ];

        $qb = $this->connection->createQueryBuilder()
            ->select("b.userID")
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->innerJoin('a', 'zapCardReserve', 'r', 'a.goodID = r.goodID')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->andWhere('a.expenseDocumentID IS NULL AND a.isDeleted = 0')
            ->groupBy('b.userID');

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
                ->andWhere('a.expenseDocumentID IS NULL AND a.isDeleted = 0');
            $goods = $qb->executeQuery()->fetchAllAssociative();
            $is_user = true;
            foreach ($goods as $good) {
                if (
                    $good['reserve'] && $good['income_status'] && $good['income_status'] != IncomeStatus::IN_WAREHOUSE ||
                    !$good['reserve'] ||
                    $good['expenseID']
                ) {
                    $is_user = false;
                    break;
                }
            }
            if ($is_user) {
                if (!in_array($item['userID'], $orders['not_sent'])) $orders['not_sent'][] = $item['userID'];
            }
        }

        return $orders;
    }

    public function getInWork(): array
    {
        $orders = [
            'in_work' => []
        ];

        $qb = $this->connection->createQueryBuilder()
            ->select("b.userID")
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->innerJoin('a', 'income', 'c', 'a.incomeID = c.incomeID')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->andWhere('a.expenseDocumentID IS NULL AND a.isDeleted = 0')
            ->andWhere('c.status IN (' . implode(',', IncomeStatus::ARR_IN_PATH) . ')')
            ->groupBy('b.userID');

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            if (!in_array($item['userID'], $orders['in_work'])) $orders['in_work'][] = $item['userID'];
        }

        return $orders;
    }

    public function getNotPaid(): array
    {
        $orders = [
            'not_paid' => []
        ];

        $qb = $this->connection->createQueryBuilder()
            ->select("b.userID")
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->innerJoin('a', 'schet', 'c', 'a.schetID = c.schetID')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->andWhere('a.expenseDocumentID IS NULL AND a.isDeleted = 0')
            ->andWhere('c.status = :schet_status')
            ->setParameter('schet_status', Schet::NOT_PAID)
            ->groupBy('b.userID');

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            if (!in_array($item['userID'], $orders['not_paid'])) $orders['not_paid'][] = $item['userID'];
        }

        return $orders;
    }

    public function getPick(): array
    {
        $orders = [
            'picking' => [],
            'picked' => []
        ];

        $qb = $this->connection->createQueryBuilder()
            ->select("a.userID", "a.isShipping")
            ->from('expenseDocuments', 'a')
            ->andWhere('a.status = 0')
            ->andWhere('a.isShipping IN (1,2)');

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            if ($item['isShipping'] == 1) {
                if (!in_array($item['userID'], $orders['picking'])) $orders['picking'][] = $item['userID'];
            } elseif ($item['isShipping'] == 2) {
                if (!in_array($item['userID'], $orders['picked'])) $orders['picked'][] = $item['userID'];
            }
        }

        return $orders;
    }

    public function getExpired(): array
    {
        $orders = [
            'expired' => []
        ];

        $qb = $this->connection->createQueryBuilder()
            ->select("b.userID")
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->innerJoin('a', 'income', 'c', 'a.incomeID = c.incomeID')
            ->innerJoin('c', 'providerPrices', 'd', 'd.providerPriceID = c.providerPriceID')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->andWhere('a.expenseDocumentID IS NULL AND a.isDeleted = 0')
            ->andWhere('c.status IN (' . implode(',', array_merge(IncomeStatus::ARR_IN_PATH, [IncomeStatus::DEFAULT_STATUS])) . ')')
            ->andWhere('TO_DAYS(NOW()) - TO_DAYS(c.dateofadded) > d.srokInDays')
            ->groupBy('b.userID');

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            if (!in_array($item['userID'], $orders['expired'])) $orders['expired'][] = $item['userID'];
        }

        return $orders;
    }

    public function getAlerts(array $types): array
    {
        $orders = [];

        foreach (array_keys($types) as $typeID) {
            $orders['alert_' . $typeID] = [];
        }

        $qb = $this->connection->createQueryBuilder()
            ->select("b.userID", "c.typeID")
            ->from('order_goods', 'a')
            ->innerJoin('a', 'orders', 'b', 'a.orderID = b.orderID')
            ->innerJoin('a', 'order_alerts', 'c', 'a.goodID = c.goodID')
            ->groupBy('b.userID, c.typeID');

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            if (!in_array($item['userID'], $orders['alert_' . $item['typeID']])) $orders['alert_' . $item['typeID']][] = $item['userID'];
        }

        return $orders;
    }

    public function getService(): array
    {
        $orders = [
            'service' => []
        ];

        $qb = $this->connection->createQueryBuilder()
            ->select("u.userID")
            ->from('users', 'u')
            ->andWhere('u.dateofservice IS NOT NULL');

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            if (!in_array($item['userID'], $orders['service'])) $orders['service'][] = $item['userID'];
        }

        return $orders;
    }

    public function getDelivery(): array
    {
        $orders = [
            'delivery' => []
        ];

        $qb = $this->connection->createQueryBuilder()
            ->select("u.userID")
            ->from('users', 'u')
            ->andWhere('u.dateofdelivery IS NOT NULL');

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            if (!in_array($item['userID'], $orders['delivery'])) $orders['delivery'][] = $item['userID'];
        }

        return $orders;
    }

    public function getReseller(): array
    {
        $orders = [
            'reseller' => []
        ];

        $qb = $this->connection->createQueryBuilder()
            ->select("a.userID")
            ->from('expenseDocuments', 'a')
            ->andWhere('a.status = 0')
            ->andWhere('a.reseller_id IS NOT NULL');

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            if (!in_array($item['userID'], $orders['reseller'])) $orders['reseller'][] = $item['userID'];
        }

        return $orders;
    }

    public function getPaidCreditCard(): array
    {
        $orders = [
            'paid_credit_card' => []
        ];

        $qb = $this->connection->createQueryBuilder()
            ->select("b.orderID")
            ->from('orders', 'b')
            ->andWhere('b.userID IS NOT NULL AND b.userID <> 0')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_NEW)
            ->andWhere('b.payMethodID = :payMethodID')
            ->setParameter('payMethodID', PayMethod::CREDIT_CARD);

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            if (!in_array($item['orderID'], $orders['paid_credit_card'])) $orders['paid_credit_card'][] = $item['orderID'];
        }

        return $orders;
    }

    public function getNotOrdered(): array
    {
        $orders = [
            'not_ordered' => []
        ];

        $qb = $this->connection->createQueryBuilder()
            ->select("b.orderID")
            ->from('orders', 'b')
            ->andWhere('b.userID IS NOT NULL')
            ->andWhere('b.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_NEW)
            ->andWhere("b.lastOrderPage <> ''")
            ->andWhere("b.userID <> 0 AND b.userID is not null");

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            if (!in_array($item['orderID'], $orders['not_ordered'])) $orders['not_ordered'][] = $item['orderID'];
        }

        return $orders;
    }

    public function getNewOrdersLast5Minutes(string $date_from, string $date_till): int
    {
//        SELECT a.orderID FROM order_goods a INNER JOIN orders b ON a.orderID = b.orderID WHERE a.isFromSite = 1 AND a.expenseDocumentID = 0 AND b.status = 2 AND b.dateofadded dateofadded < '".$date_to."' AND dateofadded >= '".$date_from." GROUP BY a.orderID

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "Count(a.orderID)"
            )
            ->from('orders', 'a')
            ->innerJoin('a', 'order_goods', 'b', 'a.orderID = b.orderID')
            ->andWhere('a.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK)
            ->andWhere('b.expenseDocumentID IS NULL AND b.isDeleted = 0 AND b.isFromSite = 1')
            ->groupBy('a.orderID');

        $qb->andWhere($qb->expr()->gte('b.dateofadded', ':date_from'));
        $qb->setParameter('date_from', $date_from);

        $qb->andWhere($qb->expr()->lte('b.dateofadded', ':date_till'));
        $qb->setParameter('date_till', $date_till);

        return $qb->executeQuery()->fetchOne();
    }

    public function getTodayOrders(): int
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("Count(a.orderID)")
            ->from('orders', 'a')
            ->andWhere('a.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_WORK);

        $today = new \DateTime();
        $date_from = $today->format('Y-m-d') . ' 00:00:00';
        $date_till = $today->format('Y-m-d') . ' 23:59:59';

        $qb->andWhere($qb->expr()->gte('a.dateofadded', ':date_from'));
        $qb->setParameter('date_from', $date_from);

        $qb->andWhere($qb->expr()->lte('a.dateofadded', ':date_till'));
        $qb->setParameter('date_till', $date_till);

        return $qb->executeQuery()->fetchOne();
    }


}