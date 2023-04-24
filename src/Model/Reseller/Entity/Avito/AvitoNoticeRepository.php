<?php

namespace App\Model\Reseller\Entity\Avito;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AvitoNotice|null find($id, $lockMode = null, $lockVersion = null)
 * @method AvitoNotice|null findOneBy(array $criteria, array $orderBy = null)
 * @method AvitoNotice[]    findAll()
 * @method AvitoNotice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvitoNoticeRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, AvitoNotice::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return AvitoNotice
     */
    public function get(int $id): AvitoNotice
    {
        if (!$avitoNotice = $this->find($id)) {
            throw new EntityNotFoundException('Объявление не найдено');
        }

        return $avitoNotice;
    }

    public function add(AvitoNotice $avitoNotice): void
    {
        $this->em->persist($avitoNotice);
    }

    public function hasByZapCard(ZapCard $zapCard): bool
    {
        return $this->createQueryBuilder('a')
            ->select('Count(a.id)')
            ->andWhere('a.zapCard = :zapCard')
            ->setParameter('zapCard', $zapCard)
            ->getQuery()
            ->getSingleScalarResult() > 0
            ;
    }

    // /**
    //  * @return AvitoNotice[] Returns an array of AvitoNotice objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?AvitoNotice
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
