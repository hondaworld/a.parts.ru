<?php


namespace App\ReadModel\Contact;


use App\ReadModel\Contact\Filter;
use App\Model\Contact\Entity\Country\Country;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class TownFetcher
{
    private $connection;
    private $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 't.name';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
    }

    /**
     * @param string $name
     * @return TownView[]|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function findTownsByName(string $name): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                't.townID',
                't.regionID',
                't.typeID',
                't.name',
                't.name_short',
                't.name_doc',
                't.daysFromMoscow',
                't.isFree',
                't.isHide',
                'r.name AS region',
                'r.daysFromMoscow AS daysFromMoscowRegion',
                'c.name AS country',
                'y.name AS type',
                'y.name_short AS type_short'
            )
            ->from('towns', 't')
            ->innerJoin('t', 'townRegions', 'r', 't.regionID = r.regionID')
            ->innerJoin('t', 'townTypes', 'y', 't.typeID = y.id')
            ->innerJoin('r', 'countries', 'c', 'c.countryID = r.countryID')
            ->where('t.name_short like :name')
            ->setParameter('name', '%' . $name . '%')
            ->orderBy("region")
            ->addOrderBy('name')
            ->executeQuery();

//        $stmt->setFetchMode(PDO::FETCH_CLASS, TownView::class);
        $arr = $stmt->fetchAllAssociative();

        $result = [];
        if ($arr) {
            foreach ($arr as $items) {
                $townView = new TownView();
                foreach ($items as $name => $value) {
                    $townView->$name = $value;
                }
                $result[] = $townView;
            }
        }

        return $result ?: null;
    }

    /**
     * @param int $id
     * @return TownView|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function findTownsById(int $id): ?TownView
    {

        $stmt = $this->connection->createQueryBuilder()
            ->select(
                't.townID',
                't.regionID',
                't.typeID',
                't.name',
                't.name_short',
                't.name_doc',
                't.daysFromMoscow',
                't.isFree',
                't.isHide',
                'r.name AS region',
                'r.daysFromMoscow AS daysFromMoscowRegion',
                'c.name AS country',
                'y.name AS type',
                'y.name_short AS type_short'
            )
            ->from('towns', 't')
            ->innerJoin('t', 'townRegions', 'r', 't.regionID = r.regionID')
            ->innerJoin('t', 'townTypes', 'y', 't.typeID = y.id')
            ->innerJoin('r', 'countries', 'c', 'c.countryID = r.countryID')
            ->where('t.townID = :id')
            ->setParameter('id', $id)
            ->executeQuery();

//        $stmt->setFetchMode(PDO::FETCH_CLASS, TownView::class);
        $result = $stmt->fetchAssociative();

        $townView = new TownView();
        foreach ($result as $name => $value) {
            $townView->$name = $value;
        }

        return $result ? $townView : null;
    }

    /**
     * @param Filter\Towns\Filter $filter
     * @param Country $country
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(Filter\Towns\Filter $filter, Country $country, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                't.townID',
                't.regionID',
                't.typeID',
                't.name',
                't.name_short',
                't.name_doc',
                't.daysFromMoscow',
                't.isFree',
                't.isHide',
                'r.name AS region',
                'r.daysFromMoscow AS daysFromMoscowRegion',
                'c.name AS country',
                'y.name AS type',
                'y.name_short AS type_short'
            )
            ->from('towns', 't')
            ->innerJoin('t', 'townRegions', 'r', 't.regionID = r.regionID')
            ->innerJoin('t', 'townTypes', 'y', 't.typeID = y.id')
            ->innerJoin('r', 'countries', 'c', 'c.countryID = r.countryID')
            ->where('r.countryID = :countryID')
            ->setParameter('countryID', $country->getId())
        ;

        if ($filter->name_short) {
            $qb->andWhere($qb->expr()->like('t.name_short', ':name_short'));
            $qb->setParameter('name_short', '%' . mb_strtolower($filter->name_short) . '%');
        }

        if ($filter->name) {
            $qb->andWhere($qb->expr()->like('t.name', ':name'));
            $qb->setParameter('name', '%' . mb_strtolower($filter->name) . '%');
        }

        if ($filter->regionID) {
            $qb->andWhere('t.regionID = :regionID');
            $qb->setParameter('regionID', $filter->regionID);
        }

        if ($filter->typeID) {
            $qb->andWhere('t.typeID = :typeID');
            $qb->setParameter('typeID', $filter->typeID);
        }

        if ($filter->isFree) {
            $qb->andWhere('t.isFree = :isFree');
            $qb->setParameter('isFree', $filter->isFree);
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}