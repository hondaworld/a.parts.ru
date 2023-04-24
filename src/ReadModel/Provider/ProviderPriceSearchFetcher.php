<?php


namespace App\ReadModel\Provider;


use App\Model\Card\Entity\Card\DetailNumber;
use App\ReadModel\Detail\WeightFetcher;
use App\ReadModel\Provider\Filter\Search\Filter;
use Doctrine\ORM\EntityManagerInterface;

class ProviderPriceSearchFetcher
{
    private $connection;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    /**
     * @param Filter $filter
     * @param WeightFetcher $weightFetcher
     * @return array|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function all(Filter $filter, WeightFetcher $weightFetcher): ?array
    {
        if (!$filter->number) return null;

        $number = (new DetailNumber($filter->number))->getValue();

        $result = [];
        for ($i = 1; $i <= 10; $i++) {
            $qb = $this->connection->createQueryBuilder()
                ->select(
                    "a.name",
                    "a.createrID",
                    "cr.name AS creater",
                    "a.number",
                    "a.price",
                    "a.quantity",
                    "a.dateOfChanged",
                    "CONCAT(c.name, ' - ', b.description) AS provider",
                    "a.providerPriceID",
                    "b.koef",
                    "b.srok",
                    "b.forWeight",
                    "d.name AS currency",
                    "cr.tableName"
                )
                ->from('shopPrice' . $i, 'a')
                ->innerJoin('a', 'creaters', 'cr', 'a.createrID = cr.createrID')
                ->innerJoin('a', 'providerPrices', 'b', 'a.providerPriceID = b.providerPriceID')
                ->innerJoin('b', 'providers', 'c', 'b.providerID = c.providerID')
                ->innerJoin('b', 'currency', 'd', 'b.currencyID = d.currencyID')
                ->andWhere('b.isHide = 0')
                ->andWhere('a.number = :number')
                ->setParameter('number', $number)
                ->orderBy('provider');
            $result = array_merge($result, $qb->executeQuery()->fetchAllAssociative());
        }
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.name",
                "a.createrID",
                "CONCAT(cr.name, if(a.creater_add <> '', CONCAT(' ', a.creater_add), '')) AS creater",
                "a.number",
                "a.price",
                "a.quantity",
                "a.dateOfChanged",
                "CONCAT(c.name, ' ', b.name) AS provider",
                "a.providerPriceID",
                "b.koef",
                "b.srok",
                "b.forWeight",
                "d.name AS currency",
                "'shopPriceN' AS tableName"
            )
            ->from('shopPriceN', 'a')
            ->innerJoin('a', 'creaters', 'cr', 'a.createrID = cr.createrID')
            ->innerJoin('a', 'providerPrices', 'b', 'a.providerPriceID = b.providerPriceID')
            ->innerJoin('b', 'providers', 'c', 'b.providerID = c.providerID')
            ->innerJoin('b', 'currency', 'd', 'b.currencyID = d.currencyID')
            ->andWhere('b.isHide = 0')
            ->andWhere('a.number = :number')
            ->setParameter('number', $number)
            ->orderBy('provider');
        $result = array_merge($result, $qb->executeQuery()->fetchAllAssociative());

        foreach ($result as &$item) {
            $item['weight'] = $weightFetcher->allByNumberAndCreater($item['number'], $item['createrID']);
            if ($item['weight']) $item['weight'] = $item['weight'][0];
        }

//        if ($rowCreater->isOriginal == 1)
//        {
//            $query = "
//		SELECT a.name, a.number, a.price, a.quantity, a.dateOfChanged, b.name AS dostavka, c.name AS dostavka1, a.providerPriceID, b.koef, b.srok, b.forWeight, d.name AS usd, '' AS creater_add
//		FROM ".$rowCreater->tableName." a
//		INNER JOIN providerPrices b ON a.providerPriceID = b.providerPriceID
//		INNER JOIN providers c ON b.providerID = c.providerID
//		INNER JOIN currency d ON b.currencyID = d.currencyID
//		WHERE a.number = '".$numberSearch."' AND a.createrID = ".$createrID." AND b.isHide = 0
//		ORDER BY dostavka1, dostavka";
//        }
//        else
//        {
//            $query = "
//		SELECT a.name, a.number, a.price, a.quantity, a.dateOfChanged, b.name AS dostavka, c.name AS dostavka1, a.providerPriceID, b.koef, b.srok, b.forWeight, d.name AS usd, a.creater_add
//		FROM shopPriceN a
//		INNER JOIN providerPrices b ON a.providerPriceID = b.providerPriceID
//		INNER JOIN providers c ON b.providerID = c.providerID
//		INNER JOIN currency d ON b.currencyID = d.currencyID
//		WHERE a.number = '".$numberSearch."' AND a.createrID = ".$createrID." AND b.isHide = 0
//		ORDER BY dostavka1, dostavka";
//        }


        return $result;
    }
}