<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Abc\Abc;
use Doctrine\ORM\EntityManagerInterface;

class AbcFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Abc ::class);
    }

    public function get(int $id): Abc
    {
        return $this->repository->get($id);
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('a.*')
            ->from('abc', 'a')
            ->orderBy('a.abc')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}