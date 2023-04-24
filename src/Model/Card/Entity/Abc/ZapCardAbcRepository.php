<?php

namespace App\Model\Card\Entity\Abc;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCardAbc|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCardAbc|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCardAbc[]    findAll()
 * @method ZapCardAbc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardAbcRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCardAbc::class);
        $this->em = $em;
    }

    public function add(ZapCardAbc $zapCardAbc): void
    {
        $this->em->persist($zapCardAbc);
    }

    public function delete(ZapCard $zapCard, ZapSklad $zapSklad): void
    {
        $this->createQueryBuilder('zca')
            ->delete()
            ->andWhere('zca.zapCard = :zapCard')
            ->setParameter('zapCard', $zapCard)
            ->andWhere('zca.zapSklad = :zapSklad')
            ->setParameter('zapSklad', $zapSklad)
            ->getQuery()
            ->execute();
    }
}
