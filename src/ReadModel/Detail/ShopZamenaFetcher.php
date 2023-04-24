<?php


namespace App\ReadModel\Detail;


use App\Model\Detail\Entity\Zamena\ShopZamena;
use App\Model\EntityNotFoundException;
use App\ReadModel\Detail\Filter\ShopZamena\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class ShopZamenaFetcher
{
    private $connection;
    private $paginator;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'number';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ShopZamena::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ShopZamena
    {
        if (!$shopZamena = $this->repository->find($id)) {
            throw new EntityNotFoundException('Замена не найдена');
        }
        return $shopZamena;
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface|null
     */
    public function all(Filter $filter, int $page, array $settings): ?PaginationInterface
    {
        if (!$filter->number && !$filter->number2 && !$filter->createrID && !$filter->createrID2) return null;

        $qb = $this->connection->createQueryBuilder()
            ->select(
                's.shopZamenaID',
                's.createrID',
                's.createrID2',
                's.number',
                's.number2',
                'c.name AS creater',
                'c2.name AS creater2',
                'm.name AS manager'
            )
            ->from('shopZamena', 's')
            ->innerJoin('s', 'creaters', 'c', 'c.createrID = s.createrID')
            ->innerJoin('s', 'creaters', 'c2', 'c2.createrID = s.createrID2')
            ->leftJoin('s', 'managers', 'm', 'm.managerID = s.managerID')
        ;

        if ($filter->number) {
            $qb->andWhere($qb->expr()->like('s.number', ':number'));
            $qb->setParameter('number', mb_strtolower($filter->number) . '%');
        }

        if ($filter->number2) {
            $qb->andWhere($qb->expr()->like('s.number2', ':number2'));
            $qb->setParameter('number2', mb_strtolower($filter->number2) . '%');
        }

        if ($filter->createrID) {
            $qb->andWhere('s.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        if ($filter->createrID2) {
            $qb->andWhere('s.createrID2 = :createrID2');
            $qb->setParameter('createrID2', $filter->createrID2);
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['number', 'number2', 'creater', 'creater2'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'number';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param string $number
     * @param int $createrID
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByNumberAndCreater(string $number, int $createrID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                's.shopZamenaID',
                's.createrID2',
                's.number2',
                'c2.name AS creater2',
                'c2.tableName',
                'c2.isOriginal'
            )
            ->from('shopZamena', 's')
            ->innerJoin('s', 'creaters', 'c2', 'c2.createrID = s.createrID2')
            ->andWhere('s.number = :number')
            ->setParameter('number', $number)
            ->andWhere('s.createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->orderBy('s.number2')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    /**
     * @param string $number
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByNumber(string $number): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                's.shopZamenaID',
                's.createrID2',
                's.number2',
                'c2.name AS creater2',
                'c2.tableName',
                'c2.isOriginal'
            )
            ->from('shopZamena', 's')
            ->innerJoin('s', 'creaters', 'c2', 'c2.createrID = s.createrID2')
            ->andWhere('s.number = :number')
            ->setParameter('number', $number)
            ->orderBy('s.number2')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    /**
     * @param string $number
     * @param int $createrID
     * @param string $number2
     * @param int $createrID2
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    public function hasZamena(string $number, int $createrID, string $number2, int $createrID2): int
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('Count(s.shopZamenaID)')
            ->from('shopZamena', 's')
            ->andWhere('s.number = :number')
            ->setParameter('number', $number)
            ->andWhere('s.createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->andWhere('s.number2 = :number2')
            ->setParameter('number2', $number2)
            ->andWhere('s.createrID2 = :createrID2')
            ->setParameter('createrID2', $createrID2)
            ->executeQuery();

        return $stmt->fetchOne();
    }
}