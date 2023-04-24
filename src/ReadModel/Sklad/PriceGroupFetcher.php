<?php


namespace App\ReadModel\Sklad;


use App\Model\Sklad\Entity\PriceGroup\PriceGroup;
use Doctrine\ORM\EntityManagerInterface;

class PriceGroupFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(PriceGroup::class);
    }

    public function get(int $id): PriceGroup
    {
        return $this->repository->get($id);
    }

    public function assoc(int $id = null): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'price_groupID',
                'name'
            )
            ->from('price_groups')
            ->where('isHide = 0')
            ->orderBy('name');

        if ($id) {
            $stmt->orWhere('price_groupID = :id')
                ->setParameter('id', $id);
        }

        return $stmt->executeQuery()->fetchAllKeyValue();
    }

    public function all(int $price_listID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('p.*')
            ->from('price_groups', 'p')
            ->where('price_listID = :price_listID')
            ->setParameter('price_listID', $price_listID)
            ->orderBy('p.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}