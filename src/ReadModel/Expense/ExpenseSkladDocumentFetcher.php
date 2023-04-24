<?php


namespace App\ReadModel\Expense;


use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Expense\Entity\SkladDocument\ExpenseSkladDocument;
use Doctrine\ORM\EntityManagerInterface;

class ExpenseSkladDocumentFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ExpenseSkladDocument::class);
    }

    public function get(int $id): ExpenseSkladDocument
    {
        return $this->repository->get($id);
    }

    public function getNextNP(int $firmID): int
    {
        return $this->getNextDocumentNumber($firmID, [DocumentType::NP]);
    }

    private function getNextDocumentNumber(int $firmID, array $doc_types): int
    {
        $documentNumber = $this->connection->createQueryBuilder()
            ->select(
                'IfNull(Max(document_num), 0)',
            )
            ->from('expense_skladDocuments', 'a')
            ->andWhere('a.firmID = :firmID')
            ->setParameter('firmID', $firmID)
            ->andWhere('a.doc_typeID IN (' . implode(',', $doc_types) . ')')
            ->andWhere('YEAR(dateofadded) = ' . (new \DateTime())->format('Y') . '')
            ->executeQuery()
            ->fetchOne()
        ;
        return $documentNumber + 1;
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
                DocumentType::NP . ' AS doc_typeID',
                'esd.expense_skladDocumentID AS id',
                'esd.document_num',
                'esd.dateofadded',
                'f.name_short AS to_name',
                'f.name_short AS from_name',
                'f.firmID',
                '(SELECT SUM(bb.price * aa.quantity) FROM expense_sklad aa INNER JOIN income bb ON aa.incomeID = bb.incomeID WHERE aa.expense_skladDocumentID = esd.expense_skladDocumentID) AS sum'
            )
            ->from('expense_skladDocuments', 'esd')
            ->innerJoin('esd', 'firms', 'f', 'esd.firmID = f.firmID')
            ->andWhere('esd.document_num = :document_num')
            ->setParameter('document_num', $document_num)
            ->andWhere("YEAR(esd.dateofadded) = :year")
            ->setParameter('year', $year)
            ->orderBy('esd.dateofadded', 'desc')
            ->executeQuery()
            ->fetchAllAssociative();
    }

}