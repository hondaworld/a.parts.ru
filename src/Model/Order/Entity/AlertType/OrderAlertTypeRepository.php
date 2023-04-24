<?php

namespace App\Model\Order\Entity\AlertType;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderAlertType|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderAlertType|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderAlertType[]    findAll()
 * @method OrderAlertType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderAlertTypeRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, OrderAlertType::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return OrderAlertType
     */
    public function get(int $id): OrderAlertType
    {
        if (!$orderAlertType = $this->find($id)) {
            throw new EntityNotFoundException('Тип не найден');
        }

        return $orderAlertType;
    }

    public function changeStatusType(): OrderAlertType
    {
        return $this->get(OrderAlertType::CHANGE_STATUS);
    }

    public function purchaseType(): OrderAlertType
    {
        return $this->get(OrderAlertType::PURCHASE);
    }

    public function movingType(): OrderAlertType
    {
        return $this->get(OrderAlertType::MOVING);
    }

    public function removeReserveType(): OrderAlertType
    {
        return $this->get(OrderAlertType::REMOVE_RESERVE);
    }

    public function add(OrderAlertType $orderAlertType): void
    {
        $this->em->persist($orderAlertType);
    }
}
