<?php


namespace App\ReadModel\Firm;


use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Firm\Entity\Schet\Schet;
use App\ReadModel\Firm\Filter\AllDocuments\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class AllDocumentsFetcher
{
    private $connection;
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
    }

    // Перемещение
    private function expenseSkladDocuments(DocumentType $documentType, Filter $filter): \Doctrine\DBAL\Query\QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.expense_skladDocumentID AS id',
                'a.document_num',
                'a.dateofadded',
                'f.name_short AS to_name',
                'f.name_short AS from_name',
                'f.firmID',
                '(SELECT SUM(bb.price * aa.quantity) FROM expense_sklad aa INNER JOIN income bb ON aa.incomeID = bb.incomeID WHERE aa.expense_skladDocumentID = a.expense_skladDocumentID) AS sum'
            )
            ->from('expense_skladDocuments', 'a')
            ->innerJoin('a', 'firms', 'f', 'a.firmID = f.firmID')
        ;

        if ($filter->from_name) {
            $qb->andWhere($qb->expr()->like('f.name', ':from_name'));
            $qb->setParameter('from_name', '%' . mb_strtolower($filter->from_name) . '%');
        }

        if ($filter->to_name) {
            $qb->andWhere($qb->expr()->like('f.name', ':to_name'));
            $qb->setParameter('to_name', '%' . mb_strtolower($filter->to_name) . '%');
        }

        if ($filter->document_num) {
            $qb->andWhere('a.document_num = :document_num');
            $qb->setParameter('document_num', $filter->document_num);
        }
        return $qb;
    }

    // Расходная накладная, товарный чек
    private function expenseDocuments(DocumentType $documentType, Filter $filter): \Doctrine\DBAL\Query\QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.expenseDocumentID AS id',
                'a.document_num',
                'a.dateofadded',
                'f.name_short AS from_name',
                'f.firmID',
                'u.name AS to_name',
                'u.userID',
                'u.edo',
                '(SELECT SUM(ROUND(price - price * discount / 100) * quantity) FROM order_goods WHERE expenseDocumentID = a.expenseDocumentID) AS sum'
            )
            ->from('expenseDocuments', 'a')
            ->innerJoin('a', 'firms', 'f', 'a.firmID = f.firmID')
            ->innerJoin('a', 'users', 'u', 'a.userID = u.userID')
            ->andWhere('a.doc_typeID = :doc_typeID')
            ->setParameter('doc_typeID', $documentType->getId());

        if ($filter->from_name) {
            $qb->andWhere($qb->expr()->like('f.name', ':from_name'));
            $qb->setParameter('from_name', '%' . mb_strtolower($filter->from_name) . '%');
        }

        if ($filter->to_name) {
            $qb->andWhere($qb->expr()->like('u.name', ':to_name'));
            $qb->setParameter('to_name', '%' . mb_strtolower($filter->to_name) . '%');
        }

        if ($filter->document_num) {
            $qb->andWhere('a.document_num = :document_num');
            $qb->setParameter('document_num', $filter->document_num);
        }
        return $qb;
    }

    // Корректирующий счет-фактура
    private function schetFakKor(DocumentType $documentType, Filter $filter): \Doctrine\DBAL\Query\QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.schet_fak_korID AS id',
                'a.firmID',
                'f.name_short AS from_name',
                'a.dateofadded',
                'a.document_num',
                'u.name AS to_name',
                'u.userID',
                'u.edo',
                '(SELECT SUM(price * quantity) FROM income WHERE incomeDocumentID IN (SELECT incomeDocumentID FROM link_SFK_VZ WHERE schet_fak_korID = a.schet_fak_korID)) AS sum'
            )
            ->from('schet_fak_kor', 'a')
            ->innerJoin('a', 'schet_fak', 'sf', 'a.schet_fakID = sf.schet_fakID')
            ->innerJoin('a', 'firms', 'f', 'a.firmID = f.firmID')
            ->innerJoin('sf', 'expenseDocuments', 'ed', 'sf.expenseDocumentID = ed.expenseDocumentID')
            ->innerJoin('ed', 'users', 'u', 'ed.userID = u.userID')
        ;

        if ($filter->from_name) {
            $qb->andWhere($qb->expr()->like('f.name', ':from_name'));
            $qb->setParameter('from_name', '%' . mb_strtolower($filter->from_name) . '%');
        }

        if ($filter->to_name) {
            $qb->andWhere($qb->expr()->like('u.name', ':to_name'));
            $qb->setParameter('to_name', '%' . mb_strtolower($filter->to_name) . '%');
        }

        if ($filter->document_num) {
            $qb->andWhere('a.document_num = :document_num');
            $qb->setParameter('document_num', $filter->document_num);
        }
        return $qb;
    }

    // Счет-фактура
    private function schetFak(DocumentType $documentType, Filter $filter): \Doctrine\DBAL\Query\QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.schet_fakID AS id',
                'a.firmID',
                'f.name_short AS from_name',
                'a.dateofadded',
                'a.document_num',
                'u.name AS to_name',
                'u.userID',
                'u.edo',
                '(SELECT SUM(ROUND(price - price * discount / 100) * quantity) FROM order_goods WHERE expenseDocumentID = ed.expenseDocumentID) AS sum'
            )
            ->from('schet_fak', 'a')
            ->innerJoin('a', 'firms', 'f', 'a.firmID = f.firmID')
            ->innerJoin('a', 'expenseDocuments', 'ed', 'a.expenseDocumentID = ed.expenseDocumentID')
            ->innerJoin('ed', 'users', 'u', 'ed.userID = u.userID')
        ;

        if ($filter->from_name) {
            $qb->andWhere($qb->expr()->like('f.name', ':from_name'));
            $qb->setParameter('from_name', '%' . mb_strtolower($filter->from_name) . '%');
        }

        if ($filter->to_name) {
            $qb->andWhere($qb->expr()->like('u.name', ':to_name'));
            $qb->setParameter('to_name', '%' . mb_strtolower($filter->to_name) . '%');
        }

        if ($filter->document_num) {
            $qb->andWhere('a.document_num = :document_num');
            $qb->setParameter('document_num', $filter->document_num);
        }
        return $qb;
    }

    // Счет
    private function schet(DocumentType $documentType, Filter $filter): \Doctrine\DBAL\Query\QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.schetID AS id',
                'a.firmID',
                'f.name_short AS from_name',
                'a.dateofadded',
                'a.schet_num AS document_num',
                'a.userID',
                'u.name AS to_name',
                'a.finance_typeID',
                '(SELECT SUM(price * quantity) FROM schet_goods WHERE schetID = a.schetID) AS sum'
            )
            ->from('schet', 'a')
            ->innerJoin('a', 'firms', 'f', 'a.firmID = f.firmID')
            ->innerJoin('a', 'users', 'u', 'a.userID = u.userID')
        ;

        if ($filter->from_name) {
            $qb->andWhere($qb->expr()->like('f.name', ':from_name'));
            $qb->setParameter('from_name', '%' . mb_strtolower($filter->from_name) . '%');
        }

        if ($filter->to_name) {
            $qb->andWhere($qb->expr()->like('u.name', ':to_name'));
            $qb->setParameter('to_name', '%' . mb_strtolower($filter->to_name) . '%');
        }

        if ($filter->document_num) {
            $qb->andWhere('a.schet_num = :schet_num');
            $qb->setParameter('schet_num', $filter->document_num);
        }
        return $qb;
    }

    // Приходная накладная, возврат клиента
    private function incomeDocuments(DocumentType $documentType, Filter $filter): \Doctrine\DBAL\Query\QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.incomeDocumentID AS id',
                'a.firmID',
                'f.name_short AS to_name',
                'a.dateofadded',
                'a.document_num',
                "CONCAT(u.name, ' - ', p.name) AS from_name",
                'u.userID',
                '(SELECT SUM(price * quantity) FROM income WHERE incomeDocumentID = a.incomeDocumentID) AS sum'
            )
            ->from('incomeDocuments', 'a')
            ->innerJoin('a', 'firms', 'f', 'a.firmID = f.firmID')
            ->innerJoin('a', 'users', 'u', 'a.userID = u.userID')
            ->innerJoin('a', 'providers', 'p', 'a.providerID = p.providerID')
            ->andWhere('a.doc_typeID = :doc_typeID')
            ->setParameter('doc_typeID', $documentType->getId())
        ;

        if ($filter->from_name) {
            $qb->andWhere($qb->expr()->like('u.name', ':from_name'));
            $qb->setParameter('from_name', '%' . mb_strtolower($filter->from_name) . '%');
        }

        if ($filter->to_name) {
            $qb->andWhere($qb->expr()->like('f.name', ':to_name'));
            $qb->setParameter('to_name', '%' . mb_strtolower($filter->to_name) . '%');
        }

        if ($filter->document_num) {
            $qb->andWhere('a.document_num = :document_num');
            $qb->setParameter('document_num', $filter->document_num);
        }
        return $qb;
    }

    // Возврат поставщику, списание
    private function incomeDocumentsReturn(DocumentType $documentType, Filter $filter): \Doctrine\DBAL\Query\QueryBuilder
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.incomeDocumentID AS id',
                'a.firmID',
                'f.name_short AS from_name',
                'a.dateofadded',
                'a.document_num',
                'u.name AS to_name',
                'u.userID',
                '(SELECT SUM(i.price * ig.quantity) FROM income_goods ig INNER JOIN income i ON i.incomeID = ig.incomeID WHERE ig.incomeDocumentID = a.incomeDocumentID) AS sum'
            )
            ->from('incomeDocuments', 'a')
            ->innerJoin('a', 'firms', 'f', 'a.firmID = f.firmID')
            ->innerJoin('a', 'users', 'u', 'a.userID = u.userID')
            ->andWhere('a.doc_typeID = :doc_typeID')
            ->setParameter('doc_typeID', $documentType->getId())
        ;

        if ($filter->from_name) {
            $qb->andWhere($qb->expr()->like('u.name', ':from_name'));
            $qb->setParameter('from_name', '%' . mb_strtolower($filter->from_name) . '%');
        }

        if ($filter->to_name) {
            $qb->andWhere($qb->expr()->like('f.name', ':to_name'));
            $qb->setParameter('to_name', '%' . mb_strtolower($filter->to_name) . '%');
        }

        if ($filter->document_num) {
            $qb->andWhere('a.document_num = :document_num');
            $qb->setParameter('document_num', $filter->document_num);
        }
        return $qb;
    }

    /**
     * @param DocumentType $documentType
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     * @throws \Exception
     */
    public function all(DocumentType $documentType, Filter $filter, int $page, array $settings): PaginationInterface
    {
        if (in_array($documentType->getId(), [DocumentType::RN, DocumentType::TCH])) {
            $qb = $this->expenseDocuments($documentType, $filter);
        }

        if (in_array($documentType->getId(), [DocumentType::PN, DocumentType::VZ])) {
            $qb = $this->incomeDocuments($documentType, $filter);
        }

        if (in_array($documentType->getId(), [DocumentType::VN, DocumentType::WON])) {
            $qb = $this->incomeDocumentsReturn($documentType, $filter);
        }

        if ($documentType->getId() == DocumentType::SFK) {
            $qb = $this->schetFakKor($documentType, $filter);
        }

        if ($documentType->getId() == DocumentType::SF) {
            $qb = $this->schetFak($documentType, $filter);
        }

        if ($documentType->getId() == DocumentType::S) {
            $qb = $this->schet($documentType, $filter);
        }

        if ($documentType->getId() == DocumentType::NP) {
            $qb = $this->expenseSkladDocuments($documentType, $filter);
        }

        if ($filter->dateofadded) {
            if ($filter->dateofadded['date_from']) {
                $qb->andWhere($qb->expr()->gte('a.dateofadded', ':date_from'));
                $qb->setParameter('date_from', (new \DateTime($filter->dateofadded['date_from']))->format('Y-m-d 00:00:00'));
            }
            if ($filter->dateofadded['date_till']) {
                $qb->andWhere($qb->expr()->lte('a.dateofadded', ':date_till'));
                $qb->setParameter('date_till', (new \DateTime($filter->dateofadded['date_till']))->format('Y-m-d 23:59:59'));
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['dateofadded', 'from_name', 'to_name', 'documtns_num', 'sum'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

}