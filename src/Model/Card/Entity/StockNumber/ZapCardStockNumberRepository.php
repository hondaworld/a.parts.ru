<?php

namespace App\Model\Card\Entity\StockNumber;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapCardStockNumber|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCardStockNumber|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCardStockNumber[]    findAll()
 * @method ZapCardStockNumber[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardStockNumberRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapCardStockNumber::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapCardStockNumber
     */
    public function get(int $id): ZapCardStockNumber
    {
        if (!$zapCardStockNumber = $this->find($id)) {
            throw new EntityNotFoundException('Номер в акции не найден');
        }

        return $zapCardStockNumber;
    }

    public function add(ZapCardStockNumber $zapCardStockNumber): void
    {
        $this->em->persist($zapCardStockNumber);
    }

    public function deleteWithNumberAndCreater(DetailNumber $number, Creater $creater): void
    {
        $this->createQueryBuilder('n')
            ->delete()
            ->andWhere('n.number = :number')
            ->andWhere('n.creater = :creater')
            ->setParameter('number', $number->getValue())
            ->setParameter('creater', $creater)
            ->getQuery()
            ->execute();
    }

    public function findFromNumberAndCreater(DetailNumber $number, Creater $creater, bool $onlyActive = true): ?ZapCardStockNumber
    {
        $qb = $this->em->createQueryBuilder()
            ->select('n', 's')
            ->from('Card:StockNumber\ZapCardStockNumber', 'n')
            ->innerJoin('n.stock', 's')
            ->where('n.number = :number')
            ->andWhere('n.creater = :creater')
            ->setParameter('number', $number->getValue())
            ->setParameter('creater', $creater);

        if ($onlyActive) {
            $qb = $qb->andWhere('s.isHide = false');
        }

        $qb = $qb->getQuery()
            ->getOneOrNullResult();

        return $qb;
    }
}
