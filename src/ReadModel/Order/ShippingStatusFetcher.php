<?php


namespace App\ReadModel\Order;


use App\Model\Expense\Entity\ShippingStatus\ShippingStatus;
use Doctrine\ORM\EntityManagerInterface;

class ShippingStatusFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ShippingStatus ::class);
    }

    public function get(int $id): ShippingStatus
    {
        return $this->repository->get($id);
    }

    public function assocNormal(): array
    {
        return $this->connection->createQueryBuilder()
            ->select('status', 'name')
            ->from('shipping_statuses')
            ->where('status > 2')
            ->orderBy('number')
            ->executeQuery()
            ->fetchAllKeyValue();
    }

    public function assocForChange(): array
    {
        return $this->connection->createQueryBuilder()
            ->select('status', 'name')
            ->from('shipping_statuses')
            ->where('status NOT IN (1,2,3)')
            ->orderBy('number')
            ->executeQuery()
            ->fetchAllKeyValue();
    }
}