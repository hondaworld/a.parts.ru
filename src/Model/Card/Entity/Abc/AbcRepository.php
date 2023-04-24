<?php

namespace App\Model\Card\Entity\Abc;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Abc|null find($id, $lockMode = null, $lockVersion = null)
 * @method Abc|null findOneBy(array $criteria, array $orderBy = null)
 * @method Abc[]    findAll()
 * @method Abc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AbcRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Abc::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Abc
     */
    public function get(int $id): Abc
    {
        if (!$abc = $this->find($id)) {
            throw new EntityNotFoundException('ABC не найдена');
        }

        return $abc;
    }

    public function hasByAbc(string $abc, int $id = 0): bool
    {
        $query = $this->createQueryBuilder('a')
            ->select('COUNT(a.abcID)')
            ->andWhere('a.abc = :abc')
            ->setParameter('abc', $abc);

        if ($id) {
            $query->andWhere('a.abcID <> :id')->setParameter('id', $id);
        }

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    public function add(Abc $abc): void
    {
        $this->em->persist($abc);
    }
}
