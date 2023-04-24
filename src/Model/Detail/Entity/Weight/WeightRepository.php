<?php

namespace App\Model\Detail\Entity\Weight;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Weight|null find($id, $lockMode = null, $lockVersion = null)
 * @method Weight|null findOneBy(array $criteria, array $orderBy = null)
 * @method Weight[]    findAll()
 * @method Weight[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeightRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Weight::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Weight
     */
    public function get(int $id): Weight
    {
        if (!$weight = $this->find($id)) {
            throw new EntityNotFoundException('Вес не найден');
        }
        return $weight;
    }

    /**
     * @param DetailNumber $number
     * @param Creater $creater
     * @return Weight
     */
    public function findByNumberAndCreater(DetailNumber $number, Creater $creater): ?Weight
    {
        if (!$weight = $this->findOneBy(['number' => $number, 'creater' => $creater], ['number' => 'asc'])) {
            return null;
        }
        return $weight;
    }

    /**
     * @param ZapCard $zapCard
     * @return Weight
     */
    public function findByZapCard(ZapCard $zapCard): ?Weight
    {
        if (!$weight = $this->findOneBy(['number' => $zapCard->getNumber(), 'creater' => $zapCard->getCreater()], ['number' => 'asc'])) {
            return null;
        }

        return $weight;
    }

    public function add(Weight $weight): void
    {
        $this->em->persist($weight);
    }
}
