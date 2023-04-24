<?php

namespace App\Model\Shop\Entity\Reseller;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Reseller|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reseller|null findOneBy(array $criteria, array $orderBy = null)
 * @method Reseller[]    findAll()
 * @method Reseller[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResellerRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Reseller::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Reseller
     */
    public function get(int $id): Reseller
    {
        if (!$reseller = $this->find($id)) {
            throw new EntityNotFoundException('Реселлер не найден');
        }

        return $reseller;
    }

    public function add(Reseller $reseller): void
    {
        $this->em->persist($reseller);
    }
}
