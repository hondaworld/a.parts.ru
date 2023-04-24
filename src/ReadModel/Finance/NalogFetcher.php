<?php


namespace App\ReadModel\Finance;


use App\Model\Finance\Entity\Nalog\Nalog;
use Doctrine\ORM\EntityManagerInterface;

class NalogFetcher
{
    private $connection;
    private $nalogs;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->nalogs = $em->getRepository(Nalog::class);
    }

    public function get(int $id): Nalog
    {
        return $this->nalogs->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('nalogID, name')
            ->from('nalogs')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('n.*')
            ->addSelect('(SELECT nds FROM nalogNds WHERE nalogID = n.nalogID ORDER BY dateofadded DESC LIMIT 1) AS nds')
            ->from('nalogs', 'n')
            ->orderBy('n.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}