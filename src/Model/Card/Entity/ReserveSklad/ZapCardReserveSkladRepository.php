<?php

namespace App\Model\Card\Entity\ReserveSklad;

use App\Model\EntityNotFoundException;
use App\Model\Expense\Entity\Sklad\ExpenseSklad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCardReserveSklad|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCardReserveSklad|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCardReserveSklad[]    findAll()
 * @method ZapCardReserveSklad[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardReserveSkladRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCardReserveSklad::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapCardReserveSklad
     */
    public function get(int $id): ZapCardReserveSklad
    {
        if (!$documentType = $this->find($id)) {
            throw new EntityNotFoundException('Резерв не найден');
        }

        return $documentType;
    }

    public function add(ZapCardReserveSklad $zapCardReserveSklad): void
    {
        $this->em->persist($zapCardReserveSklad);
    }

    public function remove(ZapCardReserveSklad $zapCardReserveSklad): void
    {
        $this->em->remove($zapCardReserveSklad);
    }

    /**
     * @param ExpenseSklad $expenseSklad
     * @return ZapCardReserveSklad[]
     */
    public function findByExpenseSklad(ExpenseSklad $expenseSklad): array
    {
        return $this->createQueryBuilder('zcrs')
            ->andWhere('zcrs.expense_sklad = :expense_sklad')
            ->setParameter('expense_sklad', $expenseSklad)
            ->getQuery()
            ->getResult()
            ;
    }
}
