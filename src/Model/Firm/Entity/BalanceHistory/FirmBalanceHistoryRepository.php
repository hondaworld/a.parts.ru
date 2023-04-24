<?php

namespace App\Model\Firm\Entity\BalanceHistory;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method FirmBalanceHistory|null find($id, $lockMode = null, $lockVersion = null)
 * @method FirmBalanceHistory|null findOneBy(array $criteria, array $orderBy = null)
 * @method FirmBalanceHistory[]    findAll()
 * @method FirmBalanceHistory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FirmBalanceHistoryRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, FirmBalanceHistory::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return FirmBalanceHistory
     */
    public function get(int $id): FirmBalanceHistory
    {
        if (!$firmBalanceHistory = $this->find($id)) {
            throw new EntityNotFoundException('Запись не найдена');
        }

        return $firmBalanceHistory;
    }

    public function add(FirmBalanceHistory $firmBalanceHistory): void
    {
        $this->em->persist($firmBalanceHistory);
    }
}
