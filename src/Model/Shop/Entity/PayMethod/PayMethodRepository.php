<?php

namespace App\Model\Shop\Entity\PayMethod;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PayMethod|null find($id, $lockMode = null, $lockVersion = null)
 * @method PayMethod|null findOneBy(array $criteria, array $orderBy = null)
 * @method PayMethod[]    findAll()
 * @method PayMethod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PayMethodRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, PayMethod::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return PayMethod
     */
    public function get(int $id): PayMethod
    {
        if (!$payMethod = $this->find($id)) {
            throw new EntityNotFoundException('Способ оплаты не найден');
        }

        return $payMethod;
    }

    public function add(PayMethod $payMethod): void
    {
        $this->em->persist($payMethod);
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

    public function updateMain(): void
    {
        $qb = $this->createQueryBuilder('c')
            ->update()
            ->set('c.isMain', 'false');
        $qb->getQuery()->execute();
    }
}
