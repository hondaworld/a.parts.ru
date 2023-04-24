<?php

namespace App\Model\Card\Entity\Location;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\EntityNotFoundException;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapSkladLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapSkladLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapSkladLocation[]    findAll()
 * @method ZapSkladLocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapSkladLocationRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapSkladLocation::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapSkladLocation
     */
    public function get(int $id): ZapSkladLocation
    {
        if (!$zapSkladLocation = $this->find($id)) {
            throw new EntityNotFoundException('Ячейка не найдена');
        }

        return $zapSkladLocation;
    }

    /**
     * @param ZapCard $zapCard
     * @param ZapSklad $zapSklad
     * @return ZapSkladLocation
     */
    public function getOrCreate(ZapCard $zapCard, ZapSklad $zapSklad): ZapSkladLocation
    {
        $zapSkladLocation = $this->findOneBy(['zapCard' => $zapCard, 'zapSklad' => $zapSklad]);
        if (!$zapSkladLocation) {
            $zapSkladLocation = new ZapSkladLocation(
                $zapCard,
                $zapSklad
            );
            $this->add($zapSkladLocation);
        }
        return $zapSkladLocation;
    }

    public function add(ZapSkladLocation $zapSkladLocation): void
    {
        $this->em->persist($zapSkladLocation);
    }
}
