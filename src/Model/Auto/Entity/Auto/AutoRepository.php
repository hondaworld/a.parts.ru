<?php

namespace App\Model\Auto\Entity\Auto;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Auto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Auto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Auto[]    findAll()
 * @method Auto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutoRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Auto::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Auto
     */
    public function get(int $id): Auto
    {
        if (!$auto = $this->find($id)) {
            throw new EntityNotFoundException('Автомобиль не найден');
        }

        return $auto;
    }

    public function hasByVin(Vin $vin, int $id = 0): bool
    {
        $query = $this->createQueryBuilder('a')
            ->select('COUNT(a.autoID)')
            ->andWhere('a.vin = :vin')
            ->setParameter('vin', $vin->getValue())
            ->andWhere("a.vin <> ''")
        ;

        if ($id) {
            $query->andWhere('a.autoID <> :id')->setParameter('id', $id);
        }

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    public function add(Auto $auto): void
    {
        $this->em->persist($auto);
    }
}
