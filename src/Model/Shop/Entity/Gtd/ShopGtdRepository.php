<?php

namespace App\Model\Shop\Entity\Gtd;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ShopGtd|null find($id, $lockMode = null, $lockVersion = null)
 * @method ShopGtd|null findOneBy(array $criteria, array $orderBy = null)
 * @method ShopGtd[]    findAll()
 * @method ShopGtd[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ShopGtdRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ShopGtd::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ShopGtd
     */
    public function get(int $id): ShopGtd
    {
        if (!$shopGtd = $this->find($id)) {
            throw new EntityNotFoundException('ГТД не найдено');
        }

        return $shopGtd;
    }

    /**
     * @return ShopGtd
     */
    public function getRand(): ShopGtd
    {
        $count = $this->createQueryBuilder('g')->select('COUNT(g.shop_gtdID)')->getQuery()->getSingleScalarResult();
        $offset = max(0, rand(0, $count - 2));

        $query = $this->createQueryBuilder('g')->select('g');
//        $query->andWhere($query->expr()->gt('LENGTH(g.name)', 12));
        $query->setMaxResults(1)->setFirstResult($offset);
        return $query->getQuery()->getSingleResult();
    }

    public function add(ShopGtd $shopGtd): void
    {
        $this->em->persist($shopGtd);
    }

    public function hasByGtd(Gtd $name, int $id = 0): bool
    {
        $query = $this->createQueryBuilder('g')
            ->select('COUNT(g.shop_gtdID)')
            ->andWhere('g.name = :name')
            ->setParameter('name', $name->getValue());

        if ($id) {
            $query->andWhere('g.shop_gtdID <> :id')->setParameter('id', $id);
        }

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @param Gtd $name
     * @return ShopGtd
     */
    public function getOrCreate(Gtd $name): ShopGtd
    {
        $shopGtd = $this->findOneBy(['name' => $name]);
        if (!$shopGtd) {
            $shopGtd = new ShopGtd($name);
            $this->add($shopGtd);
        }
        return $shopGtd;
    }
}
