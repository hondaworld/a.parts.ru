<?php

namespace App\Model\Contact\Entity\Town;

use App\Model\Contact\Entity\TownRegion\TownRegion;
use App\Model\Contact\Entity\TownType\TownType;
use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Town|null find($id, $lockMode = null, $lockVersion = null)
 * @method Town|null findOneBy(array $criteria, array $orderBy = null)
 * @method Town[]    findAll()
 * @method Town[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TownRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Town::class);
        $this->em = $em;
    }
    /**
     * @param int $id
     * @return Town
     */
    public function get(int $id): Town
    {
        if (!$town = $this->find($id)) {
            throw new EntityNotFoundException('Город не найден');
        }

        return $town;
    }

    public function add(Town $town): void
    {
        $this->em->persist($town);
    }

    public function hasByRegion(TownRegion $region): bool
    {
        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t.townID)')
            ->andWhere('t.region = :region')
            ->setParameter('region', $region->getId());

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    public function hasByType(TownType $type): bool
    {
        $query = $this->createQueryBuilder('t')
            ->select('COUNT(t.townID)')
            ->andWhere('t.type = :type')
            ->setParameter('type', $type->getId());

        return $query->getQuery()->getSingleScalarResult() > 0;
    }
}
