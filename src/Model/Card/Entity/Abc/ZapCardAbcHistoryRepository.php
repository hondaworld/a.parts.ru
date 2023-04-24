<?php

namespace App\Model\Card\Entity\Abc;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCardAbcHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCardAbcHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCardAbcHistory[]    findAll()
 * @method ZapCardAbcHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardAbcHistoryRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCardAbcHistory::class);
        $this->em = $em;
    }

    public function add(ZapCardAbcHistory $zapCardAbcHistory): void
    {
        $this->em->persist($zapCardAbcHistory);
    }
}
