<?php


namespace App\ReadModel\User;


use App\Model\Shop\Entity\Discount\Discount;
use Doctrine\ORM\EntityManagerInterface;

class DiscountFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Discount::class);
    }

    public function get(int $id): Discount
    {
        return $this->repository->get($id);
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('d.*')
            ->from('discounts', 'd')
            ->orderBy('d.summ')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}