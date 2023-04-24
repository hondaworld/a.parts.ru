<?php


namespace App\ReadModel\Shop;


use App\Model\Shop\Entity\Reseller\Reseller;
use Doctrine\ORM\EntityManagerInterface;

class ResellerFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Reseller::class);
    }

    public function get(int $id): Reseller
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'name'
            )
            ->from('resellers')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'd.id',
                'd.name',
                'd.isHide'
            )
            ->from('resellers', 'd')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}