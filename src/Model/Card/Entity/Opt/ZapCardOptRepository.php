<?php

namespace App\Model\Card\Entity\Opt;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCardOpt|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCardOpt|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCardOpt[]    findAll()
 * @method ZapCardOpt[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardOptRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCardOpt::class);
        $this->em = $em;
    }

    public function add(ZapCardOpt $zapCardOpt): void
    {
        $this->em->persist($zapCardOpt);
    }
}
