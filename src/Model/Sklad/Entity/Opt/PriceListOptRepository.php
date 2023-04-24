<?php

namespace App\Model\Sklad\Entity\Opt;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PriceListOpt|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceListOpt|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceListOpt[]    findAll()
 * @method PriceListOpt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceListOptRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, PriceListOpt::class);
        $this->em = $em;
    }

    public function add(PriceListOpt $priceListOpt): void
    {
        $this->em->persist($priceListOpt);
    }
}
