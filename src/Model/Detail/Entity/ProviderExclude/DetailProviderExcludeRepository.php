<?php

namespace App\Model\Detail\Entity\ProviderExclude;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DetailProviderExclude|null find($id, $lockMode = null, $lockVersion = null)
 * @method DetailProviderExclude|null findOneBy(array $criteria, array $orderBy = null)
 * @method DetailProviderExclude[]    findAll()
 * @method DetailProviderExclude[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetailProviderExcludeRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, DetailProviderExclude::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return DetailProviderExclude
     */
    public function get(int $id): DetailProviderExclude
    {
        if (!$detailProviderExclude = $this->find($id)) {
            throw new EntityNotFoundException('Регион не найден');
        }

        return $detailProviderExclude;
    }

    public function add(DetailProviderExclude $detailProviderExclude): void
    {
        $this->em->persist($detailProviderExclude);
    }
}
