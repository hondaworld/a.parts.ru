<?php

namespace App\Model\Firm\Entity\SchetGood;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SchetGood|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchetGood|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchetGood[]    findAll()
 * @method SchetGood[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchetGoodRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, SchetGood::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return SchetGood
     */
    public function get(int $id): SchetGood
    {
        if (!$schetGood = $this->find($id)) {
            throw new EntityNotFoundException('Деталь не найдена');
        }

        return $schetGood;
    }

    public function add(SchetGood $schetGood): void
    {
        $this->em->persist($schetGood);
    }

    // /**
    //  * @return SchetGood[] Returns an array of SchetGood objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SchetGood
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
