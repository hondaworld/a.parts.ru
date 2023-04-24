<?php

namespace App\Model\Work\Entity\Category;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WorkCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkCategory[]    findAll()
 * @method WorkCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkCategoryRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, WorkCategory::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return WorkCategory
     * @throws EntityNotFoundException
     */
    public function get(int $id): WorkCategory
    {
        if (!$workCategory = $this->find($id)) {
            throw new EntityNotFoundException('Категория не найдена');
        }

        return $workCategory;
    }

    public function add(WorkCategory $workCategory): void
    {
        $this->em->persist($workCategory);
    }

    public function getMaxSort(): int
    {
        $query = $this->createQueryBuilder('m')
            ->select('MAX(m.number)');

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
            ->set('g.number', 'g.number + :additional');

        $qb->andWhere($qb->expr()->gt('g.number', ':number'));
        $qb->setParameter('number', $sort);
        $qb->setParameter('additional', $additional);
        $qb->getQuery()->execute();
    }
}
