<?php

namespace App\Model\Menu\Entity\Action;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use App\Model\EntityNotFoundException;

/**
 * @method MenuAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuAction[]    findAll()
 * @method MenuAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuActionRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, MenuAction::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return MenuAction
     */
    public function get(int $id): MenuAction
    {
        if (!$action = $this->find($id)) {
            throw new EntityNotFoundException('Операция не найдена');
        }

        return $action;
    }

    public function add(MenuAction $action): void
    {
        $this->em->persist($action);
    }

    // /**
    //  * @return MenuAction[] Returns an array of MenuAction objects
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
    public function findOneBySomeField($value): ?MenuAction
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
