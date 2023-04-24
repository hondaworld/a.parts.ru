<?php


namespace App\ReadModel\Order;


use App\Model\Order\Entity\Site\Site;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class SiteFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Site ::class);
    }

    public function get(int $id): Site
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        return $this->connection->createQueryBuilder()
            ->select('a.siteID', 'a.name_short')
            ->from('sites', 'a')
            ->executeQuery()
            ->fetchAllKeyValue();
    }
}