<?php


namespace App\ReadModel\Order;


use App\Model\Order\Entity\AlertType\OrderAlertType;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class OrderAlertTypeFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(OrderAlertType ::class);
    }

    public function get(int $id): OrderAlertType
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        return $this->connection->createQueryBuilder()
            ->select('a.typeID', 'a.name')
            ->from('order_alert_types', 'a')
            ->executeQuery()
            ->fetchAllKeyValue();
    }
}