<?php


namespace App\ReadModel\Detail;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Weight\Weight;
use App\Model\EntityNotFoundException;
use App\ReadModel\Detail\Filter\Weight\Filter;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class WeightFetcher
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
        $this->repository = $em->getRepository(Weight::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): Weight
    {
        if (!$weight = $this->repository->find($id)) {
            throw new EntityNotFoundException('Вес не найден');
        }
        return $weight;
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface|null
     */
    public function all(Filter $filter, int $page, array $settings): ?PaginationInterface
    {
        if (!$filter->number && !$filter->createrID) return null;

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'w.weightID',
                'w.createrID',
                'w.number',
                'c.name AS creater',
                'w.weight',
                'w.weightIsReal'
            )
            ->from('weights', 'w')
            ->innerJoin('w', 'creaters', 'c', 'c.createrID = w.createrID');

        if ($filter->number) {
            $number = (new DetailNumber($filter->number))->getValue();
            $qb->andWhere($qb->expr()->like('w.number', ':number'));
            $qb->setParameter('number', $number . '%');
        }

        if ($filter->createrID) {
            $qb->andWhere('w.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['number', 'creater'], true)) {
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
     */
    public function allByNumberAndCreater(string $number, int $createrID): array
    {
        try {
            $stmt = $this->connection->createQueryBuilder()
                ->select(
                    'w.weightID',
                    'w.weight',
                    'w.weightIsReal'
                )
                ->from('weights', 'w')
                ->andWhere('w.number = :number')
                ->setParameter('number', $number)
                ->andWhere('w.createrID = :createrID')
                ->setParameter('createrID', $createrID)
                ->executeQuery();
            return $stmt->fetchAllAssociative();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Вес по номеру и производителю
     *
     * @param string $number
     * @param int $createrID
     * @return array|null
     */
    public function oneByNumberAndCreater(string $number, int $createrID): ?array
    {
        $weights = $this->allByNumberAndCreater($number, $createrID);
        return $weights ? $weights[0] : null;
    }

    /**
     * @param string $number
     * @param int $createrID
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    public function hasWeight(string $number, int $createrID): int
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('Count(w.weightID)')
            ->from('weights', 'w')
            ->andWhere('w.number = :number')
            ->setParameter('number', $number)
            ->andWhere('w.createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->executeQuery();

        return $stmt->fetchOne();
    }

    public function updateWeight(float $weight, int $weightID): void
    {
        $qb = $this->connection->createQueryBuilder()
            ->update('weights')
            ->set('weight', ':weight')
            ->setParameter('weight', $weight)
            ->where('weightID = :weightID')
            ->setParameter('weightID', $weightID);
        $qb->executeStatement();
    }

    public function insertWeight(array $arr): int
    {
        $values = [];
        foreach (array_keys($arr) as $key) {
            $values[$key] = '?';
        }
        $qb = $this->connection->createQueryBuilder()
            ->insert('weights')
            ->values($values)
            ->setParameters(array_values($arr));

        return $qb->executeStatement();
    }
}