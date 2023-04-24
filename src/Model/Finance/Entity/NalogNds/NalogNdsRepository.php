<?php

namespace App\Model\Finance\Entity\NalogNds;

use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Firm\Entity\Firm\Firm;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Model\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @method NalogNds|null find($id, $lockMode = null, $lockVersion = null)
 * @method NalogNds|null findOneBy(array $criteria, array $orderBy = null)
 * @method NalogNds[]    findAll()
 * @method NalogNds[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NalogNdsRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, NalogNds::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return NalogNds
     */
    public function get(int $id): NalogNds
    {
        if (!$nalogNds = $this->find($id)) {
            throw new EntityNotFoundException('НДС не найдена');
        }

        return $nalogNds;
    }

    public function add(NalogNds $nalogNds): void
    {
        $this->em->persist($nalogNds);
    }

    public function hasNdsMoreOne(Nalog $nalog): bool
    {
        $query = $this->createQueryBuilder('n')
            ->select('COUNT(n.nalogNdsID)')
            ->andWhere('n.nalog = :nalog')
            ->setParameter('nalog', $nalog->getId());

        return $query->getQuery()->getSingleScalarResult() > 1;
    }

    public function getLastByFirm(Firm $firm, ?\DateTime $dateofadded = null): ?NalogNds
    {
        $query = $this->createQueryBuilder('nn')
            ->select('nn', 'n')
            ->innerJoin('nn.nalog', 'n')
            ->innerJoin('n.firms', 'f')
            ->andWhere('f.firmID = :firmID')
            ->setParameter('firmID', $firm->getId())
            ->orderBy('nn.dateofadded', 'DESC')
            ->setMaxResults(1)
        ;

        if ($dateofadded) {
            $query->andWhere('nn.dateofadded <= :dateofadded')->setParameter('dateofadded', $dateofadded);
        }

        return $query->getQuery()->getOneOrNullResult();
    }
}
