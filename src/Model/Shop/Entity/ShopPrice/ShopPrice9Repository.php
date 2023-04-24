<?php

namespace App\Model\Shop\Entity\ShopPrice;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShopPrice1|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopPrice1|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopPrice1[]    findAll()
 * @method ShopPrice1[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopPrice9Repository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ShopPrice9::class);
        $this->em = $em;
    }
}
