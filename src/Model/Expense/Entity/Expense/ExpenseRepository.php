<?php

namespace App\Model\Expense\Entity\Expense;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Expense|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expense|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expense[]    findAll()
 * @method Expense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Expense::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Expense
     */
    public function get(int $id): Expense
    {
        if (!$expense = $this->find($id)) {
            throw new EntityNotFoundException('Расход не найден');
        }

        return $expense;
    }

    public function add(Expense $expense): void
    {
        $this->em->persist($expense);
    }

    public function remove(Expense $expense): void
    {
        $this->em->remove($expense);
    }
}
