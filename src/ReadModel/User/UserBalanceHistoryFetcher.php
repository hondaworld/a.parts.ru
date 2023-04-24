<?php


namespace App\ReadModel\User;


use App\Model\EntityNotFoundException;
use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use App\Model\User\Entity\User\User;
use App\ReadModel\User\Filter;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class UserBalanceHistoryFetcher
{
    private $connection;
    private $paginator;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(UserBalanceHistory::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): UserBalanceHistory
    {

        if (!$userBalanceHistory = $this->repository->find($id)) {
            throw new EntityNotFoundException('Запись не найдена');
        }

        return $userBalanceHistory;
    }

    /**
     * @param User $user
     * @param Filter\UserBalanceHistory\Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     * @throws \Exception
     */
    public function allByUser(User $user, Filter\UserBalanceHistory\Filter $filter, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'b.balanceID',
                'b.firmID',
                'f.name_short AS firm',
                "b.schetID",
                "b.expenseDocumentID",
                'b.dateofadded',
                'b.balance',
                'ft.name AS finance_type',
                'b.attach',
                'b.description',
                's.schet_num',
                'ed.document_num',
            )
            ->from('userBalanceHistory', 'b')
            ->leftJoin('b', 'finance_types', 'ft', 'b.finance_typeID = ft.finance_typeID')
            ->leftJoin('b', 'firms', 'f', 'b.firmID = f.firmID')
            ->leftJoin('b', 'schet', 's', 'b.schetID = s.schetID')
            ->leftJoin('b', 'expenseDocuments', 'ed', 'b.expenseDocumentID = ed.expenseDocumentID')
            ->where('b.userID = :userID')
            ->setParameter('userID', $user->getId());

        if ($filter->firmID) {
            $qb->andWhere('b.firmID = :firmID');
            $qb->setParameter('firmID', $filter->firmID);
        }

        if ($filter->dateofadded) {
            if ($filter->dateofadded['date_from']) {
                $qb->andWhere($qb->expr()->gte('b.dateofadded', ':date_from'));
                $qb->setParameter('date_from', (new DateTime($filter->dateofadded['date_from']))->format('Y-m-d 00:00:00'));
            }
            if ($filter->dateofadded['date_till']) {
                $qb->andWhere($qb->expr()->lte('b.dateofadded', ':date_till'));
                $qb->setParameter('date_till', (new DateTime($filter->dateofadded['date_till']))->format('Y-m-d 23:59:59'));
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['dateofadded'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param User $user
     * @return array
     * @throws \Exception
     */
    public function lastOperations(User $user): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'b.balanceID',
                'b.firmID',
                'f.name_short AS firm',
                "b.schetID",
                "b.expenseDocumentID",
                'b.dateofadded',
                'b.balance',
                'ft.name AS finance_type',
                'b.attach',
                'b.description',
                's.schet_num',
                'c.id AS check_id',
                'c.kassa_id',
                'c.fiscal_summ',
                'c.state',
            )
            ->from('userBalanceHistory', 'b')
            ->leftJoin('b', 'finance_types', 'ft', 'b.finance_typeID = ft.finance_typeID')
            ->leftJoin('b', 'firms', 'f', 'b.firmID = f.firmID')
            ->leftJoin('b', 'schet', 's', 'b.schetID = s.schetID')
            ->leftJoin('b', 'checks', 'c', 'b.balanceID = c.balanceID')
            ->where('b.userID = :userID')
            ->andWhere('b.expenseDocumentID IS NULL')
            ->setParameter('userID', $user->getId())
            ->setMaxResults(15)
            ->orderBy('b.dateofadded', 'DESC');
        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param User $user
     * @return array
     * @throws \Exception
     */
    public function sumOperations(User $user): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'f.name_short AS firm',
                'Sum(b.balance) as balance',
                'ft.name AS finance_type',
            )
            ->from('userBalanceHistory', 'b')
            ->innerJoin('b', 'finance_types', 'ft', 'b.finance_typeID = ft.finance_typeID')
            ->innerJoin('b', 'firms', 'f', 'b.firmID = f.firmID')
            ->where('b.userID = :userID')
            ->setParameter('userID', $user->getId())
            ->orderBy('firm', 'ASC')
            ->addOrderBy('finance_type', 'ASC')
            ->groupBy('b.firmID')
            ->addGroupBy('b.finance_typeID');
        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param User $user
     * @param ?Firm $firm
     * @param FinanceType $financeType
     * @return float
     * @throws Exception
     */
    public function sumOperationsByFirmAndType(User $user, FinanceType $financeType, ?Firm $firm = null): float
    {
        if (!$firm) $firm = $financeType->getFirm();

        $qb = $this->connection->createQueryBuilder()
            ->select('Sum(b.balance) as balance')
            ->from('userBalanceHistory', 'b')
            ->andWhere('b.userID = :userID')
            ->setParameter('userID', $user->getId())
            ->andWhere('b.firmID = :firmID')
            ->setParameter('firmID', $firm->getId())
            ->andWhere('b.finance_typeID = :finance_typeID')
            ->setParameter('finance_typeID', $financeType->getId());
        return $qb->executeQuery()->fetchOne() ?: 0;
    }

    /**
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function lastPay(User $user): array
    {
//        SELECT a.balance, c.fiscal_summ, c.state
//	FROM userBalanceHistory a
//	INNER JOIN finance_types b ON a.finance_typeID = b.finance_typeID
//    LEFT JOIN checks c on a.balanceID = c.balanceID
//	WHERE a.userID = $userID AND a.expenseDocumentID = 0
//	ORDER BY a.dateofadded DESC
//	LIMIT 1
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'b.balance',
                'c.fiscal_summ',
                'c.state',
            )
            ->from('userBalanceHistory', 'b')
            ->innerJoin('b', 'finance_types', 'ft', 'b.finance_typeID = ft.finance_typeID')
            ->leftJoin('b', 'checks', 'c', 'b.balanceID = c.balanceID')
            ->where('b.userID = :userID')
            ->setParameter('userID', $user->getId())
            ->andWhere('b.expenseDocumentID IS NULL')
            ->orderBy('b.dateofadded', 'DESC')
            ->setMaxResults(1);
        return $qb->executeQuery()->fetchAssociative();
    }

    /**
     * Возвращает количество дней просрочки
     *
     * @param User $user
     * @return int
     * @throws Exception
     */
    public function getDebtDays(User $user): int
    {
        $countDays = 0;
        $sum = 0;

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ifnull(SUM(balance), 0) AS balance',
                'DATEDIFF(Now(), dateofadded) AS days',
            )
            ->from('userBalanceHistory', 'b')
            ->where('b.userID = :userID')
            ->setParameter('userID', $user->getId())
            ->groupBy('DATEDIFF(Now(), dateofadded)')
            ->orderBy('DATEDIFF(Now(), dateofadded)', 'DESC');
        $arr = $qb->executeQuery()->fetchAllAssociative();


        foreach ($arr as $item) {
            $sum += $item['balance'];
            if ($sum >= 0) $countDays = $item['days'];

        }
        return $countDays;
    }

    /**
     * @param User $user
     * @param Filter\UserBalanceAct\Filter $filter
     * @return array
     * @throws Exception
     */
    public function act(User $user, Filter\UserBalanceAct\Filter $filter): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'b.balanceID',
                'b.firmID',
                'b.userID',
                'f.name_short AS firm_name',
                'u.name AS user_name',
                'b.dateofadded',
                'b.balance',
                'b.description',
                'id.document_num',
                'id.dateofadded AS document_date',
                'ft.name AS finance_type',
            )
            ->from('userBalanceHistory', 'b')
            ->innerJoin('b', 'firms', 'f', 'b.firmID = f.firmID')
            ->innerJoin('b', 'users', 'u', 'b.userID = u.userID')
            ->innerJoin('b', 'finance_types', 'ft', 'b.finance_typeID = ft.finance_typeID')
            ->leftJoin('b', 'expenseDocuments', 'id', 'b.expenseDocumentID = id.expenseDocumentID');

        if ($filter->firmID) {
            $qb->andWhere('b.firmID = :firmID');
            $qb->setParameter('firmID', $filter->firmID);
        }

        if ($filter->userID) {
            $qb->andWhere('b.userID = :userID');
            $qb->setParameter('userID', $filter->userID);
        }

        $qb->andWhere($qb->expr()->gte('b.dateofadded', ':date_from'));
        $qb->setParameter('date_from', (new DateTime($filter->dateofadded['date_from']))->format('Y-m-d 00:00:00'));
        $qb->andWhere($qb->expr()->lte('b.dateofadded', ':date_till'));
        $qb->setParameter('date_till', (new DateTime($filter->dateofadded['date_till']))->format('Y-m-d 23:59:59'));

        $qb->orderBy('b.dateofadded');

        $balance = $qb->executeQuery()->fetchAllAssociative();

        $balance_plus = 0;
        $balance_minus = 0;
        foreach ($balance as $item) {
            if ($item["balance"] > 0) $balance_plus += $item["balance"];
            if ($item["balance"] < 0) $balance_minus += $item["balance"];
        }

        $qb = $this->connection->createQueryBuilder()
            ->select('Sum(balance)')
            ->from('userBalanceHistory', 'b');
        if ($filter->firmID) {
            $qb->andWhere('b.firmID = :firmID');
            $qb->setParameter('firmID', $filter->firmID);
        }
        if ($filter->userID) {
            $qb->andWhere('b.userID = :userID');
            $qb->setParameter('userID', $filter->userID);
        }
        $qb->andWhere($qb->expr()->lt('b.dateofadded', ':date_till'));
        $qb->setParameter('date_till', (new DateTime($filter->dateofadded['date_from']))->format('Y-m-d 00:00:00'));
        $saldo_from = $qb->executeQuery()->fetchOne();


        $qb = $this->connection->createQueryBuilder()
            ->select('Sum(balance)')
            ->from('userBalanceHistory', 'b');
        if ($filter->firmID) {
            $qb->andWhere('b.firmID = :firmID');
            $qb->setParameter('firmID', $filter->firmID);
        }
        if ($filter->userID) {
            $qb->andWhere('b.userID = :userID');
            $qb->setParameter('userID', $filter->userID);
        }
        $qb->andWhere($qb->expr()->lte('b.dateofadded', ':date_till'));
        $qb->setParameter('date_till', (new DateTime($filter->dateofadded['date_till']))->format('Y-m-d 23:59:59'));
        $saldo_till = $qb->executeQuery()->fetchOne();

        $result = [
            'saldo_from' => $saldo_from,
            'saldo_till' => $saldo_till,
            'balance_plus' => $balance_plus,
            'balance_minus' => $balance_minus,
            'balance' => $balance
        ];

        return $result;
    }
}