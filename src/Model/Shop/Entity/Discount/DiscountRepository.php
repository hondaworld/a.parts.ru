<?php

namespace App\Model\Shop\Entity\Discount;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Discount|null find($id, $lockMode = null, $lockVersion = null)
 * @method Discount|null findOneBy(array $criteria, array $orderBy = null)
 * @method Discount[]    findAll()
 * @method Discount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DiscountRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Discount::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Discount
     */
    public function get(int $id): Discount
    {
        if (!$discount = $this->find($id)) {
            throw new EntityNotFoundException('Скидка не найдена');
        }

        return $discount;
    }

    public function add(Discount $discount): void
    {
        $this->em->persist($discount);
    }
}
