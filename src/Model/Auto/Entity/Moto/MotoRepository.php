<?php

namespace App\Model\Auto\Entity\Moto;

use App\Model\Auto\Entity\Auto\Vin;
use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Moto|null find($id, $lockMode = null, $lockVersion = null)
 * @method Moto|null findOneBy(array $criteria, array $orderBy = null)
 * @method Moto[]    findAll()
 * @method Moto[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MotoRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Moto::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Moto
     */
    public function get(int $id): Moto
    {
        if (!$moto = $this->find($id)) {
            throw new EntityNotFoundException('Мотоцикл не найден');
        }

        return $moto;
    }

    public function hasByVin(Vin $vin, int $id = 0): bool
    {
        $query = $this->createQueryBuilder('m')
            ->select('COUNT(m.motoID)')
            ->andWhere('m.vin = :vin')
            ->setParameter('vin', $vin->getValue())
            ->andWhere("m.vin <> ''")
        ;

        if ($id) {
            $query->andWhere('m.motoID <> :id')->setParameter('id', $id);
        }

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    public function add(Moto $moto): void
    {
        $this->em->persist($moto);
    }
}
