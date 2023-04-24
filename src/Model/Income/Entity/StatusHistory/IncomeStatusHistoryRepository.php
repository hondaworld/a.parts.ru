<?php

namespace App\Model\Income\Entity\StatusHistory;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IncomeStatusHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncomeStatusHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncomeStatusHistory[]    findAll()
 * @method IncomeStatusHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncomeStatusHistoryRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, IncomeStatusHistory::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return IncomeStatusHistory
     */
    public function get(int $id): IncomeStatusHistory
    {
        if (!$incomeStatusHistory = $this->find($id)) {
            throw new EntityNotFoundException('Запись не найдена');
        }

        return $incomeStatusHistory;
    }

    public function add(IncomeStatusHistory $incomeStatusHistory): void
    {
        $this->em->persist($incomeStatusHistory);
    }
}
