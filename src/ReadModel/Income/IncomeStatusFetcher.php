<?php


namespace App\ReadModel\Income;


use App\Model\Income\Entity\Status\IncomeStatus;
use Doctrine\ORM\EntityManagerInterface;
use function Doctrine\DBAL\Query\QueryBuilder;

class IncomeStatusFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(IncomeStatus::class);
    }

    public function get(int $id): IncomeStatus
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('status, name')
            ->from('income_statuses')
            ->orderBy('number');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocDeleted(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('status, name')
            ->from('income_statuses')
            ->orderBy('number');

        $qb->andWhere($qb->expr()->in('status', IncomeStatus::ARR_DELETED));

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocExcludeDeleted(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('status, name')
            ->from('income_statuses')
            ->orderBy('number');

        $qb->andWhere($qb->expr()->notIn('status', IncomeStatus::ARR_DELETED));

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocAllowChange(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('status, name')
            ->from('income_statuses')
            ->where("status NOT IN (1,8,5,10,11)")
            ->orderBy('number');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocForReportIncomeGoods(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('status, name')
            ->from('income_statuses')
            ->orderBy('number');

        $qb->andWhere($qb->expr()->in('status', array_merge(IncomeStatus::ARR_IN_PATH, [IncomeStatus::DEFAULT_STATUS])));

        return $qb->executeQuery()->fetchAllKeyValue();
    }

}