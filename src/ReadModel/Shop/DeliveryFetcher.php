<?php


namespace App\ReadModel\Shop;


use App\Model\Shop\Entity\Delivery\Delivery;
use Doctrine\ORM\EntityManagerInterface;

class DeliveryFetcher
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
                'deliveryID',
                'name'
            )
            ->from('delivery')
            ->orderBy('number')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'd.deliveryID',
                "CONCAT(d.x1, if (d.isPercent1 = 1, '%', 'руб.'), '<', d.porog, 'руб.', '<=', d.x2, if (d.isPercent2 = 1, '%', 'руб.')) AS val",
                'd.name',
                'd.isTK',
                'd.isHide',
                'd.isMain',
                'd.number'
            )
            ->from('delivery', 'd')
            ->orderBy('number')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}