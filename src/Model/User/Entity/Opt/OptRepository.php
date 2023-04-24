<?php

namespace App\Model\User\Entity\Opt;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Opt|null find($id, $lockMode = null, $lockVersion = null)
 * @method Opt|null findOneBy(array $criteria, array $orderBy = null)
 * @method Opt[]    findAll()
 * @method Opt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OptRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Opt::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Opt
     * @throws EntityNotFoundException
     */
    public function get(int $id): Opt
    {
        if (!$opt = $this->find($id)) {
            throw new EntityNotFoundException('Колонка прайса не найдена');
        }

        return $opt;
    }

    public function add(Opt $opt): void
    {
        $this->em->persist($opt);
    }

    public function getMaxSort(): int
    {
        $query = $this->createQueryBuilder('o')
            ->select('MAX(o.number)');

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
        $qb = $this->createQueryBuilder('o')
            ->update()
            ->set('o.number', 'o.number + :additional');

        $qb->andWhere($qb->expr()->gt('o.number', ':number'));
        $qb->setParameter('number', $sort);
        $qb->setParameter('additional', $additional);
        $qb->getQuery()->execute();
    }

    public function findAllOrdered(): array
    {
        return $this->findBy(['isHide' => false], ['number' => 'asc']);
    }
}
