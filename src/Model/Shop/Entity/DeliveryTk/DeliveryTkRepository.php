<?php

namespace App\Model\Shop\Entity\DeliveryTk;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeliveryTk|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryTk|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryTk[]    findAll()
 * @method DeliveryTk[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryTkRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, DeliveryTk::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return DeliveryTk
     */
    public function get(int $id): DeliveryTk
    {
        if (!$deliveryTk = $this->find($id)) {
            throw new EntityNotFoundException('ТК отгрузки не найдена');
        }

        return $deliveryTk;
    }

    public function add(DeliveryTk $deliveryTk): void
    {
        $this->em->persist($deliveryTk);
    }
}
