<?php


namespace App\ReadModel\Expense;


use App\Model\Expense\Entity\Type\ExpenseType;
use Doctrine\ORM\EntityManagerInterface;

class ExpenseTypeFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ExpenseType ::class);
    }

    public function get(int $id): ExpenseType
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'name',
            )
            ->from('expenseTypes')
            ->where('isHide = 0')
            ->orderBy('name');

        return $stmt->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'et.name',
                'et.isMSms',
                'et.isHide',
            )
            ->from('expenseTypes', 'et')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}