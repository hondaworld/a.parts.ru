<?php

namespace App\Model\Order\Entity\ManagerOperation;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ManagerOrderOperation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ManagerOrderOperation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ManagerOrderOperation[]    findAll()
 * @method ManagerOrderOperation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ManagerOrderOperationRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ManagerOrderOperation::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ManagerOrderOperation
     */
    public function get(int $id): ManagerOrderOperation
    {
        if (!$managerOrderOperation = $this->find($id)) {
            throw new EntityNotFoundException('Операция не найдена');
        }

        return $managerOrderOperation;
    }

    public function add(ManagerOrderOperation $managerOrderOperation): void
    {
        $this->em->persist($managerOrderOperation);
    }
}
