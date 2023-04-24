<?php

namespace App\Model\Menu\Entity\Section;

use App\Model\Menu\Entity\Group\MenuGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MenuSection|null find($id, $lockMode = null, $lockVersion = null)
 * @method MenuSection|null findOneBy(array $criteria, array $orderBy = null)
 * @method MenuSection[]    findAll()
 * @method MenuSection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MenuSectionRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, MenuSection::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return MenuSection
     */
    public function get(int $id): MenuSection
    {
        if (!$section = $this->find($id)) {
            throw new EntityNotFoundException('Секция меню не найдена');
        }

        return $section;
    }

    public function add(MenuSection $section): void
    {
        $this->em->persist($section);
    }

    public function getMaxSort(MenuGroup $group, int $parentID): int
    {
        $query = $this->createQueryBuilder('m')
            ->select('MAX(m.sort)')
            ->where('m.group = :groupID')
            ->setParameter('groupID', $group->getId())
            ->andWhere('m.parent_id = :parentID')
            ->setParameter('parentID', $parentID);

        return $query->getQuery()->getOneOrNullResult()[1] ?: 0;
    }

    public function getNextSort(MenuGroup $group, int $parentID): int
    {
        return $this->getMaxSort($group, $parentID) + 1;
    }

    public function addSort(MenuGroup $group, int $parentID, int $sort): void
    {
        $this->changeSort($group, $parentID, $sort - 1, 1);
    }

    public function removeSort(MenuGroup $group, int $parentID, int $sort): void
    {
        $this->changeSort($group, $parentID, $sort, -1);
    }

    public function changeSort(MenuGroup $group, int $parentID, int $sort, int $additional): void
    {
        $qb = $this->createQueryBuilder('s')
            ->update()
            ->set('s.sort', 's.sort + :additional');

        $qb->andWhere($qb->expr()->gt('s.sort', ':sort'));
        $qb->setParameter('sort', $sort);
        $qb->setParameter('additional', $additional);
        $qb->andWhere('s.group = :groupID');
        $qb->setParameter('groupID', $group->getId());
        $qb->andWhere('s.parent_id = :parentID');
        $qb->setParameter('parentID', $parentID);
        $qb->getQuery()->execute();
    }

    public function findByParentId(int $parent_id): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.parent_id = :parent_id')
            ->setParameter('parent_id', $parent_id)
            ->orderBy('s.sort', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return MenuSection[] Returns an array of MenuSection objects
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
    public function findOneBySomeField($value): ?MenuSection
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
