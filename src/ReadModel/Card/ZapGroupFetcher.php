<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Category\ZapCategory;
use App\Model\Card\Entity\Group\ZapGroup;
use Doctrine\ORM\EntityManagerInterface;

class ZapGroupFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapGroup ::class);
    }

    public function get(int $id): ZapGroup
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('zapGroupID, name')
            ->from('zapGroup')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function allByCategory(ZapCategory $zapCategory): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('g.*')
            ->from('zapGroup', 'g')
            ->where('zapCategoryID = :zapCategoryID')
            ->setParameter('zapCategoryID', $zapCategory->getId())
            ->orderBy('g.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}