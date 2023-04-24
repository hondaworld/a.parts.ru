<?php


namespace App\ReadModel\Reports;


use App\ReadModel\Reports\Filter\FinanceType\Filter;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ReportFinanceTypeFetcher extends ReportFetcher
{
    private Connection $connection;
    private string $minDate = '';
    private string $maxDate = '';

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    /**
     * @param Filter $filter
     * @return array|null
     * @throws Exception
     */
    public function dates(Filter $filter): array
    {
        $date_from = new DateTime($this->minDate);
        $date_till = new DateTime($this->maxDate);

        if ($filter->dateofreport) {
            if ($filter->dateofreport['date_from']) {
                $date_from = new DateTime($filter->dateofreport['date_from']);
            }
            if ($filter->dateofreport['date_till']) {
                $date_till = new DateTime($filter->dateofreport['date_till']);
            }
        }

        return $this->getDates($date_from, $date_till, $filter->period);
    }

    /**
     * @param array $financeTypes
     * @param Filter $filter
     * @return array|null
     * @throws \Doctrine\DBAL\Exception
     * @throws Exception
     */
    public function all(array $financeTypes, Filter $filter): ?array
    {
//        SELECT ifnull(Sum(balance), 0) AS summ, c.name AS finance_type
//		FROM userBalanceHistory a
//		INNER JOIN finance_types c ON a.finance_typeID = c.finance_typeID
//		WHERE a.expenseDocumentID = 0 $where
//		GROUP BY a.finance_typeID

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'b.finance_typeID',
                "b.balance",
                "b.dateofadded",
            )
            ->from('userBalanceHistory', 'b')
            ->andWhere('b.expenseDocumentID IS NULL')
            ->orderBy('b.dateofadded')
        ;

        if ($filter->dateofreport) {
            if ($filter->dateofreport['date_from']) {
                $dateFrom = new DateTime($filter->dateofreport['date_from']);
                $qb->andWhere($qb->expr()->gte('b.dateofadded', ':date_from'));
                $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
            }

            if ($filter->dateofreport['date_till']) {
                $dateTill = new DateTime($filter->dateofreport['date_till']);
                $qb->andWhere($qb->expr()->lte('b.dateofadded', ':date_till'));
                $qb->setParameter('date_till', $dateTill->format('Y-m-d 23:59:59'));
            }
        }

        $arr = $qb->executeQuery()->fetchAllAssociative();

        $this->minDate = $arr[0]['dateofadded'];
        $this->maxDate = $arr[count($arr) - 1]['dateofadded'];


        return $this->generateArray($arr, $financeTypes, $filter->period);
    }

    /**
     * @param array $arr
     * @param array $financeTypes
     * @param string $period
     * @return array
     * @throws Exception
     */
    private function generateArray(array $arr, array $financeTypes, string $period): array
    {
        $arrBlank = [
            'value' => 0,
            'date' => []
        ];

        $result = [];
        foreach ($financeTypes as $finance_typeID => $name) {
            $result[$finance_typeID] = $arrBlank;
        }

        foreach ($arr as $item) {
            $date = $this->getDateByPeriod($item['dateofadded'], $period);
            $result[$item['finance_typeID']]['value'] += $item['balance'];
            $result[$item['finance_typeID']]['date'][$date] = ($result[$item['finance_typeID']]['date'][$date] ?? 0) + $item['balance'];
        }
        return $result;
    }

    /**
     * @param string $dateofadded
     * @param string $period
     * @return string
     * @throws Exception
     */
    private function getDateByPeriod(string $dateofadded, string $period): string
    {
        $date = (new DateTime($dateofadded));
        switch ($period) {
            case 'year':
                return $date->format('Y');
            case 'month':
                return $date->format('Y-m');
            case 'day':
                return $date->format('Y-m-d');
        }
        return $date->format('Y-m-d');
    }
}