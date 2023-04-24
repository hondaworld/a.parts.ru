<?php

namespace App\Model\Finance\Entity\CurrencyRate;

use App\Model\EntityNotFoundException;
use App\Model\Finance\Entity\Currency\Currency;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CurrencyRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method CurrencyRate|null findOneBy(array $criteria, array $orderBy = null)
 * @method CurrencyRate[]    findAll()
 * @method CurrencyRate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrencyRateRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, CurrencyRate::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return CurrencyRate
     */
    public function get(int $id): CurrencyRate
    {
        if (!$currencyRate = $this->find($id)) {
            throw new EntityNotFoundException('Запись не найдена');
        }

        return $currencyRate;
    }

    public function add(CurrencyRate $currencyRate): void
    {
        $this->em->persist($currencyRate);
    }

    public function hasByDate(Currency $currency_to, Currency $currency_from, \DateTime $dateofadded, int $id = 0): bool
    {
        $query = $this->createQueryBuilder('cr')
            ->select('COUNT(cr.currencyRateID)')
            ->andWhere('cr.currencyID_to = :currencyID_to')
            ->setParameter('currencyID_to', $currency_to->getId())
            ->andWhere('cr.dateofadded = :dateofadded')
            ->setParameter('dateofadded', $dateofadded)
            ->andWhere('cr.currencyID = :currencyID')
            ->setParameter('currencyID', $currency_from->getId())
        ;

        if ($id) {
            $query->andWhere('cr.currencyRateID <> :id')->setParameter('id', $id);
        }

        return $query->getQuery()->getSingleScalarResult() > 0;
    }
}
