<?php


namespace App\ReadModel\User;


use App\Model\User\Entity\EmailStatus\UserEmailStatus;
use Doctrine\ORM\EntityManagerInterface;

class UserEmailStatusFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(UserEmailStatus::class);
    }

    public function get(int $id): UserEmailStatus
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('userEmailStatusID, name')
            ->from('userEmailStatuses')
            ->orderBy('userEmailStatusID');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

}