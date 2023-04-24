<?php


namespace App\ReadModel\Auto;


use App\Model\Auto\Entity\Marka\AutoMarka;
use Doctrine\ORM\EntityManagerInterface;

class AutoMarkaFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(AutoMarka ::class);
    }

    public function get(int $id): AutoMarka
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('auto_markaID, name')
            ->from('auto_marka')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('m.*')
            ->from('auto_marka', 'm')
            ->orderBy('m.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}