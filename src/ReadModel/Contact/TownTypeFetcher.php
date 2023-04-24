<?php


namespace App\ReadModel\Contact;

use App\Model\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;

class TownTypeFetcher
{
    private $connection;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'name'
            )
            ->from('townTypes')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('townTypes')
            ->orderBy("name")
            ->executeQuery();

        $result = $stmt->fetchAllAssociative();

        return $result ?: null;
    }
}