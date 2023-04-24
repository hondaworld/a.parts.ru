<?php

namespace App\Model\Auto\Entity\MotoGroup;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MotoGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method MotoGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method MotoGroup[]    findAll()
 * @method MotoGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MotoGroupRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, MotoGroup::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return MotoGroup
     */
    public function get(int $id): MotoGroup
    {
        if (!$motoGroup = $this->find($id)) {
            throw new EntityNotFoundException('Авто не найдено');
        }

        return $motoGroup;
    }

    public function add(MotoGroup $motoGroup): void
    {
        $this->em->persist($motoGroup);
    }
}
