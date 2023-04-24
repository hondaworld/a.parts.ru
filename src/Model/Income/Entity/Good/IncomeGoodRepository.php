<?php

namespace App\Model\Income\Entity\Good;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IncomeGood|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncomeGood|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncomeGood[]    findAll()
 * @method IncomeGood[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncomeGoodRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, IncomeGood::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return IncomeGood
     */
    public function get(int $id): IncomeGood
    {
        if (!$incomeGood = $this->find($id)) {
            throw new EntityNotFoundException('Товар прихода не найден');
        }

        return $incomeGood;
    }

    public function add(IncomeGood $incomeGood): void
    {
        $this->em->persist($incomeGood);
    }
}
