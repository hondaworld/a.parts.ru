<?php

namespace App\Model\Expense\Entity\Sklad;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\EntityNotFoundException;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExpenseSklad|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpenseSklad|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpenseSklad[]    findAll()
 * @method ExpenseSklad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseSkladRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ExpenseSklad::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ExpenseSklad
     */
    public function get(int $id): ExpenseSklad
    {
        if (!$expenseSklad = $this->find($id)) {
            throw new EntityNotFoundException('Расход не найден');
        }

        return $expenseSklad;
    }

    public function add(ExpenseSklad $expenseSklad): void
    {
        $this->em->persist($expenseSklad);
    }

    public function remove(ExpenseSklad $expenseSklad): void
    {
        $this->em->remove($expenseSklad);
    }

    public function hasAdded(ZapCard $zapCard, ZapSklad $zapSklad, ZapSklad $zapSklad_to): bool
    {
        $query = $this->createQueryBuilder('es')
            ->select('COUNT(es.expenseID)')
            ->andWhere('es.zapCard = :zapCard')
            ->setParameter('zapCard', $zapCard)
            ->andWhere('es.zapSklad = :zapSklad')
            ->setParameter('zapSklad', $zapSklad)
            ->andWhere('es.zapSklad_to = :zapSklad_to')
            ->setParameter('zapSklad_to', $zapSklad_to)
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::ADDED)
            ->andWhere('es.order_good is null')
        ;

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    public function hasAddedByOrderGood(OrderGood $orderGood): bool
    {
        $query = $this->createQueryBuilder('es')
            ->select('COUNT(es.expenseID)')
            ->andWhere('es.order_good = :orderGood')
            ->setParameter('orderGood', $orderGood)
            ->andWhere('es.status <> :status')
            ->setParameter('status', ExpenseSklad::INCOME)
        ;

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @param ZapCard $zapCard
     * @param ZapSklad $zapSklad
     * @param ZapSklad $zapSklad_to
     * @return ExpenseSklad[]
     */
    public function findAdded(ZapCard $zapCard, ZapSklad $zapSklad, ZapSklad $zapSklad_to): array
    {
        return $this->createQueryBuilder('es')
            ->andWhere('es.zapCard = :zapCard')
            ->setParameter('zapCard', $zapCard)
            ->andWhere('es.zapSklad = :zapSklad')
            ->setParameter('zapSklad', $zapSklad)
            ->andWhere('es.zapSklad_to = :zapSklad_to')
            ->setParameter('zapSklad_to', $zapSklad_to)
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::ADDED)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param ZapCard $zapCard
     * @param ZapSklad $zapSklad
     * @param ZapSklad $zapSklad_to
     * @return ExpenseSklad[]
     */
    public function findPacked(ZapCard $zapCard, ZapSklad $zapSklad, ZapSklad $zapSklad_to): array
    {
        return $this->createQueryBuilder('es')
            ->andWhere('es.zapCard = :zapCard')
            ->setParameter('zapCard', $zapCard)
            ->andWhere('es.zapSklad = :zapSklad')
            ->setParameter('zapSklad', $zapSklad)
            ->andWhere('es.zapSklad_to = :zapSklad_to')
            ->setParameter('zapSklad_to', $zapSklad_to)
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::PACKED)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param ZapCard $zapCard
     * @param ZapSklad $zapSklad
     * @param ZapSklad $zapSklad_to
     * @return ExpenseSklad[]
     */
    public function findSent(ZapCard $zapCard, ZapSklad $zapSklad, ZapSklad $zapSklad_to): array
    {
        return $this->createQueryBuilder('es')
            ->andWhere('es.zapCard = :zapCard')
            ->setParameter('zapCard', $zapCard)
            ->andWhere('es.zapSklad = :zapSklad')
            ->setParameter('zapSklad', $zapSklad)
            ->andWhere('es.zapSklad_to = :zapSklad_to')
            ->setParameter('zapSklad_to', $zapSklad_to)
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::SENT)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param ZapSklad $zapSklad
     * @return ExpenseSklad[]
     */
    public function findAllPacked(ZapSklad $zapSklad): array
    {
        return $this->createQueryBuilder('es')
            ->andWhere('es.zapSklad = :zapSklad')
            ->setParameter('zapSklad', $zapSklad)
            ->andWhere('es.status = :status')
            ->setParameter('status', ExpenseSklad::PACKED)
            ->getQuery()
            ->getResult()
        ;
    }
}
