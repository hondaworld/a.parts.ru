<?php

namespace App\Model\Card\Entity\Stock;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCardStock|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCardStock|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCardStock[]    findAll()
 * @method ZapCardStock[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardStockRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCardStock::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapCardStock
     */
    public function get(int $id): ZapCardStock
    {
        if (!$zapCardStock = $this->find($id)) {
            throw new EntityNotFoundException('Акция не найдена');
        }

        return $zapCardStock;
    }

    public function add(ZapCardStock $zapCardStock): void
    {
        $this->em->persist($zapCardStock);
    }
}
