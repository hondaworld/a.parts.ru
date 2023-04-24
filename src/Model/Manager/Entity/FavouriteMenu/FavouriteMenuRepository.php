<?php

namespace App\Model\Manager\Entity\FavouriteMenu;

use App\Model\EntityNotFoundException;
use App\Model\Manager\Entity\Manager\Manager;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FavouriteMenu|null find($id, $lockMode = null, $lockVersion = null)
 * @method FavouriteMenu|null findOneBy(array $criteria, array $orderBy = null)
 * @method FavouriteMenu[]    findAll()
 * @method FavouriteMenu[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FavouriteMenuRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, FavouriteMenu::class);
        $this->em = $em;
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param int $id
     * @return FavouriteMenu
     */
    public function get(int $id): FavouriteMenu
    {
        if (!$favouriteMenu = $this->find($id)) {
            throw new EntityNotFoundException('Пункт меню не найден');
        }

        return $favouriteMenu;
    }

    public function add(FavouriteMenu $favouriteMenu): void
    {
        $this->em->persist($favouriteMenu);
    }

    public function getMaxSort(Manager $manager): int
    {
        $query = $this->createQueryBuilder('m')
            ->select('MAX(m.sort)')
            ->where('m.manager = :manager')
            ->setParameter('manager', $manager);

        return $query->getQuery()->getOneOrNullResult()[1] ?: 0;
    }

    public function getNextSort(Manager $manager): int
    {
        return $this->getMaxSort($manager) + 1;
    }

    public function addSort(Manager $manager, int $sort): void
    {
        $this->changeSort($manager, $sort - 1, 1);
    }

    public function removeSort(Manager $manager, int $sort): void
    {
        $this->changeSort($manager, $sort, -1);
    }

    public function changeSort(Manager $manager, int $sort, int $additional): void
    {
        $qb = $this->createQueryBuilder('s')
            ->update()
            ->set('s.sort', 's.sort + :additional');

        $qb->andWhere($qb->expr()->gt('s.sort', ':sort'));
        $qb->setParameter('sort', $sort);
        $qb->setParameter('additional', $additional);
        $qb->andWhere('s.manager = :manager');
        $qb->setParameter('manager', $manager);
        $qb->getQuery()->execute();
    }
}
