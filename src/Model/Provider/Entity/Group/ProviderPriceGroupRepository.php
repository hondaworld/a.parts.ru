<?php

namespace App\Model\Provider\Entity\Group;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProviderPriceGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProviderPriceGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProviderPriceGroup[]    findAll()
 * @method ProviderPriceGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProviderPriceGroupRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ProviderPriceGroup::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ProviderPriceGroup
     */
    public function get(int $id): ProviderPriceGroup
    {
        if (!$providerPriceGroup = $this->find($id)) {
            throw new EntityNotFoundException('Группа не найдена');
        }

        return $providerPriceGroup;
    }
}
