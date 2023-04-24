<?php

namespace App\Model\User\Entity\ShopPayType;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShopPayType|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopPayType|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopPayType[]    findAll()
 * @method ShopPayType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopPayTypeRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ShopPayType::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ShopPayType
     * @throws EntityNotFoundException
     */
    public function get(int $id): ShopPayType
    {
        if (!$shopPayType = $this->find($id)) {
            throw new EntityNotFoundException('Метод оплаты клиентов не найден');
        }

        return $shopPayType;
    }

    public function add(ShopPayType $shopPayType): void
    {
        $this->em->persist($shopPayType);
    }
}
