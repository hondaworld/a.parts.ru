<?php


namespace App\ReadModel\Shop;


use App\Model\Shop\Entity\ShopType\ShopType;
use Doctrine\ORM\EntityManagerInterface;

class ShopTypeFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ShopType::class);
    }

    public function get(int $id): ShopType
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'shop_typeID',
                'name'
            )
            ->from('shop_types')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'st.shop_typeID',
                'st.name',
                'st.isHide',
                'st.noneDelete'
            )
            ->from('shop_types', 'st')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}