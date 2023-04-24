<?php


namespace App\ReadModel\User;


use App\Model\User\Entity\ShopPayType\ShopPayType;
use Doctrine\ORM\EntityManagerInterface;

class ShopPayTypeFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ShopPayType::class);
    }

    public function get(int $id): ShopPayType
    {
        return $this->repository->get($id);
    }

    public function assoc(int $shop_pay_typeID = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('shop_pay_typeID, name')
            ->from('shop_pay_types')
            ->orderBy('name')
            ->where('isHide = 0');

        if ($shop_pay_typeID) {
            $qb->orWhere('shop_pay_typeID = :shop_pay_typeID')
                ->setParameter('shop_pay_typeID', $shop_pay_typeID);
        }

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('s.*')
            ->addSelect('(SELECT Count(userID) FROM users WHERE shop_pay_typeID = s.shop_pay_typeID) AS users')
            ->from('shop_pay_types', 's')
            ->orderBy('s.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}