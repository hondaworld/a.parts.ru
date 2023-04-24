<?php

namespace App\Model\Card\Entity\Category;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCategory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCategory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCategory[]    findAll()
 * @method ZapCategory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCategoryRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCategory::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapCategory
     * @throws EntityNotFoundException
     */
    public function get(int $id): ZapCategory
    {
        if (!$zapCategory = $this->find($id)) {
            throw new EntityNotFoundException('Категория товаров не найдена');
        }

        return $zapCategory;
    }

    public function add(ZapCategory $zapCategory): void
    {
        $this->em->persist($zapCategory);
    }

    public function getMaxSort(): int
    {
        $query = $this->createQueryBuilder('c')
            ->select('MAX(c.number)');

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
        $qb = $this->createQueryBuilder('c')
            ->update()
            ->set('c.number', 'c.number + :additional');

        $qb->andWhere($qb->expr()->gt('c.number', ':number'));
        $qb->setParameter('number', $sort);
        $qb->setParameter('additional', $additional);
        $qb->getQuery()->execute();
    }
}
