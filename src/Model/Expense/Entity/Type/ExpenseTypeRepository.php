<?php

namespace App\Model\Expense\Entity\Type;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExpenseType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpenseType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpenseType[]    findAll()
 * @method ExpenseType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseTypeRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ExpenseType::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ExpenseType
     */
    public function get(int $id): ExpenseType
    {
        if (!$expenseType = $this->find($id)) {
            throw new EntityNotFoundException('Тип не найден');
        }

        return $expenseType;
    }

    public function add(ExpenseType $expenseType): void
    {
        $this->em->persist($expenseType);
    }
}
