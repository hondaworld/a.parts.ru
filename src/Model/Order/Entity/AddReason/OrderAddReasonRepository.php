<?php

namespace App\Model\Order\Entity\AddReason;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderAddReason|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderAddReason|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderAddReason[]    findAll()
 * @method OrderAddReason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderAddReasonRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, OrderAddReason::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return OrderAddReason
     */
    public function get(int $id): OrderAddReason
    {
        if (!$orderAddReason = $this->find($id)) {
            throw new EntityNotFoundException('Причина добавления не найдена');
        }

        return $orderAddReason;
    }

    public function add(OrderAddReason $orderAddReason): void
    {
        $this->em->persist($orderAddReason);
    }
}
