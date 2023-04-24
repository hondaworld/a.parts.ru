<?php


namespace App\ReadModel\Income;


use App\Model\Income\Entity\StatusHistory\IncomeStatusHistory;
use Doctrine\ORM\EntityManagerInterface;

class IncomeStatusHistoryFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(IncomeStatusHistory::class);
    }

    public function get(int $id): IncomeStatusHistory
    {
        return $this->repository->get($id);
    }

    public function allByIncome(int $incomeID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'h.dateofadded',
                's.name AS status_name',
                'm.name AS manager'
            )
            ->from('income_status_history', 'h')
            ->innerJoin('h', 'income_statuses', 's', 'h.status = s.status')
            ->innerJoin('h', 'managers', 'm', 'h.managerID = m.managerID')
            ->where('h.incomeID = :incomeID')
            ->setParameter('incomeID', $incomeID)
            ->orderBy('dateofadded');

        return $qb->executeQuery()->fetchAllAssociative();
    }

}