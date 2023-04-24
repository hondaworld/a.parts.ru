<?php


namespace App\ReadModel\Detail;


use App\Model\Detail\Entity\PriceExclude\DetailProviderPriceExclude;
use App\Model\EntityNotFoundException;
use App\ReadModel\Detail\Filter\PriceExclude\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class DetailProviderPriceExcludeFetcher
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
        $this->repository = $em->getRepository(DetailProviderPriceExclude::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): DetailProviderPriceExclude
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
                'e.providerPriceID',
                'c.name AS creater',
                "CONCAT(p.name, ' ', p.description) AS providerPrice"
            )
            ->from('numberPricesExclude', 'e')
            ->innerJoin('e', 'creaters', 'c', 'c.createrID = e.createrID')
            ->innerJoin('e', 'providerPrices', 'p', 'p.providerPriceID = e.providerPriceID')
        ;

        if ($filter->number) {
            $qb->andWhere($qb->expr()->like('e.number', ':number'));
            $qb->setParameter('number', mb_strtolower($filter->number) . '%');
        }

        if ($filter->createrID) {
            $qb->andWhere('e.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        if ($filter->providerPriceID) {
            $qb->andWhere('e.providerPriceID = :providerPriceID');
            $qb->setParameter('providerPriceID', $filter->providerPriceID);
        }

        $sort = isset($settings['sort']) ? $settings['sort'] : self::DEFAULT_SORT_FIELD_NAME;
        $direction = isset($settings['direction']) ? $settings['direction'] : self::DEFAULT_SORT_DIRECTION;
        $size = isset($settings['inPage']) ? $settings['inPage'] : self::PER_PAGE;

        if (!in_array($sort, ['number', 'creater', 'providerPrice'], true)) {
            $sort = 'number';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param string $number
     * @param int $createrID
     * @param int $providerPriceID
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    public function hasProviderPriceExclude(string $number, int $createrID, int $providerPriceID): int
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('Count(e.excludeID)')
            ->from('numberPricesExclude', 'e')
            ->andWhere('e.number = :number')
            ->setParameter('number', $number)
            ->andWhere('e.createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->andWhere('e.providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPriceID)
            ->executeQuery();

        return $stmt->fetchOne();
    }
}