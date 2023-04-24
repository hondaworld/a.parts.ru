<?php

namespace App\Model\Contact\Entity\TownType;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method TownType|null find($id, $lockMode = null, $lockVersion = null)
 * @method TownType|null findOneBy(array $criteria, array $orderBy = null)
 * @method TownType[]    findAll()
 * @method TownType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TownTypeRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, TownType::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return TownType
     */
    public function get(int $id): TownType
    {
        if (!$type = $this->find($id)) {
            throw new EntityNotFoundException('Тип не найден');
        }

        return $type;
    }

    public function add(TownType $type): void
    {
        $this->em->persist($type);
    }
}
