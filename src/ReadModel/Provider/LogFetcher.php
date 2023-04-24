<?php


namespace App\ReadModel\Provider;


use Doctrine\ORM\EntityManagerInterface;

class LogFetcher
{
    private $connection;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('l.*')
            ->from('logPrice', 'l')
            ->orderBy('l.dateofadded', 'DESC')
            ->addOrderBy('l.logPriceID', 'DESC')
            ->setMaxResults(150)
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    public function full(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('l.*')
            ->from('logPrice_all', 'l')
            ->orderBy('l.dateofadded', 'DESC')
            ->addOrderBy('l.logPriceID', 'DESC')
            ->setMaxResults(50)
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    public function yesterdayUploadedPrices(): array
    {
        $yesterday = (new \DateTime())->modify('-1 day');
        $stmt = $this->connection->createQueryBuilder()
            ->select('l.*')
            ->from('logPrice_all', 'l')
            ->andWhere('l.dateofadded >= :date_from')
            ->andWhere('l.dateofadded <= :date_till')
            ->setParameter('date_from', $yesterday->format('Y-m-d') . ' 00:00:00')
            ->setParameter('date_till', $yesterday->format('Y-m-d') . ' 23:59:59')
            ->orderBy('l.dateofadded', 'DESC')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}