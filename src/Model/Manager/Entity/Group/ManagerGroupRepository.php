<?php

namespace App\Model\Manager\Entity\Group;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ManagerGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ManagerGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ManagerGroup[]    findAll()
 * @method ManagerGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ManagerGroupRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ManagerGroup::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ManagerGroup
     */
    public function get(int $id): ManagerGroup
    {
        if (!$group = $this->find($id)) {
            throw new EntityNotFoundException('Группа менеджеров не найдена');
        }

        return $group;
    }

    public function add(ManagerGroup $group): void
    {
        $this->em->persist($group);
    }

    // /**
    //  * @return ManagerGroup[] Returns an array of ManagerGroup objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ManagerGroup
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
