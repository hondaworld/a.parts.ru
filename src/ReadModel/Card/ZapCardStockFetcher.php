<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Measure\EdIzm;
use App\Model\Card\Entity\Stock\ZapCardStock;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ZapCardStockFetcher
{
    private $connection;
    private $paginator;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'name';
    public const DEFAULT_SORT_DIRECTION = 'asc';

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapCardStock ::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ZapCardStock
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('stockID, name')
            ->from('zapCardStocks')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    /**
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'stockID',
                'name',
                'dateofadded',
                'isHide',
            )
            ->from('zapCardStocks', 's')
        ;

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['name', 'dateofadded'], true)) {
            $sort = 'name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, 10000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

}