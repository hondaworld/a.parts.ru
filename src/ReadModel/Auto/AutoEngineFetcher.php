<?php


namespace App\ReadModel\Auto;


use App\Model\Auto\Entity\Engine\AutoEngine;
use App\Model\Auto\Entity\Generation\AutoGeneration;
use Doctrine\ORM\EntityManagerInterface;

class AutoEngineFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(AutoEngine ::class);
    }

    public function get(int $id): AutoEngine
    {
        return $this->repository->get($id);
    }

    public function allByGeneration(AutoGeneration $autoGeneration): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('e.*')
            ->from('auto_engine', 'e')
            ->andWhere('e.auto_generationID = :auto_generationID')
            ->setParameter('auto_generationID', $autoGeneration->getId())
            ->orderBy('e.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}