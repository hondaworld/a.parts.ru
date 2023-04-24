<?php


namespace App\ReadModel\Auto;


use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\MotoModel\MotoModel;
use Doctrine\ORM\EntityManagerInterface;

class MotoModelFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(MotoModel ::class);
    }

    public function get(int $id): MotoModel
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("moto_modelID, CONCAT(brand.name, ' ', model.name) AS name")
            ->from('moto_model', 'model')
            ->innerJoin('model', 'auto_marka', 'brand', 'model.auto_markaID = brand.auto_markaID')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function allByMarka(AutoMarka $autoMarka): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('m.*, g.name AS gr')
            ->from('moto_model', 'm')
            ->innerJoin('m', 'moto_group', 'g', 'm.moto_groupID = g.moto_groupID')
            ->andWhere('m.auto_markaID = :auto_markaID')
            ->setParameter('auto_markaID', $autoMarka->getId())
            ->orderBy('m.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}