<?php

namespace App\Model\Detail\Entity\PriceExclude;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DetailProviderPriceExclude|null find($id, $lockMode = null, $lockVersion = null)
 * @method DetailProviderPriceExclude|null findOneBy(array $criteria, array $orderBy = null)
 * @method DetailProviderPriceExclude[]    findAll()
 * @method DetailProviderPriceExclude[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetailProviderPriceExcludeRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, DetailProviderPriceExclude::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return DetailProviderPriceExclude
     */
    public function get(int $id): DetailProviderPriceExclude
    {
        if (!$detailProviderPriceExclude = $this->find($id)) {
            throw new EntityNotFoundException('Регион не найден');
        }

        return $detailProviderPriceExclude;
    }

    public function add(DetailProviderPriceExclude $detailProviderPriceExclude): void
    {
        $this->em->persist($detailProviderPriceExclude);
    }
}
