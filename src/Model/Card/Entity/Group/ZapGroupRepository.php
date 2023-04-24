<?php

namespace App\Model\Card\Entity\Group;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ZapGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapGroup[]    findAll()
 * @method ZapGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapGroupRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ZapGroup::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ZapGroup
     * @throws EntityNotFoundException
     */
    public function get(int $id): ZapGroup
    {
        if (!$zapGroup = $this->find($id)) {
            throw new EntityNotFoundException('Группа товаров не найдена');
        }

        return $zapGroup;
    }

    public function add(ZapGroup $zapGroup): void
    {
        $this->em->persist($zapGroup);
    }
}
