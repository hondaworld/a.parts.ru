<?php

namespace App\Model\Work\Entity\Group;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method WorkGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method WorkGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method WorkGroup[]    findAll()
 * @method WorkGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WorkGroupRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, WorkGroup::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return WorkGroup
     * @throws EntityNotFoundException
     */
    public function get(int $id): WorkGroup
    {
        if (!$workGroup = $this->find($id)) {
            throw new EntityNotFoundException('Группа не найдена');
        }

        return $workGroup;
    }

    public function add(WorkGroup $workGroup): void
    {
        $this->em->persist($workGroup);
    }
}
