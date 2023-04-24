<?php


namespace App\ReadModel\Provider;

use Doctrine\ORM\EntityManagerInterface;

class PriceUploaderFetcher
{
    private $connection;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    public function getCountPrices(string $tableName, int $providerPriceID, int $createrID = 0): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('createrID, Count(*) AS c')
            ->from($tableName == '' ? 'shopPriceN' : $tableName)
            ->where('providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPriceID)
            ->groupBy('createrID');

        if ($createrID != 0) {
            $qb
                ->andWhere('createrID = :createrID')
                ->setParameter('createrID', $createrID);
        }
        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function deletePrices(string $tableName, int $providerPriceID, int $createrID = 0): int
    {
        $qb = $this->connection->createQueryBuilder()
            ->delete($tableName == '' ? 'shopPriceN' : $tableName)
            ->where('providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPriceID);

        if ($createrID != 0) {
            $qb
                ->andWhere('createrID = :createrID')
                ->setParameter('createrID', $createrID);
        }
        return $qb->executeStatement();
    }

    public function deletePrice(string $tableName, int $providerPriceID, int $createrID, string $number): int
    {
        $qb = $this->connection->createQueryBuilder()
            ->delete($tableName == '' ? 'shopPriceN' : $tableName)
            ->where('providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPriceID)
            ->andWhere('createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->andWhere('number = :number')
            ->setParameter('number', $number);
        return $qb->executeStatement();
    }

    public function insertPrice(string $tableName, array $arr): int
    {
        $values = [];
        foreach (array_keys($arr) as $key) {
            $values[$key] = '?';
        }
        $qb = $this->connection->createQueryBuilder()
            ->insert($tableName == '' ? 'shopPriceN' : $tableName)
            ->values($values)
            ->setParameters(array_values($arr));

        return $qb->executeStatement();
    }

    public function copyPrice(string $tableName, int $providerPriceID, int $childrenProviderPriceID): void
    {
        $query = "
            INSERT INTO " . ($tableName == '' ? 'shopPriceN' : $tableName) . "
            (number, price, providerPriceID, createrID, quantity, name) 
            SELECT number, price, ?, createrID, quantity, name
            FROM " . ($tableName == '' ? 'shopPriceN' : $tableName) . " 
            WHERE providerPriceID = ?
            ";
        $stmt = $this->connection->prepare($query);
        $params = [$childrenProviderPriceID, $providerPriceID];
        $stmt->executeStatement($params);
    }

    public function findPriceNumber(string $tableName, string $number, int $providerPriceID, int $createrID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from($tableName == '' ? 'shopPriceN' : $tableName)
            ->where('providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPriceID)
            ->andWhere('createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->andWhere('number = :number')
            ->setParameter('number', $number);
        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function getCountPriceNumber(string $tableName, string $number, int $providerPriceID, int $createrID): int
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('Count(*) AS c')
            ->from($tableName == '' ? 'shopPriceN' : $tableName)
            ->where('providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPriceID)
            ->andWhere('createrID = :createrID')
            ->setParameter('createrID', $createrID)
            ->andWhere('number = :number')
            ->setParameter('number', $number);
        return $qb->executeQuery()->fetchOne();
    }

    public function updatePrice(string $tableName, string $number, int $createrID, int $providerPriceID, float $price, int $quantity = null): int
    {
        if ($this->getCountPriceNumber($tableName, $number, $providerPriceID, $createrID) > 0) {
            $qb = $this->connection->createQueryBuilder()
                ->update($tableName == '' ? 'shopPriceN' : $tableName)
                ->set('price', ':price')
                ->setParameter('price', $price)
                ->where('providerPriceID = :providerPriceID')
                ->setParameter('providerPriceID', $providerPriceID)
                ->andWhere('createrID = :createrID')
                ->setParameter('createrID', $createrID)
                ->andWhere('number = :number')
                ->setParameter('number', $number);
            if ($quantity != null) {
                $qb ->set('quantity', ':quantity')
                    ->setParameter('quantity', $quantity);
            }
            return $qb->executeStatement();
        } else {
            return $this->insertPrice($tableName, [
                'number' => $number,
                'createrID' => $createrID,
                'providerPriceID' => $providerPriceID,
                'price' => $price,
                'quantity' => $quantity ?: 0,
            ]);
        }
    }

    public function uploadingPriceBegin(string $price)
    {
        $qb = $this->connection->createQueryBuilder()
            ->insert('shopPriceWorking')
            ->values(['price' => ':price', 'dt_create' => 'Now()'])
            ->setParameter('price', $price);
        return $qb->executeStatement();
    }

    public function uploadingPriceEnd(string $price)
    {
        $qb = $this->connection->createQueryBuilder()
            ->delete('shopPriceWorking')
            ->where('price = :price')
            ->setParameter('price', $price);
        return $qb->executeStatement();
    }

    public function uploadingPriceDelete()
    {
//        DELETE FROM shopPriceWorking WHERE TIME_TO_SEC(timediff(Now(), dt_create)) > 3600

        $qb = $this->connection->createQueryBuilder()
            ->delete('shopPriceWorking')
            ->where('TIME_TO_SEC(timediff(Now(), dt_create)) > 3600');
        return $qb->executeStatement();
    }
}