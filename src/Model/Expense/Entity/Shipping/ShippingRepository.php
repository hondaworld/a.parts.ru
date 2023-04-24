<?php

namespace App\Model\Expense\Entity\Shipping;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Shipping|null find($id, $lockMode = null, $lockVersion = null)
 * @method Shipping|null findOneBy(array $criteria, array $orderBy = null)
 * @method Shipping[]    findAll()
 * @method Shipping[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Shipping::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Shipping
     */
    public function get(int $id): Shipping
    {
        if (!$shipping = $this->find($id)) {
            throw new EntityNotFoundException('Отгрузка не найдена');
        }

        return $shipping;
    }

    public function add(Shipping $shipping): void
    {
        $this->em->persist($shipping);
    }
}
