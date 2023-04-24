<?php

namespace App\Model\Income\Entity\Status;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IncomeStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncomeStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncomeStatus[]    findAll()
 * @method IncomeStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncomeStatusRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, IncomeStatus::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return IncomeStatus
     */
    public function get(int $id): IncomeStatus
    {
        if (!$incomeStatus = $this->find($id)) {
            throw new EntityNotFoundException('Статус не найден');
        }

        return $incomeStatus;
    }

    public function add(IncomeStatus $incomeStatus): void
    {
        $this->em->persist($incomeStatus);
    }
}
