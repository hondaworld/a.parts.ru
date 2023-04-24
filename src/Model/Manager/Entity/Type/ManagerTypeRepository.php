<?php

namespace App\Model\Manager\Entity\Type;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ManagerType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ManagerType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ManagerType[]    findAll()
 * @method ManagerType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ManagerTypeRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ManagerType::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ManagerType
     */
    public function get(int $id): ManagerType
    {
        if (!$group = $this->find($id)) {
            throw new EntityNotFoundException('Тип менеджеров не найдена');
        }

        return $group;
    }

    public function add(ManagerType $type): void
    {
        $this->em->persist($type);
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
