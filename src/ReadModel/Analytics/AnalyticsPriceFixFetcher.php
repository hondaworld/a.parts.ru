<?php


namespace App\ReadModel\Analytics;


use App\ReadModel\Analytics\Filter\PriceFix\Filter;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class AnalyticsPriceFixFetcher
{
    public const DEFAULT_SORT_FIELD_NAME = 'number';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 50;
    private Connection $connection;
    private PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(Filter $filter, int $page, array $settings): PaginationInterface
    {
//        SELECT a.zapCardID, a.number, a.price, a.is_price_group_fix, b.name AS creater, c.name AS price_group, if (a.zapGroupID=0, a.name_big, (SELECT name FROM zapGroup WHERE zapGroupID = a.zapGroupID)) AS zapGroup, (SELECT ifnull(SUM(quantityIn), 0) FROM income WHERE zapCardID = a.zapCardID) AS quantityIn, ifnull((SELECT abc FROM zapCard_abc WHERE zapCardID = a.zapCardID AND zapSkladID = 1), '') AS abc_msk, ifnull((SELECT abc FROM zapCard_abc WHERE zapCardID = a.zapCardID AND zapSkladID = 5), '') AS abc_spb
//	FROM zapCards a
//	INNER JOIN creaters b ON a.createrID = b.createrID
//	LEFT JOIN price_groups c ON a.price_groupID = c.price_groupID
//	WHERE (SELECT ifnull(SUM(quantityIn), 0) FROM income WHERE zapCardID = a.zapCardID) > 0 $where

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'zc.zapCardID',
                'zc.number',
                'zc.createrID',
                'c.name AS creater_name',
                "zc.price",
                "zc.is_price_group_fix",
                "pg.name AS price_group",
                "if (zc.zapGroupID IS NULL, zc.name_big, (SELECT name FROM zapGroup WHERE zapGroupID = zc.zapGroupID)) AS detail_name",
                "(SELECT ifNull(SUM(quantityIn), 0) FROM income WHERE zapCardID = zc.zapCardID) AS quantity",
                "ifNull((SELECT abc FROM zapCard_abc WHERE zapCardID = zc.zapCardID AND zapSkladID = 1), '') AS abc_msk",
                "ifNull((SELECT abc FROM zapCard_abc WHERE zapCardID = zc.zapCardID AND zapSkladID = 5), '') AS abc_spb"
            )
            ->from('zapCards', 'zc')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID')
            ->leftJoin('zc', 'price_groups', 'pg', 'zc.price_groupID = pg.price_groupID')
            ->andWhere('(SELECT ifNull(SUM(quantityIn), 0) FROM income WHERE zapCardID = zc.zapCardID) > 0');

        if ($filter->price_groupID) {
            $qb->andWhere('zc.price_groupID = :price_groupID');
            $qb->setParameter('price_groupID', $filter->price_groupID);
        }

        if ($filter->is_price_group_fix !== null) {
            $qb->andWhere('zc.is_price_group_fix = :is_price_group_fix');
            $qb->setParameter('is_price_group_fix', $filter->is_price_group_fix);
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['abc_msk', 'abc_spb', 'detail_name', 'creater_name', 'number', 'price', 'quantity', 'price_group', 'is_price_group_fix'], true)) {
            $sort = 'number';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}