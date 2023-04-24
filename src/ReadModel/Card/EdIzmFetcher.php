<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Measure\EdIzm;
use Doctrine\ORM\EntityManagerInterface;

class EdIzmFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(EdIzm ::class);
    }

    public function get(int $id): EdIzm
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('ed_izmID, name')
            ->from('ed_izm')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('e.*')
            ->from('ed_izm', 'e')
            ->orderBy('e.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}