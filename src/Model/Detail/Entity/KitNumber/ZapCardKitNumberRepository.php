<?php

namespace App\Model\Detail\Entity\KitNumber;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Kit\ZapCardKit;
use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCardKitNumber|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCardKitNumber|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCardKitNumber[]    findAll()
 * @method ZapCardKitNumber[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardKitNumberRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCardKitNumber::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapCardKitNumber
     */
    public function get(int $id): ZapCardKitNumber
    {
        if (!$zapCardKitNumber = $this->find($id)) {
            throw new EntityNotFoundException('Номер не найден');
        }

        return $zapCardKitNumber;
    }

    public function add(ZapCardKitNumber $zapCardKitNumber): void
    {
        $this->em->persist($zapCardKitNumber);
    }

    public function hasByNumber(ZapCardKit $zapCardKit, DetailNumber $number, int $id = 0): bool
    {
        $query = $this->createQueryBuilder('k')
            ->select('COUNT(k.id)')
            ->andWhere('k.kit = :kit')
            ->setParameter('kit', $zapCardKit)
            ->andWhere('k.number = :number')
            ->setParameter('number', $number->getValue());
        if ($id) {
            $query->andWhere('k.id <> :id')->setParameter('id', $id);
        }

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    public function getMaxSort(ZapCardKit $zapCardKit): int
    {
        $query = $this->createQueryBuilder('m')
            ->select('MAX(m.sort)')
            ->where('m.kit = :kit')
            ->setParameter('kit', $zapCardKit->getId());

        return $query->getQuery()->getOneOrNullResult()[1] ?: 0;
    }

    public function getNextSort(ZapCardKit $zapCardKit): int
    {
        return $this->getMaxSort($zapCardKit) + 1;
    }

    public function addSort(ZapCardKit $zapCardKit, int $sort): void
    {
        $this->changeSort($zapCardKit, $sort - 1, 1);
    }

    public function removeSort(ZapCardKit $zapCardKit, int $sort): void
    {
        $this->changeSort($zapCardKit, $sort, -1);
    }

    public function changeSort(ZapCardKit $zapCardKit, int $sort, int $additional): void
    {
        $qb = $this->createQueryBuilder('s')
            ->update()
            ->set('s.sort', 's.sort + :additional');

        $qb->andWhere($qb->expr()->gt('s.sort', ':sort'));
        $qb->setParameter('sort', $sort);
        $qb->setParameter('additional', $additional);
        $qb->andWhere('s.kit = :kit');
        $qb->setParameter('kit', $zapCardKit->getId());
        $qb->getQuery()->execute();
    }
}
