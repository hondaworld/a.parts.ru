<?php

namespace App\Model\Detail\Entity\Zamena;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShopZamena|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopZamena|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopZamena[]    findAll()
 * @method ShopZamena[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopZamenaRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ShopZamena::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ShopZamena
     */
    public function get(int $id): ShopZamena
    {
        if (!$shopZamena = $this->find($id)) {
            throw new EntityNotFoundException('Замена не найдена');
        }

        return $shopZamena;
    }

    public function add(ShopZamena $shopZamena): void
    {
        $this->em->persist($shopZamena);
    }
}
