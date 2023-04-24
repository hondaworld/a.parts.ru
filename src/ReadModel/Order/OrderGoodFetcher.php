<?php


namespace App\ReadModel\Order;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\User\Entity\Opt\Opt;
use App\ReadModel\Order\Filter\Good\Filter;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;
use function Doctrine\DBAL\Query\QueryBuilder;

class OrderGoodFetcher
{
    private $connection;
    private $repository;

    public const PER_PAGE = 20;
    private PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(OrderGood ::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): OrderGood
    {
        return $this->repository->get($id);
    }

    public function findByIncome(int $incomeID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('order_goods', 'og')
            ->where('og.incomeID = :incomeID')
            ->setParameter('incomeID', $incomeID)
            ->executeQuery();

        return $stmt->fetchAllAssociative();
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

    /**
     * @param int $userID
     * @return float
     * @throws Exception
     */
    public function getSumByRetailUser(int $userID): float
    {
//        SELECT ifnull(Sum((a.price - a.price*a.discount/100) * (a.quantity - a.quantityReturn)), 0) AS p, c.discountParts, c.discountService, c.userID
//	FROM order_goods a
//	INNER JOIN orders b ON a.orderID = b.orderID
//	INNER JOIN users c ON c.userID = b.userID
//	WHERE c.userID = '".AddSlashes($userID)."' AND (a.expenseDocumentID <> 0 OR b.status = 3) AND a.isDeleted = 0 AND c.optID = 1
        return $this->connection->createQueryBuilder()
            ->select('IfNull(Sum((og.price - og.price * og.discount / 100) * (og.quantity - og.quantityReturn)), 0)')
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('o', 'users', 'u', 'o.userID = u.userID')
            ->where('u.userID = :userID')
            ->setParameter('userID', $userID)
            ->andWhere('og.expenseDocumentID IS NOT NULL OR o.status = 3')
            ->andWhere('og.isDeleted = 0')
            ->andWhere('u.optID = :optID')
            ->setParameter('optID', Opt::DEFAULT_OPT_ID)
            ->executeQuery()
            ->fetchOne() ?: 0
        ;
    }

    /**
     * @param int $userID
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface|null
     */
    public function all(int $userID, Filter $filter, int $page, array $settings): ?PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'og.goodID',
                'og.expenseDocumentID',
                'og.incomeDocumentID',
                'og.orderID',
                'og.dateofadded',
                'og.createrID',
                'og.number',
                'og.number_old',
                'c.name AS creater_name',
                'og.price',
                'og.discount',
                'og.quantity',
                'og.quantityReturn',
                'og.incomeID',
                'og.zapSkladID',
                'og.providerPriceID',
                'og.isDeleted',
                'og.deleteReasonID',
                'og.schetID',
                'o.status AS order_status',
                'o.siteID',
                'o.deliveryID',
                'o.payMethodID',
                'o.user_contactID',
                'o.dostavka',
                'o.isOwnDelivery',
                'o.office_id',
                'vin',
                'i.price AS income_price',
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'creaters', 'c', 'c.createrID = og.createrID')
            ->leftJoin('og', 'income', 'i', 'og.incomeID = i.incomeID')
            ->andWhere('o.userID = :userID')
            ->setParameter('userID', $userID)
            ->andWhere('o.status > 1')
            ->orderBy('o.dateofadded', 'DESC')
            ->addOrderBy('og.dateofadded', 'DESC')
            ->addOrderBy('og.goodID', 'DESC');

        if ($filter->number) {
            $number = new DetailNumber($filter->number);
            $qb->andWhere($qb->expr()->like('og.number', ':number'));
            $qb->setParameter('number', $number->getValue() . '%');
        }

        if ($filter->orderID) {
            if (intval(trim($filter->orderID)) > 0) {
                $qb->andWhere('og.orderID = :orderID');
                $qb->setParameter('orderID', trim($filter->orderID));
            }
        }

