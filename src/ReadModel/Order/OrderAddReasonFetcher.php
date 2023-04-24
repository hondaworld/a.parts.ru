<?php


namespace App\ReadModel\Order;


use App\Model\Order\Entity\AddReason\OrderAddReason;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class OrderAddReasonFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(OrderAddReason ::class);
    }

    public function get(int $id): OrderAddReason
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        return $this->connection->createQueryBuilder()
            ->select('a.order_add_reasonID', 'a.name')
            ->from('order_add_reasons', 'a')
            ->executeQuery()
            ->fetchAllKeyValue();
    }
}