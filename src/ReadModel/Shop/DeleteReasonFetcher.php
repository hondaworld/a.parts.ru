<?php


namespace App\ReadModel\Shop;


use App\Model\Shop\Entity\DeleteReason\DeleteReason;
use Doctrine\ORM\EntityManagerInterface;

class DeleteReasonFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(DeleteReason::class);
    }

    public function get(int $id): DeleteReason
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'deleteReasonID',
                'name'
            )
            ->from('deleteReasons')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'd.deleteReasonID',
                'd.name',
                'd.isHide',
                'd.isMain',
                'd.noneDelete'
            )
            ->from('deleteReasons', 'd')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}