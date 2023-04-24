<?php

namespace App\Model\Finance\Entity\FinanceType;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FinanceType|null find($id, $lockMode = null, $lockVersion = null)
 * @method FinanceType|null findOneBy(array $criteria, array $orderBy = null)
 * @method FinanceType[]    findAll()
 * @method FinanceType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FinanceTypeRepository extends ServiceEntityRepository
{
    private $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, FinanceType::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return FinanceType
     */
    public function get(int $id): FinanceType
    {
        if (!$financeType = $this->find($id)) {
            throw new EntityNotFoundException('Вид оплаты не найден');
        }

        return $financeType;
    }

    public function updateMain(): void
    {
        $qb = $this->createQueryBuilder('f')
            ->update()
            ->set('f.isMain', 'false');
        $qb->getQuery()->execute();
    }

    public function add(FinanceType $financeType): void
    {
        $this->em->persist($financeType);
    }
}
