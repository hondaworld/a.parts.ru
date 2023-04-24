<?php


namespace App\ReadModel\Finance;


use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Finance\Entity\NalogNds\NalogNds;
use Doctrine\ORM\EntityManagerInterface;

class NalogNdsFetcher
{
    private $connection;
    private $nalogsNds;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->nalogsNds = $em->getRepository(NalogNds::class);
    }

    public function get(int $id): NalogNds
    {
        return $this->nalogsNds->get($id);
    }

    public function all(Nalog $nalog): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('n.*')
            ->from('nalogNds', 'n')
            ->where('n.nalogID = :nalogID')
            ->setParameter('nalogID', $nalog->getId())
            ->orderBy('n.dateofadded')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}