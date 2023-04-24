<?php


namespace App\ReadModel\Shop;


use App\Model\Shop\Entity\Gtd\ShopGtd;
use App\ReadModel\Shop\Filter\Gtd\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ShopGtdFetcher
{
    private $connection;
    private $repository;
    private $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'name';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ShopGtd::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ShopGtd
    {
        return $this->repository->get($id);
    }

    public function findGtd(string $name): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('name')
            ->from('shop_gtd')
            ->where('name like :name')
            ->setParameter("name", $name . '%')
            ->orderBy('name')
            ->setMaxResults(10)
            ->executeQuery();

//        $stmt->setFetchMode(\PDO::FETCH_COLUMN, 0);
//        $result = $qb->fetchAll();

        return $stmt->fetchFirstColumn();
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'shop_gtdID',
                'name'
            )
            ->from('shop_gtd')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(Filter $filter, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'g.shop_gtdID',
                'g.name',
            )
            ->from('shop_gtd', 'g');

        if ($filter->name) {
            $qb->andWhere($qb->expr()->like('REPLACE(g.name, "/", "")', ':name'));
            $qb->setParameter('name', '%' . mb_strtolower(str_replace('/', '', $filter->name)) . '%');
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['name'], true)) {
            $sort = 'name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

}