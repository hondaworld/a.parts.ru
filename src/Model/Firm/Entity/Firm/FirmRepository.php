<?php

namespace App\Model\Firm\Entity\Firm;

use App\Model\EntityNotFoundException;
use App\Model\Finance\Entity\Nalog\Nalog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Firm|null find($id, $lockMode = null, $lockVersion = null)
 * @method Firm|null findOneBy(array $criteria, array $orderBy = null)
 * @method Firm[]    findAll()
 * @method Firm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FirmRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Firm::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Firm
     */
    public function get(int $id): Firm
    {
        if (!$firm = $this->find($id)) {
            throw new EntityNotFoundException('Организация не найдена');
        }

        return $firm;
    }

    public function add(Firm $firm): void
    {
        $this->em->persist($firm);
    }

    public function hasByNalog(Nalog $nalog): bool
    {
        $query = $this->createQueryBuilder('f')
            ->select('COUNT(f.firmID)')
            ->andWhere('f.nalog = :nalog')
            ->setParameter('nalog', $nalog->getId());

        return $query->getQuery()->getSingleScalarResult() > 0;
    }
}
