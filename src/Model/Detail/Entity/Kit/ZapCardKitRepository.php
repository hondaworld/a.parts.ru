<?php

namespace App\Model\Detail\Entity\Kit;

use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCardKit|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCardKit|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCardKit[]    findAll()
 * @method ZapCardKit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardKitRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCardKit::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapCardKit
     */
    public function get(int $id): ZapCardKit
    {
        if (!$zapCardKit = $this->find($id)) {
            throw new EntityNotFoundException('Комплект не найден');
        }

        return $zapCardKit;
    }

    public function add(ZapCardKit $zapCardKit): void
    {
        $this->em->persist($zapCardKit);
    }

    public function getMaxSort(AutoModel $autoModel): int
    {
        $query = $this->createQueryBuilder('m')
            ->select('MAX(m.sort)')
            ->where('m.auto_model = :auto_model')
            ->setParameter('auto_model', $autoModel->getId());

        return $query->getQuery()->getOneOrNullResult()[1] ?: 0;
    }

    public function getNextSort(AutoModel $autoModel): int
    {
        return $this->getMaxSort($autoModel) + 1;
    }

    public function addSort(AutoModel $autoModel, int $sort): void
    {
        $this->changeSort($autoModel, $sort - 1, 1);
    }

    public function removeSort(AutoModel $autoModel, int $sort): void
    {
        $this->changeSort($autoModel, $sort, -1);
    }

    public function changeSort(AutoModel $autoModel, int $sort, int $additional): void
    {
        $qb = $this->createQueryBuilder('s')
            ->update()
            ->set('s.sort', 's.sort + :additional');

        $qb->andWhere($qb->expr()->gt('s.sort', ':sort'));
        $qb->setParameter('sort', $sort);
        $qb->setParameter('additional', $additional);
        $qb->andWhere('s.auto_model = :auto_model');
        $qb->setParameter('auto_model', $autoModel->getId());
        $qb->getQuery()->execute();
    }
}
