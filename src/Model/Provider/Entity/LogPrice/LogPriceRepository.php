<?php

namespace App\Model\Provider\Entity\LogPrice;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LogPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogPrice[]    findAll()
 * @method LogPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogPriceRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, LogPrice::class);
        $this->em = $em;
    }

    public function removeOld(): void
    {
        $date = (new \DateTime())->modify('-1 month');
        $this->createQueryBuilder('l')
            ->delete()
            ->andWhere('l.dateofadded < :dateofadded')
            ->setParameter('dateofadded', $date)
            ->getQuery()
            ->execute();
    }

    public function add(LogPrice $logPrice): void
    {
        $this->em->persist($logPrice);
    }
}
