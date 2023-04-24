<?php

namespace App\Model\Firm\Entity\OrgJob;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrgJob|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrgJob|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrgJob[]    findAll()
 * @method OrgJob[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrgJobRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, OrgJob::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return OrgJob
     */
    public function get(int $id): OrgJob
    {
        if (!$orgJob = $this->find($id)) {
            throw new EntityNotFoundException('Должности не найдено');
        }

        return $orgJob;
    }

    /**
     * @return OrgJob|null
     */
    public function getMain(): ?OrgJob
    {
        return $this->findOneBy(['isMain' => 1]);
    }

    public function add(OrgJob $orgJob): void
    {
        $this->em->persist($orgJob);
    }

    public function updateMain(): void
    {
        $qb = $this->createQueryBuilder('d')
            ->update()
            ->set('d.isMain', 'false');
        $qb->getQuery()->execute();
    }
}
