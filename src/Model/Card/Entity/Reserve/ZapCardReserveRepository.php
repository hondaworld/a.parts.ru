<?php

namespace App\Model\Card\Entity\Reserve;

use App\Model\EntityNotFoundException;
use App\Model\Income\Entity\Income\Income;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCardReserve|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCardReserve|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCardReserve[]    findAll()
 * @method ZapCardReserve[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardReserveRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCardReserve::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapCardReserve
     */
    public function get(int $id): ZapCardReserve
    {
        if (!$zapCardReserve = $this->find($id)) {
            throw new EntityNotFoundException('Резерв не найден');
        }

        return $zapCardReserve;
    }

    public function add(ZapCardReserve $zapCardReserve): void
    {
        $this->em->persist($zapCardReserve);
    }

    public function remove(ZapCardReserve $zapCardReserve): void
    {
        $this->em->remove($zapCardReserve);
    }

    public function hasByIncome(Income $income): bool
    {
        $query = $this->createQueryBuilder('zcr')
            ->select('COUNT(zcr.reserveID)')
            ->andWhere('zcr.income = :income')
            ->setParameter('income', $income);

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    public function deleteByIncome(Income $income): void
    {
        $zapCardReserves = $this->findBy(['income' => $income]);
        if ($zapCardReserves) {
            foreach ($zapCardReserves as $zapCardReserve) {
                $this->remove($zapCardReserve);
            }
        }
    }

    public function deleteByIncomeAndOrderGood(Income $income, OrderGood $orderGood): void
    {
        $zapCardReserves = $this->findBy(['income' => $income, 'order_good' => $orderGood]);
        if ($zapCardReserves) {
            foreach ($zapCardReserves as $zapCardReserve) {
                $this->remove($zapCardReserve);
            }
        }
    }

    /**
     * @return ZapCardReserve[]
     */
    public function getExpired(): array
    {
        return $this->createQueryBuilder('zcr')
            ->andWhere('zcr.dateofclosed IS NOT NULL')
            ->andWhere('zcr.dateofclosed < CURRENT_TIMESTAMP()')
            ->getQuery()
            ->getResult();
    }

    /**
     * @param OrderGood $orderGood
     * @param ZapSklad $zapSklad
     * @param Income $income
     * @return ZapCardReserve[]
     */
    public function findForShipping(OrderGood $orderGood, ZapSklad $zapSklad, Income $income): array
    {
        return $this->createQueryBuilder('zcr')
            ->andWhere('zcr.order_good = :orderGood')
            ->setParameter('orderGood', $orderGood)
            ->andWhere('zcr.zapSklad = :zapSklad')
            ->setParameter('zapSklad', $zapSklad)
            ->andWhere('zcr.income = :income')
            ->setParameter('income', $income)
            ->getQuery()
            ->getResult();
    }
}
