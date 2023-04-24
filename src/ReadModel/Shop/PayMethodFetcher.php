<?php


namespace App\ReadModel\Shop;


use App\Model\Shop\Entity\PayMethod\PayMethod;
use Doctrine\ORM\EntityManagerInterface;

class PayMethodFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(PayMethod::class);
    }

    public function get(int $id): PayMethod
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'payMethodID',
                'name'
            )
            ->from('payMethods')
            ->orderBy('number')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'p.payMethodID',
                'p.val',
                'p.isHide',
                'p.isMain',
                'p.number'
            )
            ->from('payMethods', 'p')
            ->orderBy('number')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}