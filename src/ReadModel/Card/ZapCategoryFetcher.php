<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Category\ZapCategory;
use Doctrine\ORM\EntityManagerInterface;

class ZapCategoryFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapCategory ::class);
    }

    public function get(int $id): ZapCategory
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('zapCategoryID, name')
            ->from('zapCategory')
            ->orderBy('number');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('c.*')
            ->from('zapCategory', 'c')
            ->orderBy('c.number')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}