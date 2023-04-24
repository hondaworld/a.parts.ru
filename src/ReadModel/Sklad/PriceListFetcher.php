<?php


namespace App\ReadModel\Sklad;


use App\Model\Sklad\Entity\PriceList\PriceList;
use Doctrine\ORM\EntityManagerInterface;

class PriceListFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(PriceList::class);
    }

    public function get(int $id): PriceList
    {
        return $this->repository->get($id);
    }

    public function assoc(int $id = null): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'price_listID',
                'name'
            )
            ->from('price_lists')
            ->where('isHide = 0')
            ->orderBy('name_short');

        if ($id) {
            $stmt->orWhere('price_listID = :id')
                ->setParameter('id', $id);
        }

        return $stmt->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('p.*')
            ->from('price_lists', 'p')
            ->orderBy('p.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}