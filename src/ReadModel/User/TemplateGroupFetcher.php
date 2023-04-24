<?php


namespace App\ReadModel\User;


use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use Doctrine\ORM\EntityManagerInterface;

class TemplateGroupFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(TemplateGroup::class);
    }

    public function get(int $id): TemplateGroup
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('templateGroupID, name')
            ->from('templateGroups')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('tg.*')
            ->from('templateGroups', 'tg')
            ->orderBy('tg.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}