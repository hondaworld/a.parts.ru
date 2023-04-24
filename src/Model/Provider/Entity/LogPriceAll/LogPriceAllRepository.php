<?php

namespace App\Model\Provider\Entity\LogPriceAll;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LogPriceAll|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogPriceAll|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogPriceAll[]    findAll()
 * @method LogPriceAll[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogPriceAllRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, LogPriceAll::class);
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

    public function add(LogPriceAll $logPriceAll): void
    {
        $this->em->persist($logPriceAll);
    }
}
