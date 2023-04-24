<?php

namespace App\Model\Shop\Entity\Location;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShopLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopLocation[]    findAll()
 * @method ShopLocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopLocationRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ShopLocation::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ShopLocation
     */
    public function get(int $id): ShopLocation
    {
        if (!$shopLocation = $this->find($id)) {
            throw new EntityNotFoundException('Ячейка не найдена');
        }

        return $shopLocation;
    }

    /**
     * @param string $name
     * @return ShopLocation
     */
    public function getByName(string $name): ?ShopLocation
    {
        return $this->findOneBy(['name' => $name]);
    }

    public function add(ShopLocation $shopLocation): void
    {
        $this->em->persist($shopLocation);
    }
}
