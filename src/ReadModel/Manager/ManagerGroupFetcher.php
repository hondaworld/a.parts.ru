<?php


namespace App\ReadModel\Manager;


use App\Model\EntityNotFoundException;
use App\Model\Manager\Entity\Group\ManagerGroup;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class ManagerGroupFetcher
{
    private $connection;
    private $groups;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->groups = $em->getRepository(ManagerGroup::class);
    }

    public function get(int $id): ManagerGroup
    {
        return $this->groups->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'managerGroupID',
                'name'
            )
            ->from('managerGroups')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'g.managerGroupID',
                'g.name'
            )
            ->from('managerGroups', 'g')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }
}