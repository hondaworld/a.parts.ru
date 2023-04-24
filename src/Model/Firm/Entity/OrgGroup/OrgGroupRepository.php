<?php

namespace App\Model\Firm\Entity\OrgGroup;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Model\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrgGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrgGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrgGroup[]    findAll()
 * @method OrgGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrgGroupRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, OrgGroup::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return OrgGroup
     */
    public function get(int $id): OrgGroup
    {
        if (!$orgGroup = $this->find($id)) {
            throw new EntityNotFoundException('Подразделение не найдено');
        }

        return $orgGroup;
    }

    /**
     * @return OrgGroup|null
     */
    public function getMain(): ?OrgGroup
    {
        return $this->findOneBy(['isMain' => 1]);
    }

    public function add(OrgGroup $orgGroup): void
    {
        $this->em->persist($orgGroup);
    }

    public function updateMain(): void
    {
        $qb = $this->createQueryBuilder('d')
            ->update()
            ->set('d.isMain', 'false');
        $qb->getQuery()->execute();
    }
}
