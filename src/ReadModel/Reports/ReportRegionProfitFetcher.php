<?php


namespace App\ReadModel\Reports;


use App\Model\Contact\Entity\TownRegion\TownRegion;
use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\Opt\Opt;
use App\ReadModel\Reports\Filter\RegionProfit\Filter;
use App\ReadModel\Shop\ResellerFetcher;
use App\ReadModel\User\UserFetcher;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class ReportRegionProfitFetcher extends ReportFetcher
{
    private Connection $connection;
    private array $users = [];
    private array $arr;
    private array $prevArr;
    private UserFetcher $userFetcher;
    private ResellerFetcher $resellerFetcher;

    public function __construct(EntityManagerInterface $em, UserFetcher $userFetcher, ResellerFetcher $resellerFetcher)
    {
        $this->connection = $em->getConnection();
        $this->userFetcher = $userFetcher;
        $this->resellerFetcher = $resellerFetcher;
    }

    /**
     * @param Filter $filter
     * @return array|null
     * @throws Exception
     */
    public function prevDates(Filter $filter): ?array
    {
        if (!$filter->dateofreport || !$filter->dateofreport['date_from'] || !$filter->dateofreport['date_till'] || !$filter->dateofprev) return null;

        $dateFrom = new DateTime($filter->dateofreport['date_from']);
        $dateTill = new DateTime($filter->dateofreport['date_till']);

        return $this->getPrevDates($dateFrom, $dateTill, $filter->dateofprev);
    }

    /**
     * @param Filter $filter
     * @return array|null
     * @throws Exception
     */
    public function dates(Filter $filter): ?array
    {
        if (!$filter->dateofreport || !$filter->dateofreport['date_from'] || !$filter->dateofreport['date_till']) return null;
        $date_from = new DateTime($filter->dateofreport['date_from']);
        $date_till = new DateTime($filter->dateofreport['date_till']);
        return $this->getDates($date_from, $date_till);
    }

    /**
     * @return array|null
     * @throws Exception
     */
    public function today(Manager $manager): array
    {
        $dateFrom = new DateTime();
//        $dateTill = (new DateTime())->modify('-1 year');

        $arr = $this->data($dateFrom, $dateFrom, $manager);
        $this->users = $this->getUsers($arr);
        $result = $this->generateArray($arr, $this->users);

        $a = [
            'msk' => [
                'profit' =>
                    $result['msk']['opt']['sklad']['profit']['value'] +
                    $result['msk']['opt']['zakaz']['profit']['value'] +
                    $result['msk']['notOpt']['sklad']['profit']['value'] +
                    $result['msk']['notOpt']['zakaz']['profit']['value'],
                'income' =>
                    $result['msk']['opt']['sklad']['income']['value'] +
                    $result['msk']['opt']['zakaz']['income']['value'] +
                    $result['msk']['notOpt']['sklad']['income']['value'] +
                    $result['msk']['notOpt']['zakaz']['income']['value']
            ],
            'spb' => [
                'profit' =>
                    $result['spb']['opt']['sklad']['profit']['value'] +
                    $result['spb']['opt']['zakaz']['profit']['value'] +
                    $result['spb']['notOpt']['sklad']['profit']['value'] +
                    $result['spb']['notOpt']['zakaz']['profit']['value'],
                'income' =>
                    $result['spb']['opt']['sklad']['income']['value'] +
                    $result['spb']['opt']['zakaz']['income']['value'] +
                    $result['spb']['notOpt']['sklad']['income']['value'] +
                    $result['spb']['notOpt']['zakaz']['income']['value']
            ],
            'region' => [
                'profit' =>
                    $result['region']['opt']['sklad']['profit']['value'] +
                    $result['region']['opt']['zakaz']['profit']['value'] +
                    $result['region']['notOpt']['sklad']['profit']['value'] +
                    $result['region']['notOpt']['zakaz']['profit']['value'],
                'income' =>
                    $result['region']['opt']['sklad']['income']['value'] +
                    $result['region']['opt']['zakaz']['income']['value'] +
                    $result['region']['notOpt']['sklad']['income']['value'] +
                    $result['region']['notOpt']['zakaz']['income']['value']
            ],
        ];

        $a ['profit'] = $a['msk']['profit'] + $a['spb']['profit'] + $a['region']['profit'];
        $a ['income'] = $a['msk']['income'] + $a['spb']['income'] + $a['region']['income'];

        return $a;
    }

    /**
     * @param Filter $filter
     * @return array|null
     * @throws Exception
     */
    public function all(Filter $filter): ?array
    {
        if (!$filter->dateofreport || !$filter->dateofreport['date_from'] || !$filter->dateofreport['date_till']) return null;

        $dateFrom = new DateTime($filter->dateofreport['date_from']);
        $dateTill = new DateTime($filter->dateofreport['date_till']);

        $arr = $this->data($dateFrom, $dateTill);

        $this->users = $this->getUsers($arr);

        return $this->generateArray($arr, $this->users);
    }

    /**
     * @param Filter $filter
     * @return array|null
     * @throws Exception
     */
    public function prev(Filter $filter): ?array
    {
        if (!$filter->dateofreport || !$filter->dateofreport['date_from'] || !$filter->dateofreport['date_till'] || !$filter->dateofprev) return null;

        $dateFrom = new DateTime($filter->dateofreport['date_from']);
        $dateTill = new DateTime($filter->dateofreport['date_till']);

        $days = $dateFrom->diff($dateTill);

        $datePrev = clone($filter->dateofprev);
        $datePrevTill = clone($datePrev);
        $datePrevTill->modify('+' . $days->days . ' day');

        $prevArr = $this->data($datePrev, $datePrevTill);

        $this->users = $this->users + $this->getUsers($prevArr);

        return $this->generateArray($prevArr, $this->users);
    }

    /**
     * @param array $arr
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    private function getUsers(array $arr): array
    {
        $users = [];
        foreach ($arr as $item) {
            if (!in_array($item['userID'], $users) && !in_array($item['userID'], $this->users)) $users[] = $item['userID'];
        }
        return $this->userFetcher->findByIDsWithRegion($users);
    }

    /**
     * @param array $arr
     * @param array $users
     * @return array
     * @throws Exception
     */
    private function generateArray(array $arr, array $users): array
    {
        $resellers = $this->resellerFetcher->assoc();

        $arrBlankInner = [
            'profit' => ['value' => 0, 'date' => []],
            'income' => ['value' => 0, 'date' => []],
            'checks' => ['value' => 0, 'date' => []]
        ];

        $result = [
            'msk' => [
                'opt' => $arrBlankInner,
                'notOpt' => $arrBlankInner,
                'service' => $arrBlankInner,
            ],
            'region' => [
                'opt' => $arrBlankInner,
                'notOpt' => $arrBlankInner,
                'service' => $arrBlankInner,
            ],
            'spb' => [
                'opt' => $arrBlankInner,
                'notOpt' => $arrBlankInner,
                'service' => $arrBlankInner,
            ],
            'resellers' => [],
        ];


        $expenseDocumentsResellers = [];
        $expenseDocumentsUsersResellers = [];
        foreach ($resellers as $resellerID => $reseller) {
            $result['resellers'][$resellerID] = $arrBlankInner;

            $expenseDocumentsResellers[$resellerID] = [];
            $expenseDocumentsUsersResellers[$resellerID] = [];
        }

        $expenseDocuments = [];
        $expenseDocumentsService = [];
        $expenseDocumentsUsers = [];
        $expenseDocumentsUsersService = [];

        foreach ($arr as $item) {
            $date = (new DateTime($item['dateofadded']))->format('Y-m-d');

            $region = 'region';
            if (isset($users[$item['userID']])) {
                if ($users[$item['userID']]['regionID'] == TownRegion::MSK) $region = 'msk';
                if ($users[$item['userID']]['regionID'] == TownRegion::SPB) $region = 'spb';
            }
            $opt = $item['optID'] == Opt::DEFAULT_OPT_ID ? 'notOpt' : 'opt';
            $isSklad = $item['isSklad'] == 1 ? 'sklad' : 'zakaz';

            $isCheck = false;
            $isCheckAll = false;

            if (in_array($item['doc_typeID'], [DocumentType::RN, DocumentType::TCH]) && $item['expenseDocumentStatus'] == ExpenseDocument::STATUS_DONE && !in_array($item['expenseDocumentID'], $expenseDocuments)) {
                $isCheck = true;
                $expenseDocuments[] = $item['expenseDocumentID'];
            }

            if (!in_array($item['expenseDocumentID'], $expenseDocumentsUsers)) {
                $isCheckAll = true;
                $expenseDocumentsUsers[] = $item['expenseDocumentID'];
            }
            $result = $this->generateItem($item, $result, $region, $opt, $date, $isCheck, $isCheckAll);

            if ($item['isService'] == 1) {
                $isCheck = false;
                $isCheckAll = false;

                if (in_array($item['doc_typeID'], [DocumentType::RN, DocumentType::TCH]) && $item['expenseDocumentStatus'] == ExpenseDocument::STATUS_DONE && !in_array($item['expenseDocumentID'], $expenseDocumentsService)) {
                    $isCheck = true;
                    $expenseDocumentsService[] = $item['expenseDocumentID'];
                }

                if (!in_array($item['expenseDocumentID'], $expenseDocumentsUsersService)) {
                    $isCheckAll = true;
                    $expenseDocumentsUsersService[] = $item['expenseDocumentID'];
                }

                $result = $this->generateItem($item, $result, $region, 'service', $date, $isCheck, $isCheckAll);
            }

            if ($item['reseller_id']) {
                $isCheck = false;
                $isCheckAll = false;

                if (in_array($item['doc_typeID'], [DocumentType::RN, DocumentType::TCH]) && $item['expenseDocumentStatus'] == ExpenseDocument::STATUS_DONE && !in_array($item['expenseDocumentID'], $expenseDocumentsResellers[$item['reseller_id']])) {
                    $isCheck = true;
                    $expenseDocumentsResellers[$item['reseller_id']][] = $item['expenseDocumentID'];
                }

                if (!in_array($item['expenseDocumentID'], $expenseDocumentsUsersResellers[$item['reseller_id']])) {
                    $isCheckAll = true;
                    $expenseDocumentsUsersResellers[$item['reseller_id']][] = $item['expenseDocumentID'];
                }

                $result = $this->generateItem($item, $result, 'resellers', $item['reseller_id'], $date, $isCheck, $isCheckAll);
            }
        }
        return $result;
    }

    private function generateItem(array $item, array $result, string $region, string $opt, string $date, bool $isCheck, bool $isCheckAll): array
    {
        $profit = ($item['priceGood'] - $item['priceZak']) * $item['quantity'];
        $income = $item['priceGood'] * $item['quantity'];
        $isSklad = $item['isSklad'] == 1 ? 'sklad' : 'zakaz';

        $result[$region][$opt]['profit']['value'] += $profit;
        $result[$region][$opt]['income']['value'] += $income;

        $result[$region][$opt]['profit']['date'][$date] = ($result[$region][$opt]['profit']['date'][$date] ?? 0) + $profit;
        $result[$region][$opt]['income']['date'][$date] = ($result[$region][$opt]['income']['date'][$date] ?? 0) + $income;

        if ($isCheck) {
            $result[$region][$opt]['checks']['value']++;
            $result[$region][$opt]['checks']['date'][$date] = ($result[$region][$opt]['checks']['date'][$date] ?? 0) + 1;
        }

//        if ($isCheckAll) {
//            $result[$region][$opt]['checksAll']['value']++;
//            $result[$region][$opt]['checksAll']['date'][$date] = ($result[$region][$opt]['checksAll']['date'][$date] ?? 0) + 1;
//            $result[$region][$opt]['checksAll']['value']++;
//            $result[$region][$opt]['checksAll']['date'][$date] = ($result[$region][$opt]['checksAll']['date'][$date] ?? 0) + 1;
//        }

        return $result;
    }

    /**
     * @param DateTime $dateFrom
     * @param DateTime $dateTill
     * @param Manager|null $manager
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    private function data(DateTime $dateFrom, DateTime $dateTill, Manager $manager = null): array
    {

//        SELECT SUM((ROUND(a.price-a.price*a.discount/100) - d.price) * c.quantity) AS summ, SUM((ROUND(a.price-a.price*a.discount/100)) * c.quantity) AS summ1, e.userID, e.optID, h.isService, a.zapSkladID, if(a.zapSkladID = 0, 0, 1) AS isSklad
//		FROM order_goods a
//		INNER JOIN orders b ON a.orderID = b.orderID
//		INNER JOIN expense c ON a.goodID = c.goodID
//		INNER JOIN income d ON c.incomeID = d.incomeID
//		INNER JOIN users e ON b.userID = e.userID
//		INNER JOIN opt g ON g.optID = e.optID
//		INNER JOIN expenseDocuments h ON a.expenseDocumentID = h.expenseDocumentID
//		WHERE a.expenseDocumentID <> 0 AND d.price > 0 AND a.number <> '15400PLMA03' $where
//		GROUP BY e.userID, isSklad, h.isService

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ROUND(og.price - og.price * og.discount / 100) AS priceGood',
                'i.price AS priceZak',
                "e.quantity",
                "u.optID",
                "u.userID",
                "u.name AS user_name",
                'ed.dateofadded',
                'ed.isService',
                'og.zapSkladID',
                'og.expenseDocumentID',
                'ed.doc_typeID',
                'ed.status AS expenseDocumentStatus',
                'ed.managerID',
                'ed.reseller_id',
                'if(og.zapSkladID IS NULL, 0, 1) AS isSklad',
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->innerJoin('e', 'income', 'i', 'e.incomeID = i.incomeID')
            ->innerJoin('o', 'users', 'u', 'o.userID = u.userID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'og.expenseDocumentID = ed.expenseDocumentID')
            ->andWhere('i.price > 0')
            ->andWhere("og.number <> '15400PLMA03'");


        $qb->andWhere($qb->expr()->gte('ed.dateofadded', ':date_from'));
        $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
        $qb->andWhere($qb->expr()->lte('ed.dateofadded', ':date_till'));
        $qb->setParameter('date_till', $dateTill->format('Y-m-d 23:59:59'));

        if ($manager) {
            $qb->andWhere('og.managerID = :managerID')->setParameter('managerID', $manager->getId());
        }

        return $qb->executeQuery()->fetchAllAssociative();
    }
}