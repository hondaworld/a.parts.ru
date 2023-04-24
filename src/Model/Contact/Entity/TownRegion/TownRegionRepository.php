<?php

namespace App\Model\Contact\Entity\TownRegion;

use App\Model\Contact\Entity\Country\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TownRegion|null find($id, $lockMode = null, $lockVersion = null)
 * @method TownRegion|null findOneBy(array $criteria, array $orderBy = null)
 * @method TownRegion[]    findAll()
 * @method TownRegion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TownRegionRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, TownRegion::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return TownRegion
     */
    public function get(int $id): TownRegion
    {
        if (!$region = $this->find($id)) {
            throw new EntityNotFoundException('Регион не найден');
        }

        return $region;
    }

    public function add(TownRegion $region): void
    {
        $this->em->persist($region);
    }

    public function hasByCountry(Country $country): bool
    {
        $query = $this->createQueryBuilder('r')
            ->select('COUNT(r.regionID)')
            ->andWhere('r.country = :country')
            ->setParameter('country', $country->getId());

        return $query->getQuery()->getSingleScalarResult() > 0;
    }
}
