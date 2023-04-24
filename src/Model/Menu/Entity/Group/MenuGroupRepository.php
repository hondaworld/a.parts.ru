<?php

namespace App\Model\Menu\Entity\Group;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MenuGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuGroup[]    findAll()
 * @method MenuGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuGroupRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, MenuGroup::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return MenuGroup
     * @throws EntityNotFoundException
     */
    public function get(int $id): MenuGroup
    {
        if (!$group = $this->find($id)) {
            throw new EntityNotFoundException('Группа меню не найдена');
        }

        return $group;
    }

    public function add(MenuGroup $group): void
    {
        $this->em->persist($group);
    }

    public function getMaxSort(): int
    {
        $query = $this->createQueryBuilder('m')
            ->select('MAX(m.sort)');

        return $query->getQuery()->getSingleScalarResult() ?: 0;
    }

    public function getNextSort(): int
    {
        return $this->getMaxSort() + 1;
    }

    public function addSort(int $sort): void
    {
        $this->changeSort($sort - 1, 1);
    }

    public function removeSort(int $sort): void
    {
        $this->changeSort($sort, -1);
    }

    public function changeSort(int $sort, int $additional): void
    {
        $qb = $this->createQueryBuilder('g')
            ->update()
            ->set('g.sort', 'g.sort + :additional');

        $qb->andWhere($qb->expr()->gt('g.sort', ':sort'));
        $qb->setParameter('sort', $sort);
        $qb->setParameter('additional', $additional);
        $qb->getQuery()->execute();
    }

    // /**
    //  * @return MenuGroup[] Returns an array of MenuGroup objects
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
    public function findOneBySomeField($value): ?MenuGroup
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
