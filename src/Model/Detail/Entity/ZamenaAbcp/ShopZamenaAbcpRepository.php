<?php

namespace App\Model\Detail\Entity\ZamenaAbcp;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShopZamenaAbcp|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopZamenaAbcp|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopZamenaAbcp[]    findAll()
 * @method ShopZamenaAbcp[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopZamenaAbcpRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ShopZamenaAbcp::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ShopZamenaAbcp
     */
    public function get(int $id): ShopZamenaAbcp
    {
        if (!$shopZamena = $this->find($id)) {
            throw new EntityNotFoundException('Замена не найдена');
        }

        return $shopZamena;
    }

    public function add(ShopZamenaAbcp $shopZamena): void
    {
        $this->em->persist($shopZamena);
    }
}
