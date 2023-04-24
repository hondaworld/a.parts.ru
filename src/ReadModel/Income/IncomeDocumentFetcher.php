<?php


namespace App\ReadModel\Income;


use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Income\Entity\Document\IncomeDocument;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class IncomeDocumentFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(IncomeDocument::class);
    }

    public function get(int $id): IncomeDocument
    {
        return $this->repository->get($id);
    }

    /**
     * @param int $firmID
     * @return int
     * @throws Exception
     */
    public function getNextPN(int $firmID): int
    {
        return $this->getNextDocumentNumber($firmID, [DocumentType::PN]);
    }

    /**
     * @param int $firmID
     * @return int
     * @throws Exception
     */
    public function getNextVN(int $firmID): int
    {
        return $this->getNextDocumentNumber($firmID, [DocumentType::VN]);
    }

    /**
     * @param int $firmID
     * @return int
     * @throws Exception
     */
    public function getNextWON(int $firmID): int
    {
        return $this->getNextDocumentNumber($firmID, [DocumentType::WON]);
    }

    /**
     * @param int $firmID
     * @return int
     * @throws Exception
     */
    public function getNextVZ(int $firmID): int
    {
        return $this->getNextDocumentNumber($firmID, [DocumentType::VZ]);
    }

    /**
     * @param int $firmID
     * @param array $doc_types
     * @return int
     * @throws Exception
     */
    private function getNextDocumentNumber(int $firmID, array $doc_types): int
    {
        $documentNumber = $this->connection->createQueryBuilder()
            ->select(
                'IfNull(Max(document_num), 0)',
            )
            ->from('incomeDocuments', 'a')
            ->andWhere('a.firmID = :firmID')
            ->setParameter('firmID', $firmID)
            ->andWhere('a.doc_typeID IN (' . implode(',', $doc_types) . ')')
            ->andWhere('YEAR(dateofadded) = ' . (new \DateTime())->format('Y'))
            ->executeQuery()
            ->fetchOne();
        return $documentNumber + 1;
    }

    public function findByIds(array $arIds): array
    {
        if (!$arIds) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.incomeDocumentID',
                'a.document_num',
                'a.document_prefix',
                'a.document_sufix',
                'a.dateofadded',
                'd.name_short AS doc_name',
                'd.path AS doc_path',
            )
            ->from('incomeDocuments', 'a')
            ->innerJoin('a', 'doc_types', 'd', 'a.doc_typeID = d.doc_typeID');
        $qb->andWhere($qb->expr()->in('a.incomeDocumentID', $arIds));

        return $qb->executeQuery()->fetchAllAssociativeIndexed();
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
                'id.incomeDocumentID',
                'id.firmID',
                'f.name_short AS firm_name',
                'id.dateofadded',
                'id.document_num',
                'u.name AS user_name',
                'u.userID',
                '(SELECT SUM(price * quantity) FROM income WHERE incomeDocumentID = id.incomeDocumentID) AS sum'
            )
            ->from('incomeDocuments', 'id')
            ->innerJoin('id', 'firms', 'f', 'id.firmID = f.firmID')
            ->innerJoin('id', 'users', 'u', 'id.userID = u.userID')
            ->andWhere('id.document_num = :document_num')
            ->setParameter('document_num', $document_num)
            ->andWhere("id.dateofadded >= '2013-01-01'")
            ->andWhere('doc_typeID = :doc_typeID')
            ->setParameter('doc_typeID', DocumentType::VZ)
            ->orderBy('id.dateofadded', 'desc')
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
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'id.doc_typeID',
                'id.incomeDocumentID AS id',
                'id.firmID',
                'f.name_short AS to_name',
                'id.dateofadded',
                'id.document_num',
                'u.name AS from_name',
                'u.userID',
                '(SELECT SUM(price * quantity) FROM income WHERE incomeDocumentID = id.incomeDocumentID) AS sum'
            )
            ->from('incomeDocuments', 'id')
            ->innerJoin('id', 'firms', 'f', 'id.firmID = f.firmID')
            ->innerJoin('id', 'users', 'u', 'id.userID = u.userID')
            ->andWhere('id.document_num = :document_num')
            ->setParameter('document_num', $document_num)
            ->andWhere("YEAR(id.dateofadded) = :year")
            ->setParameter('year', $year)
            ->orderBy('id.dateofadded', 'desc');

        $qb->andWhere($qb->expr()->in('id.doc_typeID', [DocumentType::PN, DocumentType::VZ]));

        $arr1 = $qb->executeQuery()->fetchAllAssociative();

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'id.doc_typeID',
                'id.incomeDocumentID AS id',
                'id.firmID',
                'f.name_short AS from_name',
                'id.dateofadded',
                'id.document_num',
                'u.name AS to_name',
                'u.userID',
                '(SELECT SUM(i.price * ig.quantity) FROM income_goods ig INNER JOIN income i ON i.incomeID = ig.incomeID WHERE ig.incomeDocumentID = id.incomeDocumentID) AS sum'
            )
            ->from('incomeDocuments', 'id')
            ->innerJoin('id', 'firms', 'f', 'id.firmID = f.firmID')
            ->innerJoin('id', 'users', 'u', 'id.userID = u.userID')
            ->andWhere('id.document_num = :document_num')
            ->setParameter('document_num', $document_num)
            ->andWhere("YEAR(id.dateofadded) = :year")
            ->setParameter('year', $year)
            ->orderBy('id.dateofadded', 'desc');

        $qb->andWhere($qb->expr()->in('id.doc_typeID', [DocumentType::VN, DocumentType::WON]));

        $arr2 = $qb->executeQuery()->fetchAllAssociative();

        return array_merge($arr1, $arr2);
    }
}