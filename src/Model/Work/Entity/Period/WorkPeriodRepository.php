<?php

namespace App\Model\Work\Entity\Period;

use App\Model\Auto\Entity\Modification\AutoModification;
use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WorkPeriod|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkPeriod|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkPeriod[]    findAll()
 * @method WorkPeriod[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkPeriodRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, WorkPeriod::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return WorkPeriod
     * @throws EntityNotFoundException
     */
    public function get(int $id): WorkPeriod
    {
        if (!$workPeriod = $this->find($id)) {
            throw new EntityNotFoundException('Период не найден');
        }

        return $workPeriod;
    }

    public function add(WorkPeriod $workPeriod): void
    {
        $this->em->persist($workPeriod);
    }

    public function getMaxSort(AutoModification $autoModification): int
    {
        $query = $this->createQueryBuilder('m')
            ->select('MAX(m.number)')
            ->where('m.auto_modification = :auto_modification')
            ->setParameter('auto_modification', $autoModification->getId());

        return $query->getQuery()->getOneOrNullResult()[1] ?: 0;
    }

    public function getNextSort(AutoModification $autoModification): int
    {
        return $this->getMaxSort($autoModification) + 1;
    }

    public function addSort(AutoModification $autoModification, int $sort): void
    {
        $this->changeSort($autoModification, $sort - 1, 1);
    }

    public function removeSort(AutoModification $autoModification, int $sort): void
    {
        $this->changeSort($autoModification, $sort, -1);
    }

    public function changeSort(AutoModification $autoModification, int $sort, int $additional): void
    {
        $qb = $this->createQueryBuilder('s')
            ->update()
            ->set('s.number', 's.number + :additional');

        $qb->andWhere($qb->expr()->gt('s.number', ':number'));
        $qb->setParameter('number', $sort);
        $qb->setParameter('additional', $additional);
        $qb->andWhere('s.auto_modification = :auto_modification');
        $qb->setParameter('auto_modification', $autoModification->getId());
        $qb->getQuery()->execute();
    }
}
