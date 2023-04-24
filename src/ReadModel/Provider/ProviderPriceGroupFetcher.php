<?php


namespace App\ReadModel\Provider;


use App\Model\Provider\Entity\Group\ProviderPriceGroup;
use App\Model\Provider\Entity\Price\ProviderPrice;
use Doctrine\ORM\EntityManagerInterface;

class ProviderPriceGroupFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ProviderPriceGroup::class);
    }

    public function get(int $id): ProviderPrice
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('providerPriceGroupID, name')
            ->from('providerPriceGroups')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }
}