        if ($filter->createrID) {
            $qb->andWhere('og.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        if ($filter->schetNumber) {
            $qb->innerJoin('og', 'schet', 's', 'og.schetID = s.schetID');
            $qb->andWhere('s.schet_num = :schet_num');
            $qb->setParameter('schet_num', $filter->schetNumber);
        }

        if ($filter->expenseDocumentNumber) {
//            $qb->innerJoin('og', 'expenseDocuments', 'ed', 'og.expenseDocumentID = ed.expenseDocumentID');
            $qb->andWhere($qb->expr()->or(
                $qb->expr()->eq('(SELECT document_num FROM expenseDocuments WHERE expenseDocumentID = og.expenseDocumentID)', ':document_num'),
                $qb->expr()->eq('(SELECT document_num FROM incomeDocuments WHERE incomeDocumentID = og.incomeDocumentID)', ':document_num')
            ));
//            $qb->andWhere('ed.document_num = :document_num');
            $qb->setParameter('document_num', $filter->expenseDocumentNumber);
        }

        if ($filter->providerID) {
            $qb->innerJoin('og', 'providerPrices', 'pp', 'og.providerPriceID = pp.providerPriceID');
            $qb->andWhere('pp.providerID = :providerID');
            $qb->setParameter('providerID', $filter->providerID);
        }

        if ($filter->zapSkladID) {
            $qb->andWhere('og.zapSkladID = :zapSkladID');
            $qb->setParameter('zapSkladID', $filter->zapSkladID);
        }

        if (!$filter->isShowAllGoods) {
            $qb->andWhere("og.expenseDocumentID IS NULL AND og.isDeleted = 0 AND o.status <> 3");
        }

        if ($filter->incomeStatus) {
            if ($filter->incomeStatus == -1) {
                $qb->andWhere('og.incomeID IS NULL AND og.zapSkladID IS NULL AND o.status <> 3');
            } else {
                $whereSql = '(og.incomeID IN (SELECT incomeID FROM income WHERE status = :incomeStatus)';
                if ($filter->incomeStatus == IncomeStatus::IN_WAREHOUSE) {
                    $whereSql .= ' OR (og.zapSkladID IS NOT NULL AND og.isDeleted = 0)';
                }
                if ($filter->incomeStatus == IncomeStatus::FAILURE_USER) {
                    $whereSql .= ' OR og.isDeleted <> 0';
                }
                $whereSql .= " AND o.status <> 3)";
                $qb->andWhere($whereSql);
                $qb->setParameter('incomeStatus', $filter->incomeStatus);
            }
        }

        if ($filter->reserve) {
            if ($filter->reserve == 'reserve') {
                $qb->andWhere("og.goodID IN (SELECT goodID FROM zapCardReserve)");
            } elseif ($filter->reserve == 'not_reserve') {
                $qb->andWhere("og.goodID NOT IN (SELECT goodID FROM zapCardReserve)");
            } elseif ($filter->reserve == 'shipping') {
                $qb->andWhere("og.goodID IN (SELECT goodID FROM expense) AND og.expenseDocumentID IS NULL");
            }
        }

        $size = $settings['inPage'] ?? self::PER_PAGE;

        return $this->paginator->paginate($qb, $page, $size);
    }

