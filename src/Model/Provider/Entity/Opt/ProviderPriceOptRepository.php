<?php

namespace App\Model\Provider\Entity\Opt;

use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\Entity\Provider\Provider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProviderPriceOpt|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProviderPriceOpt|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProviderPriceOpt[]    findAll()
 * @method ProviderPriceOpt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProviderPriceOptRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ProviderPriceOpt::class);
        $this->em = $em;
    }

    public function findByProviderPrice(ProviderPrice $providerPrice): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.providerPrice = :providerPrice')
            ->setParameter('providerPrice', $providerPrice->getId())
            ->innerJoin('l.opt', 'o')
            ->orderBy('o.number', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByProvider(Provider $provider): array
    {
        return $this->createQueryBuilder('l')
            ->innerJoin('l.opt', 'o')
            ->innerJoin('l.providerPrice', 'p')
            ->andWhere('p.provider = :provider')
            ->setParameter('provider', $provider)
            ->getQuery()
            ->getResult()
            ;
    }

    public function add(ProviderPriceOpt $providerPriceOpt): void
    {
        $this->em->persist($providerPriceOpt);
    }
}
