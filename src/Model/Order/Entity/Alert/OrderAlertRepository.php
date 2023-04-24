<?php

namespace App\Model\Order\Entity\Alert;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderAlert|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderAlert|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderAlert[]    findAll()
 * @method OrderAlert[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderAlertRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, OrderAlert::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return OrderAlert
     */
    public function get(int $id): OrderAlert
    {
        if (!$orderAlert = $this->find($id)) {
            throw new EntityNotFoundException('Алерт не найден');
        }

        return $orderAlert;
    }

    public function add(OrderAlert $orderAlert): void
    {
        $this->em->persist($orderAlert);
    }
}
