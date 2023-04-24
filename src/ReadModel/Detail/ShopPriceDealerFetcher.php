<?php


namespace App\ReadModel\Detail;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Dealer\ShopPriceDealer;
use App\Model\EntityNotFoundException;
use App\ReadModel\Detail\Filter\shopPriceDealer\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class ShopPriceDealerFetcher
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
        $this->repository = $em->getRepository(ShopPriceDealer::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ShopPriceDealer
    {
        if (!$shopPriceDealer = $this->repository->find($id)) {
            throw new EntityNotFoundException('Цена не найдена');
        }
        return $shopPriceDealer;
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
                'd.shopPriceDealerID',
                'd.createrID',
                'd.number',
                'c.name AS creater',
                'd.price'
            )
            ->from('shopPriceDealer', 'd')
            ->innerJoin('d', 'creaters', 'c', 'c.createrID = d.createrID');

        if ($filter->number) {
            $number = (new DetailNumber($filter->number))->getValue();
            $qb->andWhere($qb->expr()->like('d.number', ':number'));
            $qb->setParameter('number', $number . '%');
        }

        if ($filter->createrID) {
            $qb->andWhere('d.createrID = :createrID');
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
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByNumberAndCreater(string $number, int $createrID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'd.shopPriceDealerID',
                'd.price'
            )
            ->from('shopPriceDealer', 'd')
            ->andWhere('d.number = :number')
            ->setParameter('number', $number)
            ->andWhere('d.createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    /**
     * @param string $number
     * @param int $createrID
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    public function hasDealerPrice(string $number, int $createrID): int
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('Count(d.shopPriceDealerID)')
            ->from('shopPriceDealer', 'd')
            ->andWhere('d.number = :number')
            ->setParameter('number', $number)
            ->andWhere('d.createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->executeQuery();

        return $stmt->fetchOne(0);
    }

    /**
     * @param int $createrID
     * @param string|null $number
     * @return void
     * @throws \Doctrine\DBAL\Exception
     */
    public function deleteByCreaterAndNumber(int $createrID, string $number = null): void
    {
        $stmt = $this->connection->createQueryBuilder()
            ->delete('shopPriceDealer')
            ->andWhere('createrID = :createrID')
            ->setParameter('createrID', $createrID);
        if ($number != null) {
            $stmt->andWhere('number = :number')
                ->setParameter('number', $number);
        }
        $stmt->executeQuery();
    }
}