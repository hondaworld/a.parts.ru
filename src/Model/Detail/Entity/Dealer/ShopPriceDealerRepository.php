<?php

namespace App\Model\Detail\Entity\Dealer;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShopPriceDealer|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopPriceDealer|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopPriceDealer[]    findAll()
 * @method ShopPriceDealer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopPriceDealerRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ShopPriceDealer::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ShopPriceDealer
     */
    public function get(int $id): ShopPriceDealer
    {
        if (!$shopPriceDealer = $this->find($id)) {
            throw new EntityNotFoundException('Цена не найдена');
        }
        return $shopPriceDealer;
    }

    /**
     * @param DetailNumber $number
     * @param Creater $creater
     * @return ShopPriceDealer
     */
    public function findByNumberAndCreater(DetailNumber $number, Creater $creater): ?ShopPriceDealer
    {
        if (!$shopPriceDealer = $this->findOneBy(['number' => $number, 'creater' => $creater], ['number' => 'asc'])) {
            return null;
        }
        return $shopPriceDealer;
    }

    /**
     * @param ZapCard $zapCard
     * @return ShopPriceDealer
     */
    public function findByZapCard(ZapCard $zapCard): ?ShopPriceDealer
    {
        if (!$shopPriceDealer = $this->findOneBy(['number' => $zapCard->getNumber(), 'creater' => $zapCard->getCreater()], ['number' => 'asc'])) {
            return null;
        }

        return $shopPriceDealer;
    }

    public function add(ShopPriceDealer $shopPriceDealer): void
    {
        $this->em->persist($shopPriceDealer);
    }
}
