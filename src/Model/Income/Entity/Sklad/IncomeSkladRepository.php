<?php

namespace App\Model\Income\Entity\Sklad;

use App\Model\EntityNotFoundException;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method IncomeSklad|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncomeSklad|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncomeSklad[]    findAll()
 * @method IncomeSklad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncomeSkladRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, IncomeSklad::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return IncomeSklad
     */
    public function get(int $id): IncomeSklad
    {
        if (!$incomeSklad = $this->find($id)) {
            throw new EntityNotFoundException('Склад прихода не найден');
        }

        return $incomeSklad;
    }

    /**
     * @param Income $income
     * @param ZapSklad|null $zapSklad
     * @param int|null $quantity
     * @return IncomeSklad
     */
    public function getOneByIncomeOrCreate(Income $income, ?ZapSklad $zapSklad = null, ?int $quantity = null): IncomeSklad
    {
        if ($quantity === null) {
            $quantity = $income->getQuantity();
        }

        if (!$zapSklad) {
            $incomeSklad = $this->findOneBy(['income' => $income]);
        } else {
            $incomeSklad = $this->findOneBy(['income' => $income, 'zapSklad' => $zapSklad]);
        }

        if (!$incomeSklad) {
            if (!$zapSklad) {
                $zapSklad = $income->getProviderPrice()->getProvider()->getZapSklad();
            }
            $incomeSklad = new IncomeSklad($income, $zapSklad, $quantity);
            $this->add($incomeSklad);
        }

        return $incomeSklad;
    }

    /**
     * @param Income $income
     * @param ZapSklad|null $zapSklad
     * @return IncomeSklad
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getOneByIncomeForOrderGood(Income $income, ?ZapSklad $zapSklad = null): IncomeSklad
    {
        if (!$zapSklad) {
            $query = $this->createQueryBuilder('isk')
                ->select('isk')
                ->andWhere('isk.income = :income')
                ->setParameter('income', $income);
            $query->andWhere($query->expr()->gt('isk.quantityIn', 0));
            $incomeSklad = $query->getQuery()->getOneOrNullResult();
        } else {
            $incomeSklad = $this->findOneBy(['income' => $income, 'zapSklad' => $zapSklad]);
        }

        return $incomeSklad;
    }

    /**
     * @param Income $income
     * @param ZapSklad $zapSklad
     * @return IncomeSklad
     */
    public function getBySklad(Income $income, ZapSklad $zapSklad): IncomeSklad
    {
        if (!$incomeSklad = $this->findOneBy(['income' => $income, 'zapSklad' => $zapSklad])) {
            throw new EntityNotFoundException('Склад прихода не найден');
        }

        return $incomeSklad;
    }

    public function add(IncomeSklad $incomeSklad): void
    {
        $this->em->persist($incomeSklad);
    }
}
