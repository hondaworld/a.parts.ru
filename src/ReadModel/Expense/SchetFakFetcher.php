<?php


namespace App\ReadModel\Expense;


use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Expense\Entity\SchetFak\SchetFak;
use App\Model\Firm\Entity\Firm\Firm;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class SchetFakFetcher
{
    private $connection;
    private $repository;
    private PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(SchetFak::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): SchetFak
    {
        return $this->repository->get($id);
    }

    public function getNext(Firm $firm): int
    {
        if ($this->getCountDocuments($firm) == 0) {
            return $firm->getFirstSchetfak();
        }

        return $this->getNextDocumentNumber($firm);
    }

    private function getNextDocumentNumber(Firm $firm): int
    {
        $documentNumber = $this->connection->createQueryBuilder()
            ->select('IfNull(Max(document_num), 0)')
            ->from('schet_fak', 'a')
            ->andWhere('a.firmID = :firmID')
            ->setParameter('firmID', $firm->getId())
            ->andWhere('YEAR(dateofadded) = ' . (new \DateTime())->format('Y') . '')
            ->executeQuery()
            ->fetchOne();
        return $documentNumber + 1;
    }

    private function getCountDocuments(Firm $firm): int
    {
        return $this->connection->createQueryBuilder()
            ->select('Count(*)')
            ->from('schet_fak', 'a')
            ->andWhere('a.firmID = :firmID')
            ->setParameter('firmID', $firm->getId())
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * @param int $document_num
     * @return array
     * @throws \Exception
     */
    public function allByDocumentNum(int $document_num): array
    {
        return $this->connection->createQueryBuilder()
            ->select(
                'sf.schet_fakID',
                'sf.firmID',
                'f.name_short AS firm_name',
                'sf.dateofadded',
                'sf.document_num',
                'u.name AS user_name',
                'u.userID',
                '(SELECT SUM(ROUND(price - price * discount / 100) * quantity) FROM order_goods WHERE expenseDocumentID = ed.expenseDocumentID) AS sum'
            )
            ->from('schet_fak', 'sf')
            ->innerJoin('sf', 'firms', 'f', 'sf.firmID = f.firmID')
            ->innerJoin('sf', 'expenseDocuments', 'ed', 'sf.expenseDocumentID = ed.expenseDocumentID')
            ->innerJoin('ed', 'users', 'u', 'ed.userID = u.userID')
            ->andWhere('sf.document_num = :document_num')
            ->setParameter('document_num', $document_num)
            ->andWhere("sf.dateofadded >= '2013-01-01'")
            ->orderBy('sf.dateofadded', 'desc')
            ->executeQuery()
            ->fetchAllAssociative();
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
                DocumentType::SF . ' AS doc_typeID',
                'sf.schet_fakID AS id',
                'sf.firmID',
                'f.name_short AS from_name',
                'sf.dateofadded',
                'sf.document_num',
                'u.name AS to_name',
                'u.userID',
                'u.edo',
                '(SELECT SUM(ROUND(price - price * discount / 100) * quantity) FROM order_goods WHERE expenseDocumentID = ed.expenseDocumentID) AS sum'
            )
            ->from('schet_fak', 'sf')
            ->innerJoin('sf', 'firms', 'f', 'sf.firmID = f.firmID')
            ->innerJoin('sf', 'expenseDocuments', 'ed', 'sf.expenseDocumentID = ed.expenseDocumentID')
            ->innerJoin('ed', 'users', 'u', 'ed.userID = u.userID')
            ->andWhere('sf.document_num = :document_num')
            ->setParameter('document_num', $document_num)
            ->andWhere("YEAR(sf.dateofadded) = :year")
            ->setParameter('year', $year)
            ->orderBy('sf.dateofadded', 'desc')
            ->executeQuery()
            ->fetchAllAssociative();
    }
}