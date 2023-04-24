<?php

namespace App\Model\User\Entity\BalanceHistory;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserBalanceHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserBalanceHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserBalanceHistory[]    findAll()
 * @method UserBalanceHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserBalanceHistoryRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, UserBalanceHistory::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return UserBalanceHistory
     */
    public function get(int $id): UserBalanceHistory
    {
        if (!$userBalanceHistory = $this->find($id)) {
            throw new EntityNotFoundException('Запись не найдена');
        }

        return $userBalanceHistory;
    }

    public function add(UserBalanceHistory $userBalanceHistory): void
    {
        $this->em->persist($userBalanceHistory);
    }
}
