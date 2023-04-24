<?php

namespace App\Model\Expense\Entity\ShippingPlace;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShippingPlace|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShippingPlace|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShippingPlace[]    findAll()
 * @method ShippingPlace[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShippingPlaceRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ShippingPlace::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ShippingPlace
     */
    public function get(int $id): ShippingPlace
    {
        if (!$shippingPlace = $this->find($id)) {
            throw new EntityNotFoundException('Место не найдено');
        }

        return $shippingPlace;
    }

    public function add(ShippingPlace $shippingPlace): void
    {
        $this->em->persist($shippingPlace);
    }
}
