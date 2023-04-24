<?php


namespace App\ReadModel\Detail;


use App\Model\Detail\Entity\ProviderExclude\DetailProviderExclude;
use App\Model\EntityNotFoundException;
use App\ReadModel\Detail\Filter\ProviderExclude\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;
use function Doctrine\DBAL\Query\QueryBuilder;

class DetailProviderExcludeFetcher
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
        $this->repository = $em->getRepository(DetailProviderExclude::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): DetailProviderExclude
    {
        if (!$detailProviderExclude = $this->repository->find($id)) {
            throw new EntityNotFoundException('Регион не найден');
        }
        return $detailProviderExclude;
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface|null
     */
    public function all(Filter $filter, int $page, array $settings): ?PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'e.excludeID',
                'e.createrID',
                'e.number',
                'e.providerID',
                'e.comment',
                'c.name AS creater',
                "if (e.providerID = -1, 'Все', p.name) AS provider"
            )
            ->from('numberDaysExclude', 'e')
            ->innerJoin('e', 'creaters', 'c', 'c.createrID = e.createrID')
            ->leftJoin('e', 'providers', 'p', 'p.providerID = e.providerID')
        ;

        if ($filter->number) {
            $qb->andWhere($qb->expr()->like('e.number', ':number'));
            $qb->setParameter('number', mb_strtolower($filter->number) . '%');
        }

        if ($filter->createrID) {
            $qb->andWhere('e.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        if ($filter->providerID) {
            $qb->andWhere('e.providerID = :providerID');
            $qb->setParameter('providerID', $filter->providerID);
        }

        $sort = isset($settings['sort']) ? $settings['sort'] : self::DEFAULT_SORT_FIELD_NAME;
        $direction = isset($settings['direction']) ? $settings['direction'] : self::DEFAULT_SORT_DIRECTION;
        $size = isset($settings['inPage']) ? $settings['inPage'] : self::PER_PAGE;

        if (!in_array($sort, ['number', 'creater', 'provider'], true)) {
            $sort = 'number';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param string $number
     * @param int $createrID
     * @param int $providerID
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    public function hasProviderExclude(string $number, int $createrID, int $providerID): int
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('Count(e.excludeID)')
            ->from('numberDaysExclude', 'e')
            ->andWhere('e.number = :number')
            ->setParameter('number', $number)
            ->andWhere('e.createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->andWhere('e.providerID = :providerID')
            ->setParameter('providerID', $providerID)
            ->executeQuery();

        return $stmt->fetchOne();
    }

    /**
     * @param string $number
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByNumber(string $number): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('e.providerID', 'e.createrID')
            ->from('numberDaysExclude', 'e')
            ->andWhere('e.number = :number')
            ->setParameter('number', $number)
            ->executeQuery();
        $result = $stmt->fetchAllAssociative();

        $arr = [];

        foreach ($result as $item) {
            if ($item['providerID'] != -1)
                $arr[$item['createrID']]['disableDays'][] = $item['providerID'];
            else
                $arr[$item['createrID']]["isProvider"] = 1;
        }

        return $arr;
    }

    /**
     * @param array $providerPrices
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByProviderPrices(array $providerPrices): array
    {
        if (!$providerPrices) return [];

        $stmt = $this->connection->createQueryBuilder()
            ->select('e.providerID', 'e.createrID', 'e.number')
            ->from('numberDaysExclude', 'e')
            ->innerJoin('e', 'providerPrices', 'pp', 'e.providerID = pp.providerID')
            ;
        $stmt->andWhere($stmt->expr()->in('pp.providerPriceID', $providerPrices));
        $result = $stmt->executeQuery()->fetchAllAssociative();

        $arr = [];

        foreach ($result as $item) {
            if ($item['providerID'] != -1)
                $arr[$item['createrID']][$item['number']]['disableDays'][] = $item['providerID'];
            else
                $arr[$item['createrID']][$item['number']]["isProvider"] = 1;
        }

        return $arr;
    }

    /**
     * @param string $number
     * @param int $createrID
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByNumberAndCreater(string $number, int $createrID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('e.providerID', 'e.createrID')
            ->from('numberDaysExclude', 'e')
            ->andWhere('e.number = :number')
            ->setParameter('number', $number)
            ->andWhere('e.createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->executeQuery();
        $result = $stmt->fetchAllAssociative();

        $arr = [];

        foreach ($result as $item) {
            if ($item['providerID'] != -1)
                $arr[$item['createrID']]['disableDays'][] = $item['providerID'];
            else
                $arr[$item['createrID']]["isProvider"] = 1;
        }

        return $arr;
    }
}