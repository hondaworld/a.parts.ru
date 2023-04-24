<?php

namespace App\Model\Card\Entity\Inventarization;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Inventarization|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inventarization|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inventarization[]    findAll()
 * @method Inventarization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventarizationRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Inventarization::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Inventarization
     * @throws EntityNotFoundException
     */
    public function get(int $id): Inventarization
    {
        if (!$inventarization = $this->find($id)) {
            throw new EntityNotFoundException('Инвентаризация не найдена');
        }

        return $inventarization;
    }

    public function add(Inventarization $inventarization): void
    {
        $this->em->persist($inventarization);
    }
}
