<?php


namespace App\ReadModel\Manager;


use App\Model\Manager\Entity\Type\ManagerType;
use Doctrine\ORM\EntityManagerInterface;

class ManagerTypeFetcher
{
    private $connection;
    private $types;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->types = $em->getRepository(ManagerType::class);
    }

    public function get(int $id): ManagerType
    {
        return $this->types->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'managerTypeID',
                'name'
            )
            ->from('managerTypes')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                't.managerTypeID',
                't.name'
            )
            ->from('managerTypes', 't')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }
}