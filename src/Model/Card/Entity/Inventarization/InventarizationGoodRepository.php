<?php

namespace App\Model\Card\Entity\Inventarization;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method InventarizationGood|null find($id, $lockMode = null, $lockVersion = null)
 * @method InventarizationGood|null findOneBy(array $criteria, array $orderBy = null)
 * @method InventarizationGood[]    findAll()
 * @method InventarizationGood[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InventarizationGoodRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, InventarizationGood::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return InventarizationGood
     * @throws EntityNotFoundException
     */
    public function get(int $id): InventarizationGood
    {
        if (!$good = $this->find($id)) {
            throw new EntityNotFoundException('Товар не найден');
        }

        return $good;
    }

    public function add(InventarizationGood $good): void
    {
        $this->em->persist($good);
    }

    public function findByZapCardAndZapSklad(Inventarization $inventarization, ZapCard $zapCard, ZapSklad $zapSklad): ?InventarizationGood
    {
        return $this->findOneBy(['inventarization' => $inventarization, 'zapCard' => $zapCard, 'zapSklad' => $zapSklad]);
    }
}
