<?php

namespace App\Model\Manager\Entity\Auth;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ManagerAuth|null find($id, $lockMode = null, $lockVersion = null)
 * @method ManagerAuth|null findOneBy(array $criteria, array $orderBy = null)
 * @method ManagerAuth[]    findAll()
 * @method ManagerAuth[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ManagerAuthRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ManagerAuth::class);
        $this->em = $em;
    }

    public function add(ManagerAuth $managerAuth): void
    {
        $this->em->persist($managerAuth);
    }
}
