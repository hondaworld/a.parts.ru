<?php

namespace App\Model\Finance\Entity\Currency;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Currency|null find($id, $lockMode = null, $lockVersion = null)
 * @method Currency|null findOneBy(array $criteria, array $orderBy = null)
 * @method Currency[]    findAll()
 * @method Currency[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CurrencyRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Currency::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Currency
     */
    public function get(int $id): Currency
    {
        if (!$currency = $this->find($id)) {
            throw new EntityNotFoundException('Валюта не найдена');
        }

        return $currency;
    }

    public function getCurrencyNational(): Currency
    {
        if (!$currency = $this->findOneBy(['isNational' => 1])) {
            $currency = $this->get(1);
        }
        return $currency;
    }

    public function add(Currency $currency): void
    {
        $this->em->persist($currency);
    }
}
