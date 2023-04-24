<?php


namespace App\ReadModel\Contact;

use App\Model\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class CountryFetcher
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
                'countryID',
                'name'
            )
            ->from('countries')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('countries')
            ->orderBy("name")
            ->executeQuery();

//        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $result = $stmt->fetchAllAssociative();

        return $result ?: null;
    }
}