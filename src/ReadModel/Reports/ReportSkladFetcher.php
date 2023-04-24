<?php


namespace App\ReadModel\Reports;


use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

class ReportSkladFetcher extends ReportFetcher
{
    private Connection $connection;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function all(): array
    {
//        SELECT SUM(a.quantityIn * c.price) AS summ, SUM(a.quantityIn * d.price) AS summ1, a.zapSkladID
//			FROM income_sklad a
//			INNER JOIN income c ON a.incomeID = c.incomeID
//			INNER JOIN zapCards d ON c.zapCardID = d.zapCardID
//			WHERE d.createrID = ".$row->createrID."
//			GROUP BY a.zapSkladID

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'SUM(isk.quantityIn * i.price) AS sum_income',
                'SUM(isk.quantityIn * zc.price) AS sum_card',
                "isk.zapSkladID",
                "zc.createrID",
                "i.providerPriceID",
            )
            ->from('income_sklad', 'isk')
            ->innerJoin('isk', 'income', 'i', 'isk.incomeID = i.incomeID')
            ->innerJoin('i', 'zapCards', 'zc', 'i.zapCardID = zc.zapCardID')
            ->andWhere('isk.quantityIn > 0')
            ->groupBy('isk.zapSkladID, zc.createrID, i.providerPriceID')
        ;

        $arr = $qb->executeQuery()->fetchAllAssociative();

        return $this->generateArray($arr);
    }

    /**
     * @param array $arr
     * @return array
     */
    private function generateArray(array $arr): array
    {

        $result = [];
        foreach ($arr as $item) {
            if (isset($result[$item['createrID']][$item['zapSkladID']])) {
                $result[$item['createrID']][$item['zapSkladID']]['sum_income'] += $item['sum_income'];
                $result[$item['createrID']][$item['zapSkladID']]['sum_card'] += $item['sum_card'];
                if ($item['providerPriceID']) {
                    $result[$item['createrID']][$item['zapSkladID']]['providerPrices'][$item['providerPriceID']] = $result[$item['createrID']][$item['zapSkladID']]['providerPrices'][$item['providerPriceID']] ?? 0 + $item['sum_income'];
                }
            } else {
                $result[$item['createrID']][$item['zapSkladID']] = [
                    'sum_income' => $item['sum_income'],
                    'sum_card' => $item['sum_card'],
                    'providerPrices' => []
                ];
                if ($item['providerPriceID']) {
                    $result[$item['createrID']][$item['zapSkladID']]['providerPrices'][$item['providerPriceID']] = $item['sum_income'];
                }
            }
        }
        return $result;
    }
}