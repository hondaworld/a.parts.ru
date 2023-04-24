<?php


namespace App\ReadModel\Auto;


use App\Model\Auto\Entity\Generation\AutoGeneration;
use App\Model\Auto\Entity\Model\AutoModel;
use Doctrine\ORM\EntityManagerInterface;

class AutoGenerationFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(AutoGeneration ::class);
    }

    public function get(int $id): AutoGeneration
    {
        return $this->repository->get($id);
    }

    public function allByModel(AutoModel $autoModel): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('g.*')
            ->from('auto_generation', 'g')
            ->andWhere('g.auto_modelID = :auto_modelID')
            ->setParameter('auto_modelID', $autoModel->getId())
            ->orderBy('g.yearfrom')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}