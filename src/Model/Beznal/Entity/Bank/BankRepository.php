<?php

namespace App\Model\Beznal\Entity\Bank;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Bank|null find($id, $lockMode = null, $lockVersion = null)
 * @method Bank|null findOneBy(array $criteria, array $orderBy = null)
 * @method Bank[]    findAll()
 * @method Bank[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BankRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Bank::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Bank
     */
    public function get(int $id): Bank
    {
        if (!$bank = $this->find($id)) {
            throw new EntityNotFoundException('Банк не найден');
        }

        return $bank;
    }

    public function add(Bank $bank): void
    {
        $this->em->persist($bank);
    }

    public function hasByBik(string $bik, int $id = 0): bool
    {
        $query = $this->createQueryBuilder('b')
            ->select('COUNT(b.bankID)')
            ->andWhere('b.bik = :bik')
            ->setParameter('bik', $bik);

        if ($id) {
            $query->andWhere('b.bankID <> :id')->setParameter('id', $id);
        }

        return $query->getQuery()->getSingleScalarResult() > 0;
    }
}
