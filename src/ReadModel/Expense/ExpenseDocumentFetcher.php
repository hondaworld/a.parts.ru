<?php


namespace App\ReadModel\Expense;


use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\User\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ExpenseDocumentFetcher
{
    private $connection;
    private $repository;
    private PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ExpenseDocument::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ExpenseDocument
    {
        return $this->repository->get($id);
    }

    public function assocIsShippingByUsers(array $users): array
    {
        $expenseDocuments = [];

        if (!$users) return [];

        $qb = $this->connection->createQueryBuilder()
            ->select("userID", "isShipping")
            ->from('expenseDocuments', 'e')
            ->andWhere('status = 0');
        $qb->andWhere($qb->expr()->in('userID', $users));

        $items = $qb->executeQuery()->fetchAllAssociative();

        foreach ($items as $item) {
            $expenseDocuments[$item['userID']] = $item['isShipping'];
        }

        return $expenseDocuments;
    }

    public function findByIds(array $arIds): array
    {
        if (!$arIds) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.expenseDocumentID',
                'a.document_num',
                'a.document_prefix',
                'a.document_sufix',
                'a.dateofadded',
                'd.name_short AS doc_name',
                'd.path AS doc_path',
                'm.name AS manager_name',
                'a.isService',
            )
            ->from('expenseDocuments', 'a')
            ->innerJoin('a', 'doc_types', 'd', 'a.doc_typeID = d.doc_typeID')
            ->innerJoin('a', 'managers', 'm', 'a.managerID = m.managerID');
        $qb->andWhere($qb->expr()->in('a.expenseDocumentID', $arIds));

        return $qb->executeQuery()->fetchAllAssociativeIndexed();
    }

    public function expenseForYearByZapCard(int $zapCardID, int $yearDecrease = 0): array
    {
        /*
         * SELECT ifnull(SUM(c.quantity), 0) AS quantity, if(DATE_FORMAT(h.dateofadded, '%c' ) < 10, CONCAT('0',DATE_FORMAT(h.dateofadded, '%c' )), DATE_FORMAT(h.dateofadded, '%c' )) AS month, DATE_FORMAT(h.dateofadded, '%Y' ) AS year
	FROM order_goods a
	INNER JOIN orders b ON a.orderID = b.orderID
	INNER JOIN expense c ON a.goodID = c.goodID
	INNER JOIN income d ON c.incomeID = d.incomeID
	INNER JOIN zapCards e ON d.zapCardID = e.zapCardID
	INNER JOIN creaters g ON g.createrID = e.createrID
	INNER JOIN expenseDocuments h ON a.expenseDocumentID = h.expenseDocumentID
	WHERE a.expenseDocumentID <> 0 AND d.price > 0 AND a.number = '" . mysql_real_escape_string($row->number) . "' AND e.createrID = '" . mysql_real_escape_string($row->createrID) . "' AND h.dateofadded >= '" . date("Y-m-d 00:00:00", mktime(0, 0, 0, date("m"), 1, date("Y") - 1)) . "'
	GROUP BY DATE_FORMAT(h.dateofadded, '%c' ), DATE_FORMAT(h.dateofadded, '%Y' )
         */

        $datefrom = new \DateTime();
        $datefrom->setDate(date('Y') - 1 - $yearDecrease, date('m') + 1, 1);
        $datetill = new \DateTime();
        $datetill->setDate(date('Y') - $yearDecrease, date('m') + 1, 1);

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ifnull(SUM(e.quantity), 0) AS quantity',
                "DATE_FORMAT(ed.dateofadded, '%c') AS month",
                "DATE_FORMAT(ed.dateofadded, '%Y') AS year"
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->innerJoin('e', 'income', 'i', 'e.incomeID = i.incomeID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'ed.expenseDocumentID = og.expenseDocumentID')
            ->andWhere('i.price > 0')
            ->andWhere('i.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere('ed.dateofadded >= :datefrom')
            ->setParameter('datefrom', $datefrom->format('Y-m-d') . ' 00:00:00')
            ->andWhere('ed.dateofadded < :datetill')
            ->setParameter('datetill', $datetill->format('Y-m-d') . ' 00:00:00')
            ->groupBy("DATE_FORMAT(ed.dateofadded, '%c'), DATE_FORMAT(ed.dateofadded, '%Y')");

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function saleForPeriod(\DateTime $dateFrom, \DateTime $dateTill): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "i.zapCardID",
                'ifnull(SUM(e.quantity), 0) AS quantity'
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->innerJoin('e', 'income', 'i', 'e.incomeID = i.incomeID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'ed.expenseDocumentID = og.expenseDocumentID')
            ->andWhere('ed.dateofadded >= :datefrom')
            ->setParameter('datefrom', $dateFrom->format('Y-m-d') . ' 00:00:00')
            ->andWhere('ed.dateofadded < :datetill')
            ->setParameter('datetill', $dateTill->format('Y-m-d') . ' 00:00:00')
            ->groupBy("i.zapCardID")
            ->orderBy('quantity', 'desc')
        ;

        return $qb->executeQuery()->fetchAllKeyValue();

    }

    public function saleForPeriodGroupOptUser(\DateTime $dateFrom, \DateTime $dateTill): array
    {

//        SELECT SUM((ROUND(a.price-a.price*a.discount/100)) * c.quantity) AS summ, e.name, e.userID, balanceLimit
//	FROM order_goods a
//	INNER JOIN orders b ON a.orderID = b.orderID
//	INNER JOIN expense c ON a.goodID = c.goodID
//	INNER JOIN income d ON c.incomeID = d.incomeID
//	INNER JOIN users e ON b.userID = e.userID
//	INNER JOIN expenseDocuments h ON a.expenseDocumentID = h.expenseDocumentID
//	WHERE a.expenseDocumentID <> 0 AND e.optID <> 1 $where
//	GROUP BY e.userID

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "u.userID",
                'ifNull(SUM((ROUND(og.price - og.price * og.discount/100)) * e.quantity), 0) AS sum'
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->innerJoin('e', 'income', 'i', 'e.incomeID = i.incomeID')
            ->innerJoin('o', 'users', 'u', 'o.userID = u.userID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'ed.expenseDocumentID = og.expenseDocumentID')
            ->andWhere('ed.dateofadded >= :datefrom')
            ->setParameter('datefrom', $dateFrom->format('Y-m-d') . ' 00:00:00')
            ->andWhere('ed.dateofadded < :datetill')
            ->setParameter('datetill', $dateTill->format('Y-m-d') . ' 00:00:00')
            ->andWhere('u.optID > 1')
            ->groupBy("u.userID");

        return $qb->executeQuery()->fetchAllKeyValue();

    }

    /**
     * @param User $user
     * @param int $page
     * @return PaginationInterface
     */
    public function allWithChecks(User $user, int $page): PaginationInterface
    {

        //        SELECT a.expenseDocumentID, a.document_num, a.dateofadded, c.summ, c.dateofadded AS checkdate, c.state, c.check_summ, c.fiscal_summ, c.kassa_id, c.managerID, f.name AS finance_type,
//    (SELECT ifnull(SUM((ROUND(price - price * discount/100)) * quantity), 0) FROM order_goods WHERE a.expenseDocumentID = expenseDocumentID) AS summReal
//    FROM expenseDocuments a
//    INNER JOIN finance_types f on a.finance_typeID = f.finance_typeID
//    LEFT JOIN checks c on a.expenseDocumentID = c.expenseDocumentID
//    WHERE $where";

        $qb = $this->connection->createQueryBuilder()
            ->select(
                "b.expenseDocumentID",
                'b.dateofadded',
                'ft.name AS finance_type',
                'b.document_num',
                'c.id AS check_id',
                'c.summ',
                'c.dateofadded AS checkdate',
                'c.state',
                'c.check_summ',
                'c.fiscal_summ',
                'c.kassa_id',
                'm.name AS manager_name',
                '(SELECT ifnull(SUM((ROUND(price - price * discount/100)) * quantity), 0) FROM order_goods WHERE b.expenseDocumentID = expenseDocumentID) AS summReal'
            )
            ->from('expenseDocuments', 'b')
            ->innerJoin('b', 'finance_types', 'ft', 'b.finance_typeID = ft.finance_typeID')
            ->leftJoin('b', 'checks', 'c', 'b.expenseDocumentID = c.expenseDocumentID')
            ->leftJoin('c', 'managers', 'm', 'c.managerID = m.managerID')
            ->where('b.userID = :userID')
            ->setParameter('userID', $user->getId())
            ->orderBy('b.dateofadded', 'DESC');

        $sort = 'b.dateofadded';
        $direction = 'DESC';
        $size = 50;

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    public function getNextTCH(Firm $firm): int
    {
        return $this->getNextDocumentNumber($firm, [DocumentType::TCH]);
    }

    public function getNextRN(Firm $firm): int
    {
        if ($this->getCountDocuments($firm, [DocumentType::RN]) == 0) {
            return $firm->getFirstNakladnaya();
        }

        return $this->getNextDocumentNumber($firm, [DocumentType::RN]);
    }

    private function getNextDocumentNumber(Firm $firm, array $doc_types): int
    {
        $documentNumber = $this->connection->createQueryBuilder()
            ->select('IfNull(Max(document_num), 0)')
            ->from('expenseDocuments', 'a')
            ->andWhere('a.firmID = :firmID')
            ->setParameter('firmID', $firm->getId())
            ->andWhere('a.doc_typeID IN (' . implode(',', $doc_types) . ')')
            ->andWhere('YEAR(dateofadded) = ' . (new \DateTime())->format('Y') . '')
            ->executeQuery()
            ->fetchOne();
        return $documentNumber + 1;
    }

    private function getCountDocuments(Firm $firm, array $doc_types): int
    {
        return $this->connection->createQueryBuilder()
            ->select('Count(*)')
            ->from('expenseDocuments', 'a')
            ->andWhere('a.firmID = :firmID')
            ->setParameter('firmID', $firm->getId())
            ->andWhere('a.doc_typeID IN (' . implode(',', $doc_types) . ')')
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * @param int $document_num
     * @param int $year
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByDocumentNumAndYear(int $document_num, int $year): array
    {
        return $this->connection->createQueryBuilder()
            ->select(
                'ed.doc_typeID',
                'ed.expenseDocumentID AS id',
                'ed.document_num',
                'ed.dateofadded',
                'f.name_short AS from_name',
                'f.firmID',
                'u.name AS to_name',
                'u.userID',
                'u.edo',
                '(SELECT SUM(ROUND(price - price * discount / 100) * quantity) FROM order_goods WHERE expenseDocumentID = ed.expenseDocumentID) AS sum'
            )
            ->from('expenseDocuments', 'ed')
            ->innerJoin('ed', 'firms', 'f', 'ed.firmID = f.firmID')
            ->innerJoin('ed', 'users', 'u', 'ed.userID = u.userID')
            ->andWhere('ed.document_num = :document_num')
            ->setParameter('document_num', $document_num)
            ->andWhere("YEAR(ed.dateofadded) = :year")
            ->setParameter('year', $year)
            ->orderBy('ed.dateofadded', 'desc')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * @param int $document_num
     * @param int $year
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByDocumentNumAndYearNotShipping(int $document_num, int $year): array
    {
        return $this->connection->createQueryBuilder()
            ->select(
                'ed.doc_typeID',
                'dt.name_short AS doc_type_name',
                'ed.expenseDocumentID AS id',
                'ed.document_num',
                'ed.dateofadded',
                'f.name_short AS from_name',
                'f.firmID',
                'u.name AS to_name',
                'u.userID'
            )
            ->from('expenseDocuments', 'ed')
            ->innerJoin('ed', 'doc_types', 'dt', 'ed.doc_typeID = dt.doc_typeID')
            ->innerJoin('ed', 'firms', 'f', 'ed.firmID = f.firmID')
            ->innerJoin('ed', 'users', 'u', 'ed.userID = u.userID')
            ->andWhere('ed.document_num = :document_num')
            ->setParameter('document_num', $document_num)
            ->andWhere("YEAR(ed.dateofadded) = :year")
            ->setParameter('year', $year)
            ->andWhere('ed.expenseDocumentID NOT IN (SELECT expenseDocumentID FROM shippings WHERE expenseDocumentID IS NOT NULL)')
            ->orderBy('ed.dateofadded', 'desc')
            ->executeQuery()
            ->fetchAllAssociative();
    }
}