<?php

namespace App\Model\Expense\Entity\ShippingStatus;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShippingStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingStatus[]    findAll()
 * @method ShippingStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingStatusRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ShippingStatus::class);
        $this->em = $em;
    }

    /**
     * @return ShippingStatus[]
     */
    public function allByNumber(): array
    {
        return $this->findBy([], ['number' => 'asc']);
    }

    /**
     * @param int $id
     * @return ShippingStatus
     */
    public function get(int $id): ShippingStatus
    {
        if (!$shippingStatus = $this->find($id)) {
            throw new EntityNotFoundException('Статус не найден');
        }

        return $shippingStatus;
    }

    public function add(ShippingStatus $shippingStatus): void
    {
        $this->em->persist($shippingStatus);
    }
}
