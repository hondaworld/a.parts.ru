<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Inventarization\Inventarization;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Card\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class InventarizationGoodFetcher
{
    private $connection;
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'number';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 50;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
    }

    /**
     * @param Inventarization $inventarization
     * @param Filter\InventarizationGood\Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(Inventarization $inventarization, Filter\InventarizationGood\Filter $filter, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ig.goodID',
                'ig.zapCardID',
                'ig.zapSkladID',
                'ig.quantity',
                'ig.reserve',
                'ig.quantity_real',
                'zc.number',
                'zc.createrID',
                'c.name AS creater_name',
                'zs.name_short AS sklad_name',
                'm.name AS manager_name',
                "if ((zc.zapGroupID IS NULL), zc.name_big, CONCAT(zg.name, ' ', zc.name, ' ', zc.description)) AS detail_name",
                "if((ig.quantity - ig.quantity_real = 0), 0, (SELECT ifNull(SUM(price) / Count(incomeID), 0) * (ig.quantity - ig.quantity_real) FROM income WHERE zapCardID = ig.zapCardID AND price > 0)) AS price_dis",
                "ifNull((
                    SELECT sl.name_short
                        FROM shopLocation sl
                        INNER JOIN zapSkladLocation zsl ON sl.locationID = zsl.locationID
                        WHERE zsl.zapSkladID = ig.zapSkladID AND zsl.zapCardID = ig.zapCardID
                        LIMIT 1
                    ), '') AS location",
            )
            ->from('inventarization_goods', 'ig')
            ->innerJoin('ig', 'zapCards', 'zc', 'ig.zapCardID = zc.zapCardID')
            ->leftJoin('zc', 'zapGroup', 'zg', 'zc.zapGroupID = zg.zapGroupID')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID')
            ->innerJoin('ig', 'zapSklad', 'zs', 'ig.zapSkladID = zs.zapSkladID')
            ->innerJoin('ig', 'managers', 'm', 'ig.managerID = m.managerID')
            ->andWhere('ig.inventarizationID = :inventarizationID')
            ->setParameter('inventarizationID', $inventarization->getId());

        if ($filter->managerID) {
            $qb->andWhere('ig.managerID = :managerID');
            $qb->setParameter('managerID', $filter->managerID);
        }

        if ($filter->zapSkladID) {
            $qb->andWhere('ig.zapSkladID = :zapSkladID');
            $qb->setParameter('zapSkladID', $filter->zapSkladID);
        }

        if ($filter->createrID) {
            $qb->andWhere('zc.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        if ($filter->number) {
            $number = (new DetailNumber($filter->number))->getValue();
            $qb->andWhere($qb->expr()->like('zc.number', ':number'));
            $qb->setParameter('number', '%' . $number . '%');
        }

        if ($filter->location) {
            $qb->andWhere($qb->expr()->like("ifNull((
                    SELECT sl.name_short
                        FROM shopLocation sl
                        INNER JOIN zapSkladLocation zsl ON sl.locationID = zsl.locationID
                        WHERE zsl.zapSkladID = ig.zapSkladID AND zsl.zapCardID = ig.zapCardID
                        LIMIT 1
                    ), '')", ':location'));
            $qb->setParameter('location', $filter->location . '%');
        }

        if ($filter->showDis === true) {
            $qb->andWhere('ig.quantity <> ig.quantity_real');
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['creater_name', 'number', 'sklad_name', 'location'], true)) {
            $sort = self::DEFAULT_SORT_FIELD_NAME;
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param Inventarization $inventarization
     * @param Filter\InventarizationZapCard\Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function inventarization(Inventarization $inventarization, Filter\InventarizationZapCard\Filter $filter, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'zc.zapCardID',
                'zs.zapSkladID',
                'SUM(isk.quantityIn) AS quantity',
                'SUM(isk.reserve) AS reserve',
                'zc.number',
                'zc.createrID',
                'c.name AS creater_name',
                'zs.name_short AS sklad_name',
                "if ((zc.zapGroupID IS NULL), zc.name_big, CONCAT(zg.name, ' ', zc.name, ' ', zc.description)) AS detail_name",
                "ifNull((
                    SELECT sl.name_short
                        FROM shopLocation sl
                        INNER JOIN zapSkladLocation zsl ON sl.locationID = zsl.locationID
                        WHERE zsl.zapSkladID = zs.zapSkladID AND zsl.zapCardID = zc.zapCardID
                        LIMIT 1
                    ), '') AS location",
            )
            ->from('zapCards', 'zc')
            ->leftJoin('zc', 'zapGroup', 'zg', 'zc.zapGroupID = zg.zapGroupID')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID')
            ->innerJoin('zc', 'income', 'i', 'zc.zapCardID = i.zapCardID')
            ->innerJoin('i', 'income_sklad', 'isk', 'i.incomeID = isk.incomeID')
            ->innerJoin('isk', 'zapSklad', 'zs', 'isk.zapSkladID = zs.zapSkladID')
            ->andWhere('zc.zapCardID NOT IN (SELECT zapCardID FROM inventarization_goods WHERE zapSkladID = zs.zapSkladID AND inventarizationID = :inventarizationID)')
            ->andWhere('isk.quantityIn > 0')
            ->setParameter('inventarizationID', $inventarization->getId())
            ->groupBy('zc.zapCardID, zs.zapSkladID');

        if ($filter->zapSkladID) {
            $qb->andWhere('isk.zapSkladID = :zapSkladID');
            $qb->setParameter('zapSkladID', $filter->zapSkladID);
        }

        if ($filter->createrID) {
            $qb->andWhere('zc.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        if ($filter->number) {
            $number = (new DetailNumber($filter->number))->getValue();
            $qb->andWhere($qb->expr()->like('zc.number', ':number'));
            $qb->setParameter('number', '%' . $number . '%');
        }

        if ($filter->location) {
            $qb->andWhere($qb->expr()->like("ifNull((
                    SELECT sl.name_short
                        FROM shopLocation sl
                        INNER JOIN zapSkladLocation zsl ON sl.locationID = zsl.locationID
                        WHERE zsl.zapSkladID = zs.zapSkladID AND zsl.zapCardID = zc.zapCardID
                        LIMIT 1
                    ), '')", ':location'));
            $qb->setParameter('location', $filter->location . '%');
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['creater_name', 'number', 'sklad_name', 'location'], true)) {
            $sort = self::DEFAULT_SORT_FIELD_NAME;
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param Inventarization $inventarization
     * @param DetailNumber $number
     * @param ZapSklad $zapSklad
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function searchByNumber(Inventarization $inventarization, DetailNumber $number, ZapSklad $zapSklad): ?array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'zc.zapCardID',
                'zs.zapSkladID',
                'SUM(isk.quantityIn) AS quantity',
                'SUM(isk.reserve) AS reserve',
                'zc.number',
                'zc.createrID',
                'c.name AS creater_name',
                'zs.name_short AS sklad_name',
                "if ((zc.zapGroupID IS NULL), zc.name_big, CONCAT(zg.name, ' ', zc.name, ' ', zc.description)) AS detail_name",
                "ifNull((
                    SELECT sl.name_short
                        FROM shopLocation sl
                        INNER JOIN zapSkladLocation zsl ON sl.locationID = zsl.locationID
                        WHERE zsl.zapSkladID = zs.zapSkladID AND zsl.zapCardID = zc.zapCardID
                        LIMIT 1
                    ), '') AS location",
                "(SELECT quantity_real FROM inventarization_goods WHERE zapSkladID = zs.zapSkladID AND zapCardID = zc.zapCardID AND inventarizationID = :inventarizationID) AS quantity_real"
            )
            ->from('zapCards', 'zc')
            ->leftJoin('zc', 'zapGroup', 'zg', 'zc.zapGroupID = zg.zapGroupID')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID')
            ->innerJoin('zc', 'income', 'i', 'zc.zapCardID = i.zapCardID')
            ->innerJoin('i', 'income_sklad', 'isk', 'i.incomeID = isk.incomeID')
            ->innerJoin('isk', 'zapSklad', 'zs', 'isk.zapSkladID = zs.zapSkladID')
            ->andWhere('isk.quantityIn > 0')
            ->setParameter('inventarizationID', $inventarization->getId())
            ->andWhere('zc.number = :number')
            ->setParameter('number', $number->getValue())
            ->andWhere('zs.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSklad->getId())
            ->groupBy('zc.zapCardID, zs.zapSkladID');

        return $qb->executeQuery()->fetchAssociative() ?: null;
    }

}