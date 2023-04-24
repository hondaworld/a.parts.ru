<?php


namespace App\ReadModel\User;


use App\Model\User\Entity\Opt\Opt;
use Doctrine\ORM\EntityManagerInterface;

class OptFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Opt::class);
    }

    public function get(int $id): Opt
    {
        return $this->repository->get($id);
    }

    public function assocAll(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('optID, name')
            ->from('opt')
            ->orderBy('number');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assoc(int $optID = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('optID, name')
            ->from('opt')
            ->orderBy('number')
            ->where('isHide = 0');

        if ($optID) {
            $qb->orWhere('optID = :optID')
                ->setParameter('optID', $optID);
        }

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('o.*')
            ->addSelect('(SELECT Count(userID) FROM users WHERE optID = o.optID) AS users')
            ->from('opt', 'o')
            ->orderBy('o.number')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    public function allNotHide(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('o.*')
            ->from('opt', 'o')
            ->where('o.isHide = 0')
            ->orderBy('o.number')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}