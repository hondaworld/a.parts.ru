<?php

namespace App\Model\Shop\Entity\ShopType;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShopType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopType[]    findAll()
 * @method ShopType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopTypeRepository extends ServiceEntityRepository
{

    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ShopType::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ShopType
     */
    public function get(int $id): ShopType
    {
        if (!$shopType = $this->find($id)) {
            throw new EntityNotFoundException('Вид товаров не найден');
        }

        return $shopType;
    }

    public function add(ShopType $shopType): void
    {
        $this->em->persist($shopType);
    }
}