    /**
     * @param int $orderID
     * @param int $page
     * @return PaginationInterface|null
     */
    public function allSimple(int $orderID, int $page): ?PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'og.goodID',
                'og.expenseDocumentID',
                'og.incomeDocumentID',
                'og.orderID',
                'og.dateofadded',
                'og.createrID',
                'og.number',
                'c.name AS creater_name',
                'og.price',
                'og.discount',
                'og.quantity',
                'og.quantityReturn',
                'og.incomeID',
                'og.zapSkladID',
                'og.providerPriceID',
                'og.isDeleted',
                'og.deleteReasonID',
                'og.schetID',
                'o.status AS order_status',
                'o.siteID',
                'o.deliveryID',
                'o.payMethodID',
                'o.user_contactID',
                'o.dostavka',
                'o.isOwnDelivery',
                'o.office_id',
                'vin',
                'i.price AS income_price',
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'creaters', 'c', 'c.createrID = og.createrID')
            ->leftJoin('og', 'income', 'i', 'og.incomeID = i.incomeID')
            ->andWhere('o.orderID = :orderID')
            ->setParameter('orderID', $orderID)
            ->orderBy('o.dateofadded', 'DESC')
            ->addOrderBy('og.dateofadded', 'DESC')
            ->addOrderBy('og.goodID', 'DESC');

        $size = self::PER_PAGE;

        return $this->paginator->paginate($qb, $page, $size);
    }

    public function allExpenses(int $userID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'og.goodID',
                'og.expenseDocumentID',
                'og.orderID',
                'og.dateofadded',
                'og.createrID',
                'og.number',
                'c.name AS creater_name',
                'og.price',
                'og.discount',
                'og.quantity',
                'og.quantityReturn',
                'og.quantityPicking',
                'og.incomeID',
                'og.zapSkladID',
                'og.providerPriceID',
                'o.status AS order_status',
                'i.price AS income_price'
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'creaters', 'c', 'c.createrID = og.createrID')
            ->leftJoin('og', 'income', 'i', 'og.incomeID = i.incomeID')
            ->andWhere('o.userID = :userID')
            ->setParameter('userID', $userID)
            ->andWhere('o.status > 1')
            ->andWhere('og.expenseDocumentID IS NULL')
            ->andWhere('og.goodID IN (SELECT goodID FROM expense)')
            ->orderBy('og.number', 'ASC')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    public function allByOrderGoods(int $userID, array $goods): array
    {
        if (!$goods) return [];
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'og.goodID',
                'og.expenseDocumentID',
                'og.orderID',
                'og.dateofadded',
                'og.createrID',
                'og.number',
                'c.name AS creater_name',
                'og.price',
                'og.discount',
                'og.quantity',
                'og.quantityReturn',
                'og.quantityPicking',
                'og.incomeID',
                'og.zapSkladID',
                'og.providerPriceID',
                'o.status AS order_status',
                'i.price AS income_price',
                'pp.srok'
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'creaters', 'c', 'c.createrID = og.createrID')
            ->leftJoin('og', 'income', 'i', 'og.incomeID = i.incomeID')
            ->leftJoin('og', 'providerPrices', 'pp', 'og.providerPriceID = pp.providerPriceID')
            ->andWhere('o.userID = :userID')
            ->setParameter('userID', $userID)
            ->andWhere('og.expenseDocumentID IS NULL')
            ->orderBy('og.number', 'ASC')
            ;
        $stmt->andWhere($stmt->expr()->in('og.goodID', $goods));

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param int $userID
     * @return bool
     * @throws Exception
     */
    public function hasPicking(int $userID): bool
    {
        $query = $this->connection->createQueryBuilder()
            ->select('SUM(og.quantity - og.quantityPicking)')
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->andWhere('o.userID = :userID')
            ->setParameter('userID', $userID)
            ->andWhere('o.status > 1')
            ->andWhere('og.expenseDocumentID IS NULL')
            ->andWhere('og.goodID IN (SELECT goodID FROM expense)');

        return $query->executeQuery()->fetchOne() > 0;
    }

    public function sumExpenses(int $userID): float
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('Sum(round(og.price - og.price * og.discount / 100) * og.quantity) AS sum')
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->andWhere('o.userID = :userID')
            ->setParameter('userID', $userID)
            ->andWhere('o.status > 1')
            ->andWhere('og.expenseDocumentID IS NULL')
            ->andWhere('og.goodID IN (SELECT goodID FROM expense)')
            ->executeQuery();

        return $stmt->fetchOne() ?: 0;
    }

    public function findByExpenseDocument(int $expenseDocumentID): array
    {
//        SELECT a.number, a.createrID, d.shop_gtdID, d.shop_gtdID1, d.incomeID, SUM(c.quantity) AS quantity, a.price, a.discount
//		FROM order_goods a
//		INNER JOIN expense c ON a.goodID = c.goodID
//		INNER JOIN income d ON c.incomeID = d.incomeID
//		WHERE expenseDocumentID = '".AddSlashes($rowN->expenseDocumentID)."'
//		GROUP BY a.goodID, d.shop_gtdID

        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'og.goodID',
                'og.createrID',
                'og.number',
                'c.name AS creater_name',
                'og.price',
                'og.discount',
                'Sum(e.quantity) AS quantity',
                'i.shop_gtdID',
                'i.shop_gtdID1',
                'i.incomeID'
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'creaters', 'c', 'c.createrID = og.createrID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->leftJoin('e', 'income', 'i', 'e.incomeID = i.incomeID')
            ->andWhere('og.expenseDocumentID = :expenseDocumentID')
            ->setParameter('expenseDocumentID', $expenseDocumentID)
            ->groupBy('og.goodID, i.shop_gtdID')
            ->orderBy('og.number', 'ASC')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    public function findIncomeStatusChanged(): array
    {
//        SELECT b.userID FROM order_goods a INNER JOIN orders b ON a.orderID = b.orderID WHERE (a.deleteReasonEmailed = 0 OR a.lastIncomeStatusEmailed = 0) GROUP BY b.userID

        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'og.goodID',
                'og.orderID',
                'og.dateofadded',
                'og.createrID',
                'og.number',
                'og.price',
                'og.quantity',
                'og.discount',
                'c.name AS creater_name',
                'o.userID',
                'og.dateofdeleted',
                'og.zapSkladID',
                'og.lastIncomeStatusEmailed',
                'og.lastIncomeStatus',
                'og.lastIncomeStatusDate',
                'og.deleteReasonEmailed',
                'og.deleteReasonID',
                'og.incomeID',
                "if (og.incomeID IS NOT NULL, (SELECT zapSkladID FROM income_sklad WHERE quantity > 0 AND incomeID = og.incomeID LIMIT 1), null) AS incomeSkladID",
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'creaters', 'c', 'c.createrID = og.createrID')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->andWhere("og.deleteReasonEmailed = 0 OR og.lastIncomeStatusEmailed = 0")
        ;
        $arr = $stmt->executeQuery()->fetchAllAssociative();

        $result = [];
        foreach ($arr as $item) {
            $result[$item['userID']][] = $item;
        }

        return $result;
    }
}