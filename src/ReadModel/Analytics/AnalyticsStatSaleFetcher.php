<?php


namespace App\ReadModel\Analytics;


use App\Model\Income\Entity\Status\IncomeStatus;
use App\ReadModel\Analytics\Filter\StatSale\Filter;
use App\ReadModel\Reports\ReportFetcher;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use function Doctrine\DBAL\Query\QueryBuilder;

class AnalyticsStatSaleFetcher extends ReportFetcher
{
    private Connection $connection;
    private PaginatorInterface $paginator;

    private string $minDate = '';
    private string $maxDate = '';

    public const DEFAULT_SORT_FIELD_NAME = 'number';
    public const DEFAULT_SORT_DIRECTION = 'asc';

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
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

        return $this->getDates($date_from, $date_till, 'month');
    }

    /**
     * @param Filter $filter
     * @return array|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function all(Filter $filter): ?array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                '((ROUND(og.price - og.price * og.discount / 100) - i.price) * e.quantity) AS sum',
                'e.quantity',
                'ed.dateofadded',
                "i.zapCardID",
            )
            ->from('order_goods', 'og')
            ->leftJoin('og', 'income_sklad', 'isk', 'og.incomeID = isk.incomeID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->innerJoin('e', 'income', 'i', 'e.incomeID = i.incomeID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'og.expenseDocumentID = ed.expenseDocumentID')
            ->andWhere('i.price > 0')
            ->andWhere("og.number <> '15400PLMA03'")
            ->orderBy('ed.dateofadded');

        if ($filter->dateofreport) {
            if ($filter->dateofreport['date_from']) {
                $dateFrom = new DateTime($filter->dateofreport['date_from']);
                $qb->andWhere($qb->expr()->gte('ed.dateofadded', ':date_from'));
                $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
            }

            if ($filter->dateofreport['date_till']) {
                $dateTill = new DateTime($filter->dateofreport['date_till']);
                $qb->andWhere($qb->expr()->lte('ed.dateofadded', ':date_till'));
                $qb->setParameter('date_till', $dateTill->format('Y-m-d 23:59:59'));
            }

            if ($filter->zapSkladID) {
                $qb->andWhere('if (og.zapSkladID IS NULL, isk.zapSkladID = :zapSkladID, og.zapSkladID = :zapSkladID)');
                $qb->setParameter('zapSkladID', $filter->zapSkladID);
            }
        }

        $arr = $qb->executeQuery()->fetchAllAssociative();

        $qbZapCards = $this->connection->createQueryBuilder()
            ->select(
                "i.zapCardID",
            )
            ->from('income', 'i')
            ->andWhere('i.price > 0')
            ->andWhere('i.status = :status')
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
            ->groupBy('i.zapCardID');

        if ($filter->dateofreport && $filter->zapSkladID) {
            $qbZapCards->innerJoin('i', 'income_sklad', 'isk', 'i.incomeID = isk.incomeID');
            $qbZapCards->andWhere('isk.zapSkladID = :zapSkladID');
            $qbZapCards->setParameter('zapSkladID', $filter->zapSkladID);
            $qbZapCards->andWhere("isk.quantityIn + isk.quantityPath - isk.reserve > 0");
        } else {
            $qbZapCards->andWhere("i.quantityIn + i.quantityPath - i.reserve > 0");
        }
        $arrZapCards = $qbZapCards->executeQuery()->fetchFirstColumn();

        $this->minDate = $arr[0]['dateofadded'];
        $this->maxDate = $arr[count($arr) - 1]['dateofadded'];

        return $this->generateArray($arr, $arrZapCards);
    }

    /**
     * @param array $arr
     * @param array $arrZapCards
     * @return array
     * @throws Exception
     */
    private function generateArray(array $arr, array $arrZapCards): array
    {
        $result = [];

        foreach ($arrZapCards as $zapCardID) {
            $result[$zapCardID] = [
                'date' => [],
                'sum' => 0,
                'quantity' => 0
            ];
        }

        foreach ($arr as $item) {
            $date = (new DateTime($item['dateofadded']))->format('Y-m');
            if (!isset($result[$item['zapCardID']])) {
                $result[$item['zapCardID']] = [
                    'date' => [],
                    'sum' => 0,
                    'quantity' => 0
                ];
            }

            if (!isset($result[$item['zapCardID']]['date'][$date])) {
                $result[$item['zapCardID']]['date'][$date] = [
                    'sum' => 0,
                    'quantity' => 0
                ];
            }

            $result[$item['zapCardID']]['sum'] += $item['sum'];
            $result[$item['zapCardID']]['quantity'] += $item['quantity'];

            $result[$item['zapCardID']]['date'][$date]['sum'] += $item['sum'];
            $result[$item['zapCardID']]['date'][$date]['quantity'] += $item['quantity'];
        }
        return $result;
    }

    public function getIncomeData(Filter $filter, array $zapCards, array $providerPrices): array
    {
        if (!$zapCards) return [];
        $result = [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                '(i.quantityIn + i.quantityPath - i.reserve) AS quantity',
                '(SELECT quantityIn + quantityPath - reserve FROM income_sklad WHERE incomeID = i.incomeID AND zapSkladID = 1 LIMIT 1) AS quantity_msk',
                '(SELECT quantityIn + quantityPath - reserve FROM income_sklad WHERE incomeID = i.incomeID AND zapSkladID = 5 LIMIT 1) AS quantity_spb',
                '(SELECT quantityIn + quantityPath - reserve FROM income_sklad WHERE incomeID = i.incomeID AND zapSkladID = 6 LIMIT 1) AS quantity_spb2',
                'ifNull((SELECT 1 FROM order_goods WHERE incomeID = i.incomeID), 0) AS is_zakaz',
                'i.incomeID',
                'i.quantityIn',
                "i.dateofin",
                "i.providerPriceID",
                "i.zapCardID",
            )
            ->from('income', 'i')
            ->where('i.status = :status')
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
            ->orderBy('i.dateofin');
        $qb->andWhere($qb->expr()->in('i.zapCardID', $zapCards));
        $arr = $qb->executeQuery()->fetchAllAssociative();

        foreach ($arr as $item) {
            if (!isset($result[$item['zapCardID']])) {
                $result[$item['zapCardID']] = [
                    'quantity' => 0,
                    'is_nal' => false,
                    'is_sklad' => false,
                    'providerPriceID' => null,
                    'date_first_income' => null,
                ];
            }

            if (!$result[$item['zapCardID']]['date_first_income']) {
                $result[$item['zapCardID']]['date_first_income'] = $item['dateofin'];
            }

            $result[$item['zapCardID']]['quantity'] += $filter->zapSkladID == 1 ? $item['quantity_msk'] : ($filter->zapSkladID == 5 ? $item['quantity_spb'] : ($filter->zapSkladID == 6 ? $item['quantity_spb2'] : $item['quantity']));

            if ($item["quantityIn"] > 0) $result[$item['zapCardID']]["is_nal"] = true;
            if ($item["is_zakaz"] == 0) $result[$item["zapCardID"]]["is_sklad"] = true;


            if ($item['providerPriceID'] && isset($providerPrices[$item['providerPriceID']]) && $providerPrices[$item['providerPriceID']]['isHide'] == 0) {
                $result[$item['zapCardID']]["providerPriceID"] = $item['providerPriceID'];
            }
        }

//        $arr = $this->connection->createQueryBuilder()
//            ->select('i.zapCardID')
//            ->from('order_goods', 'og')
//            ->innerJoin('og', 'income', 'i', 'og.incomeID = i.incomeID')
//            ->where('i.status = :status')
//            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
//            ->executeQuery()
//            ->fetchFirstColumn();
//
//
//        foreach ($arr as $item) {
//            if (isset($result[$item['zapCardID']])) {
//                if (!in_array($item['incomeID'], $orderGoodIncomes)) $result[$item['zapCardID']]["is_sklad"] = true;
//            }
//        }

        return $result;
    }
}