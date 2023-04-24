<?php


namespace App\ReadModel\Firm;


use App\Model\EntityNotFoundException;
use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Provider\Entity\Provider\Provider;
use App\ReadModel\Firm\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class FirmBalanceHistoryFetcher
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
        $this->repository = $em->getRepository(FirmBalanceHistory::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): FirmBalanceHistory
    {

        if (!$firmBalanceHistory = $this->repository->find($id)) {
            throw new EntityNotFoundException('Запись не найдена');
        }

        return $firmBalanceHistory;
    }

    /**
     * @param Firm $firm
     * @param Filter\FirmBalanceHistory\Filter $filter
     * @param int $page
     * @param array $settings
     * @param bool $isPrint
     * @return PaginationInterface
     * @throws \Exception
     */
    public function allByFirm(Firm $firm, Filter\FirmBalanceHistory\Filter $filter, int $page, array $settings, bool $isPrint = false): PaginationInterface
    {

//        SELECT a.*, a.balance AS balance_plus, a.balance AS balance_minus, b.name AS provider, c.name_short AS firm
//	FROM firmBalanceHistory a
//	INNER JOIN providers b ON a.providerID = b.providerID
//	INNER JOIN firms c ON a.firmID = c.firmID
//	WHERE $where

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'b.balanceID',
                'b.providerID',
                'p.name AS provider_name',
                'b.dateofadded',
                'b.balance',
                'b.balance_nds',
                'b.description',
                'id.document_num',
            )
            ->from('firmBalanceHistory', 'b')
            ->innerJoin('b', 'providers', 'p', 'b.providerID = p.providerID')
            ->leftJoin('b', 'incomeDocuments', 'id', 'b.incomeDocumentID = id.incomeDocumentID')
            ->where('b.firmID = :firmID')
            ->setParameter('firmID', $firm->getId());

        if ($filter->providerID) {
            $qb->andWhere('b.providerID = :providerID');
            $qb->setParameter('providerID', $filter->providerID);
        }

        if ($filter->dateofadded) {
            if ($filter->dateofadded['date_from']) {
                $qb->andWhere($qb->expr()->gte('b.dateofadded', ':date_from'));
                $qb->setParameter('date_from', (new \DateTime($filter->dateofadded['date_from']))->format('Y-m-d 00:00:00'));
            }
            if ($filter->dateofadded['date_till']) {
                $qb->andWhere($qb->expr()->lte('b.dateofadded', ':date_till'));
                $qb->setParameter('date_till', (new \DateTime($filter->dateofadded['date_till']))->format('Y-m-d 23:59:59'));
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;
        if ($isPrint) {
            $page = 1;
            $size = 100000000;
        }

        if (!in_array($sort, ['dateofadded', 'provider_name'], true)) {
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param Provider $provider
     * @param Filter\ProviderBalanceHistory\Filter $filter
     * @param int $page
     * @param array $settings
     * @param bool $isPrint
     * @return PaginationInterface
     * @throws \Exception
     */
    public function allByProvider(Provider $provider, Filter\ProviderBalanceHistory\Filter $filter, int $page, array $settings, bool $isPrint = false): PaginationInterface
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
                'b.balance_nds',
                'b.description',
                'id.document_num',
            )
            ->from('firmBalanceHistory', 'b')
            ->innerJoin('b', 'firms', 'f', 'b.firmID = f.firmID')
            ->innerJoin('b', 'users', 'u', 'b.userID = u.userID')
            ->leftJoin('b', 'incomeDocuments', 'id', 'b.incomeDocumentID = id.incomeDocumentID')
            ->where('b.providerID = :providerID')
            ->setParameter('providerID', $provider->getId());

        if ($filter->firmID) {
            $qb->andWhere('b.firmID = :firmID');
            $qb->setParameter('firmID', $filter->firmID);
        }

        if ($filter->userID) {
            $qb->andWhere('b.userID = :userID');
            $qb->setParameter('userID', $filter->userID);
        }

        if ($filter->dateofadded) {
            if ($filter->dateofadded['date_from']) {
                $qb->andWhere($qb->expr()->gte('b.dateofadded', ':date_from'));
                $qb->setParameter('date_from', (new \DateTime($filter->dateofadded['date_from']))->format('Y-m-d 00:00:00'));
            }
            if ($filter->dateofadded['date_till']) {
                $qb->andWhere($qb->expr()->lte('b.dateofadded', ':date_till'));
                $qb->setParameter('date_till', (new \DateTime($filter->dateofadded['date_till']))->format('Y-m-d 23:59:59'));
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;
        if ($isPrint) {
            $page = 1;
            $size = 100000000;
        }

        if (!in_array($sort, ['dateofadded', 'firm_name', 'user_name'], true)) {
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param Provider $provider
     * @param Filter\ProviderBalanceAct\Filter $filter
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function act(Provider $provider, Filter\ProviderBalanceAct\Filter $filter): array
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
                'b.balance_nds',
                'b.description',
                'id.document_num',
                'id.dateofadded AS document_date',
            )
            ->from('firmBalanceHistory', 'b')
            ->innerJoin('b', 'firms', 'f', 'b.firmID = f.firmID')
            ->innerJoin('b', 'users', 'u', 'b.userID = u.userID')
            ->leftJoin('b', 'incomeDocuments', 'id', 'b.incomeDocumentID = id.incomeDocumentID');

        if ($filter->firmID) {
            $qb->andWhere('b.firmID = :firmID');
            $qb->setParameter('firmID', $filter->firmID);
        }

        if ($filter->userID) {
            $qb->andWhere('b.userID = :userID');
            $qb->setParameter('userID', $filter->userID);
        }

        $qb->andWhere($qb->expr()->gte('b.dateofadded', ':date_from'));
        $qb->setParameter('date_from', (new \DateTime($filter->dateofadded['date_from']))->format('Y-m-d 00:00:00'));
        $qb->andWhere($qb->expr()->lte('b.dateofadded', ':date_till'));
        $qb->setParameter('date_till', (new \DateTime($filter->dateofadded['date_till']))->format('Y-m-d 23:59:59'));

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
            ->from('firmBalanceHistory', 'b');
        if ($filter->firmID) {
            $qb->andWhere('b.firmID = :firmID');
            $qb->setParameter('firmID', $filter->firmID);
        }
        if ($filter->userID) {
            $qb->andWhere('b.userID = :userID');
            $qb->setParameter('userID', $filter->userID);
        }
        $qb->andWhere($qb->expr()->lt('b.dateofadded', ':date_till'));
        $qb->setParameter('date_till', (new \DateTime($filter->dateofadded['date_from']))->format('Y-m-d 00:00:00'));
        $saldo_from = $qb->executeQuery()->fetchOne();


        $qb = $this->connection->createQueryBuilder()
            ->select('Sum(balance)')
            ->from('firmBalanceHistory', 'b');
        if ($filter->firmID) {
            $qb->andWhere('b.firmID = :firmID');
            $qb->setParameter('firmID', $filter->firmID);
        }
        if ($filter->userID) {
            $qb->andWhere('b.userID = :userID');
            $qb->setParameter('userID', $filter->userID);
        }
        $qb->andWhere($qb->expr()->lte('b.dateofadded', ':date_till'));
        $qb->setParameter('date_till', (new \DateTime($filter->dateofadded['date_till']))->format('Y-m-d 23:59:59'));
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

    /**
     * @param Firm $firm
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function balanceByFirm(Firm $firm): array
    {

//        SELECT a.*, b.name AS provider
//		FROM firmBalanceHistory a
//		INNER JOIN providers b ON a.providerID = b.providerID
//		WHERE b.isHide = 0
//		GROUP BY a.providerID
//		ORDER BY provider

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'SUM(b.balance) AS balance',
                'p.name AS provider_name'
            )
            ->from('firmBalanceHistory', 'b')
            ->innerJoin('b', 'providers', 'p', 'b.providerID = p.providerID')
            ->andWhere('b.firmID = :firmID')
            ->setParameter('firmID', $firm->getId())
            ->andWhere('p.isHide = 0')
            ->orderBy('provider_name')
            ->groupBy('p.providerID');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param Provider $provider
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function balanceByProvider(Provider $provider): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'SUM(b.balance) AS balance',
                'f.name_short AS firm_name',
                'u.userID'
            )
            ->from('firmBalanceHistory', 'b')
            ->innerJoin('b', 'firms', 'f', 'b.firmID = f.firmID')
            ->innerJoin('b', 'users', 'u', 'b.userID = u.userID')
            ->andWhere('b.providerID = :providerID')
            ->setParameter('providerID', $provider->getId())
            ->orderBy('firm_name')
            ->groupBy('b.firmID')
            ->addGroupBy('b.userID');

        $arr = $qb->executeQuery()->fetchAllAssociative();
        $result = [];

        foreach ($arr as $item) {
            $result[$item['firm_name']][$item['userID']] = $item['balance'];
        }

        return $result;
    }


}