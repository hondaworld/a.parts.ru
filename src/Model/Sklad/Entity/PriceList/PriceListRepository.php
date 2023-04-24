<?php

namespace App\Model\Sklad\Entity\PriceList;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PriceList|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceList|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceList[]    findAll()
 * @method PriceList[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceListRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, PriceList::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return PriceList
     */
    public function get(int $id): PriceList
    {
        if (!$priceList = $this->find($id)) {
            throw new EntityNotFoundException('Прайс-лист не найден');
        }

        return $priceList;
    }

    /**
     * @return PriceList|null
     */
    public function getMain(): ?PriceList
    {
        return $this->findOneBy(['isMain' => 1]);
    }

    public function add(PriceList $priceList): void
    {
        $this->em->persist($priceList);
    }

    public function updateMain()
    {
        $this->createQueryBuilder('pl')
            ->update()
            ->set('pl.isMain', '0')
            ->getQuery()
            ->execute();
    }
}
