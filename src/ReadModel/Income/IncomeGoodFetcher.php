<?php


namespace App\ReadModel\Income;


use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Income\Entity\Good\IncomeGood;
use App\Model\Income\Entity\Income\Income;
use Doctrine\ORM\EntityManagerInterface;

class IncomeGoodFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(IncomeGood::class);
    }

    public function get(int $id): IncomeGood
    {
        return $this->repository->get($id);
    }

    public function findByIncomeWithDocument(Income $income): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'id.incomeDocumentID',
                'id.doc_typeID',
                'id.document_num',
                'id.dateofadded',
                'g.zapSkladID',
                'id.firmID',
                'g.quantity'
            )
            ->from('income_goods', 'g')
            ->innerJoin('g', 'incomeDocuments', 'id', 'id.incomeDocumentID = g.incomeDocumentID')
            ->where('g.incomeID = :incomeID')
            ->setParameter('incomeID', $income->getId());

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function findByZapCardWithDocument(ZapCard $zapCard): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'id.incomeDocumentID',
                'id.doc_typeID',
                'id.document_num',
                'id.dateofadded',
                'g.zapSkladID',
                'id.firmID',
                'g.quantity'
            )
            ->from('income_goods', 'g')
            ->innerJoin('g', 'incomeDocuments', 'id', 'id.incomeDocumentID = g.incomeDocumentID')
            ->where('g.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCard->getId());

        return $qb->executeQuery()->fetchAllAssociative();
    }

}