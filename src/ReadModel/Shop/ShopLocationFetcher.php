<?php


namespace App\ReadModel\Shop;


use App\Model\Shop\Entity\Location\ShopLocation;
use App\ReadModel\Shop\Filter\Location\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ShopLocationFetcher
{
    private $connection;
    private $repository;
    private $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'name_short';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ShopLocation::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ShopLocation
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'locationID',
                'name_short'
            )
            ->from('shopLocation')
            ->orderBy('name_short')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function assocByZapCardID(int $zapCardID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'zs.name_short',
                'l.name_short'
            )
            ->from('shopLocation', 'l')
            ->innerJoin('l', 'zapSkladLocation', 'zsl', 'l.locationID = zsl.locationID')
            ->innerJoin('zsl', 'zapSklad', 'zs', 'zsl.zapSkladID = zs.zapSkladID')
            ->where('zsl.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->orderBy('zs.name_short')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function findByZapCards(array $zapCards): array
    {
        if (!$zapCards) return [];
        $arr = [];

        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'zsl.zapCardID',
                'zs.name_short AS sklad_name',
                'l.name_short',
                'zs.zapSkladID'
            )
            ->from('shopLocation', 'l')
            ->innerJoin('l', 'zapSkladLocation', 'zsl', 'l.locationID = zsl.locationID')
            ->innerJoin('zsl', 'zapSklad', 'zs', 'zsl.zapSkladID = zs.zapSkladID')
            ->where('zsl.zapCardID IN (' . implode(',', $zapCards) . ')')
            ->orderBy('zsl.zapCardID, zs.name_short')
            ;
        $items = $stmt->executeQuery()->fetchAllAssociative();
        if ($items) {
            foreach ($items as $item) {
                $arr[$item['zapCardID']][$item['zapSkladID']] = [
                    'sklad_name' => $item['sklad_name'],
                    'location' => $item['name_short']
                ];
            }
        }

        return $arr;
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
                'l.locationID',
                'l.name',
                'l.name_short',
                'l.isHide',
            )
            ->from('shopLocation', 'l')
            ;

        if ($filter->name) {
            $qb->andWhere($qb->expr()->like('REPLACE(l.name, "/", "")', ':name'));
            $qb->setParameter('name', '%' . mb_strtolower(str_replace('/', '', $filter->name)) . '%');
        }

        if ($filter->name_short) {
            $qb->andWhere($qb->expr()->like('REPLACE(l.name_short, "/", "")', ':name_short'));
            $qb->setParameter('name_short', '%' . mb_strtolower(str_replace('/', '', $filter->name_short)) . '%');
        }

        if ($filter->number) {
            $qb->andWhere('l.locationID IN (SELECT locationID FROM zapSkladLocation zs INNER JOIN zapCards zc ON zs.zapCardID = zc.zapCardID WHERE zs.locationID IS NOT NULL AND zc.number like :number)');
            $qb->setParameter('number', '%' . mb_strtolower($filter->number) . '%');
        }

        if ($filter->showHidden === false) {
            $qb->andWhere('l.isHide = false');
        }

        if ($filter->isEmpty === true) {
            $qb->andWhere('l.locationID NOT IN (SELECT locationID FROM zapSkladLocation WHERE locationID IS NOT NULL)');
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['name', 'name_short'], true)) {
            $sort = 'name_short';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

}