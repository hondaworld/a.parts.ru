<?php


namespace App\ReadModel\Auto;


use App\Model\Auto\Entity\Modification\AutoModification;
use App\Model\Work\Entity\Period\WorkPeriod;
use Doctrine\ORM\EntityManagerInterface;

class WorkPeriodFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(WorkPeriod ::class);
    }

    public function get(int $id): WorkPeriod
    {
        return $this->repository->get($id);
    }

    public function allByModification(AutoModification $autoModification): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('w.*')
            ->from('workPeriod', 'w')
            ->andWhere('w.auto_modificationID = :auto_modificationID')
            ->setParameter('auto_modificationID', $autoModification->getId())
            ->orderBy('w.number')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}