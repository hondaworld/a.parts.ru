<?php


namespace App\ReadModel\Firm;


use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Firm\Entity\Schet\Schet;
use App\ReadModel\Firm\Filter\Schet\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use function Doctrine\DBAL\Query\QueryBuilder;

class SchetFetcher
{
    private $connection;
    private $repository;
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Schet ::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): Schet
    {
        return $this->repository->get($id);
    }

    public function findByIds(array $arIds): array
    {
        if (!$arIds) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.schetID',
                'a.schet_num',
                'a.document_prefix',
                'a.document_sufix',
                'a.dateofadded',
                'a.status',
                'a.finance_typeID',
            )
            ->from('schet', 'a');
        $qb->andWhere($qb->expr()->in('a.schetID', $arIds));

        return $qb->executeQuery()->fetchAllAssociativeIndexed();
    }

    public function isExist(int $firmID): bool
    {
        $schetNumber = $this->connection->createQueryBuilder()
            ->select('Count(*)')
            ->from('schet', 'a')
            ->andWhere('a.firmID = :firmID')
            ->setParameter('firmID', $firmID)
            ->executeQuery()
            ->fetchOne();
        return $schetNumber > 0;
    }

    public function getNextNumber(int $firmID, int $finance_typeID): int
    {
        $schetNumber = $this->connection->createQueryBuilder()
            ->select(
                'ifnull(Max(schet_num), 0)',
            )
            ->from('schet', 'a')
            ->andWhere('a.firmID = :firmID')
            ->setParameter('firmID', $firmID)
            ->andWhere('a.finance_typeID = :finance_typeID')
            ->setParameter('finance_typeID', $finance_typeID)
            ->andWhere('YEAR(dateofadded) = ' . (new \DateTime())->format('Y') . '')
            ->executeQuery()
            ->fetchOne();
        return $schetNumber + 1;
    }

    /**
     * @param int $userID
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSumGoodsNewSchetByUser(int $userID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "Count(b.quantity) AS qnt",
                "SUM(ROUND(b.price - b.price * b.discount / 100) * b.quantity) AS sum"
            )
            ->from('schet', 'a')
            ->innerJoin('a', 'order_goods', 'b', 'a.schetID = b.schetID')
            ->where('a.status = :status')
            ->setParameter('status', Schet::NEW)
            ->andWhere('a.userID = :userID')
            ->setParameter('userID', $userID);

        return $qb->executeQuery()->fetchAssociative();
    }

    /**
     * @param int $userID
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByUser(int $userID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'a.schetID',
                'a.schet_num',
                'a.dateofadded',
                'b.name_short AS firm_name',
                '(SELECT SUM(price * quantity) FROM schet_goods WHERE schetID = a.schetID) AS sum'
            )
            ->from('schet', 'a')
            ->innerJoin('a', 'firms', 'b', 'a.firmID = b.firmID')
            ->andWhere('a.userID = :userID')
            ->setParameter('userID', $userID)
            ->andWhere('a.status = :status')
            ->setParameter('status', Schet::PAID)
            ->orderBy('dateofadded', 'DESC')
            ->setMaxResults(15)
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     * @throws \Exception
     */
    public function all(Filter $filter, int $page, array $settings): PaginationInterface
    {
//        SELECT a.*, s.name AS user, c.name AS firm, d.name AS status_name, f.name AS finance_type
//		FROM schet a
//		INNER JOIN finance_types f ON a.finance_typeID = f.finance_typeID
//		INNER JOIN schet_statuses d ON a.status = d.status
//		INNER JOIN users b ON a.userID = s.userID
//		INNER JOIN firms c ON a.firmID = c.firmID
//		WHERE $where

        $qb = $this->connection->createQueryBuilder()
            ->select(
                's.schetID',
                's.firmID',
                's.finance_typeID',
                'f.name_short AS firm_name',
                's.dateofadded',
                'If(s.dateofpaid = 0, NULL, s.dateofpaid) AS dateofpaid',
                's.status',
                'ft.name AS finance_type',
                's.schet_num',
                's.userID',
                'u.name AS user_name',
                's.comment',
                's.summ',
                '(SELECT SUM(price * quantity) FROM schet_goods WHERE schetID = s.schetID) AS sum_goods'
            )
            ->from('schet', 's')
            ->innerJoin('s', 'finance_types', 'ft', 's.finance_typeID = ft.finance_typeID')
            ->innerJoin('s', 'firms', 'f', 's.firmID = f.firmID')
            ->innerJoin('s', 'users', 'u', 's.userID = u.userID')
        ;

        if ($filter->firmID) {
            $qb->andWhere('s.firmID = :firmID');
            $qb->setParameter('firmID', $filter->firmID);
        }

        if ($filter->finance_typeID) {
            $qb->andWhere('s.finance_typeID = :finance_typeID');
            $qb->setParameter('finance_typeID', $filter->finance_typeID);
        }

        if ($filter->status) {
            $qb->andWhere('s.status = :status');
            $qb->setParameter('status', $filter->status);
        }

        if ($filter->user_name) {
            $qb->andWhere($qb->expr()->like('u.name', ':user_name'));
            $qb->setParameter('user_name', '%' . mb_strtolower($filter->user_name) . '%');
        }

        if ($filter->schet_num) {
            $qb->andWhere('s.schet_num = :schet_num');
            $qb->setParameter('schet_num', $filter->schet_num);
        }

        if ($filter->isShowCanceled) {
            $qb->andWhere($qb->expr()->in('s.status', [Schet::NOT_PAID, Schet::PAID, Schet::CANCELED]));
        } else {
            $qb->andWhere($qb->expr()->in('s.status', [Schet::NOT_PAID, Schet::PAID]));
        }

        if ($filter->dateofadded) {
            if ($filter->dateofadded['date_from']) {
                $qb->andWhere($qb->expr()->gte('s.dateofadded', ':date_from'));
                $qb->setParameter('date_from', (new \DateTime($filter->dateofadded['date_from']))->format('Y-m-d 00:00:00'));
            }
            if ($filter->dateofadded['date_till']) {
                $qb->andWhere($qb->expr()->lte('s.dateofadded', ':date_till'));
                $qb->setParameter('date_till', (new \DateTime($filter->dateofadded['date_till']))->format('Y-m-d 23:59:59'));
            }
        }

        if ($filter->dateofpaid) {
//            $qb->andWhere('s.dateofpaid IS NOT NULL');
            if ($filter->dateofpaid['date_from']) {
                $qb->andWhere($qb->expr()->gte('s.dateofpaid', ':date_from'));
                $qb->setParameter('date_from', (new \DateTime($filter->dateofpaid['date_from']))->format('Y-m-d 00:00:00'));
            }
            if ($filter->dateofpaid['date_till']) {
                $qb->andWhere($qb->expr()->lte('s.dateofpaid', ':date_till'));
                $qb->setParameter('date_till', (new \DateTime($filter->dateofpaid['date_till']))->format('Y-m-d 23:59:59'));
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['dateofadded', 'dateofpaid', 'user_name', 'firm_name', 'schet_num', 'finance_type'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param int $document_num
     * @param int $year
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByDocumentNumAndYear(int $document_num, int $year): array
    {
        return $this->connection->createQueryBuilder()
            ->select(
                DocumentType::S . ' AS doc_typeID',
                's.schetID AS id',
                's.firmID',
                'f.name_short AS from_name',
                's.dateofadded',
                's.schet_num AS document_num',
                's.userID',
                'u.name AS to_name',
                's.finance_typeID',
                '(SELECT SUM(price * quantity) FROM schet_goods WHERE schetID = s.schetID) AS sum'
            )
            ->from('schet', 's')
            ->innerJoin('s', 'finance_types', 'ft', 's.finance_typeID = ft.finance_typeID')
            ->innerJoin('s', 'firms', 'f', 's.firmID = f.firmID')
            ->innerJoin('s', 'users', 'u', 's.userID = u.userID')
            ->andWhere('s.schet_num = :schet_num')
            ->setParameter('schet_num', $document_num)
            ->andWhere("YEAR(s.dateofadded) = :year")
            ->setParameter('year', $year)
            ->orderBy('s.dateofadded', 'desc')
            ->executeQuery()
            ->fetchAllAssociative();
    }

}