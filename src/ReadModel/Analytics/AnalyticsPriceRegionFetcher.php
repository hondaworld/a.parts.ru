<?php


namespace App\ReadModel\Analytics;


use App\ReadModel\Analytics\Filter\PriceRegion\Filter;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class AnalyticsPriceRegionFetcher
{
    private Connection $connection;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    /**
     * @param Filter $filter
     * @return array
     * @throws Exception
     */
    public function all(Filter $filter): array
    {
//        SELECT a.zapCardID, a.number, a.createrID, a.price, a.currency_price, a.currencyID, a.currency_providerPriceID, c.name AS creater
//		FROM zapCards a
//		INNER JOIN income b ON a.zapCardID = b.zapCardID
//		INNER JOIN creaters c ON a.createrID = c.createrID
//		WHERE b.quantityIn > 0
//		GROUP BY a.zapCardID
//		ORDER BY a.number

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'zc.zapCardID',
                'zc.number',
                'zc.createrID',
                'zc.price',
                'zc.currency_price',
                'zc.currencyID',
                'zc.currency_providerPriceID',
                'c.name AS creater_name'
            )
            ->from('zapCards', 'zc')
            ->innerJoin('zc', 'income', 'i', 'zc.zapCardID = i.zapCardID')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID')
            ->andWhere('i.quantityIn > 0')
            ->groupBy('zc.zapCardID')
            ->orderBy('zc.number')
        ;

        if ($filter->abc) {
            if ($filter->zapSkladID) {
                $qb->andWhere('zc.zapCardID in (SELECT zapCardID FROM zapCard_abc WHERE abc = :abc AND zapSkladID = :zapSkladID)');
                $qb->setParameter('abc', $filter->abc);
                $qb->setParameter('zapSkladID', $filter->zapSkladID);
            } else {
                $qb->andWhere('zc.zapCardID in (SELECT zapCardID FROM zapCard_abc WHERE abc = :abc)');
                $qb->setParameter('abc', $filter->abc);
            }
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }
}