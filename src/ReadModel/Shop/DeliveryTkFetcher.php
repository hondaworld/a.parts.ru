<?php


namespace App\ReadModel\Shop;


use App\Model\Shop\Entity\Delivery\Delivery;
use Doctrine\ORM\EntityManagerInterface;

class DeliveryTkFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Delivery::class);
    }

    public function get(int $id): Delivery
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'delivery_tkID',
                'name'
            )
            ->from('delivery_tk')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'd.delivery_tkID',
                'd.name',
                'd.http',
                'd.isHide'
            )
            ->from('delivery_tk', 'd')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}