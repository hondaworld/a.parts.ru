<?php


namespace App\ReadModel\Auto;


use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\MotoGroup\MotoGroup;
use Doctrine\ORM\EntityManagerInterface;

class MotoGroupFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(MotoGroup ::class);
    }

    public function get(int $id): MotoGroup
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("moto_groupID, name")
            ->from('moto_group')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('g.*')
            ->from('moto_group', 'g')
            ->orderBy('g.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}