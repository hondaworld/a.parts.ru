<?php

namespace App\Model\Shop\Entity\DeleteReason;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DeleteReason|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeleteReason|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeleteReason[]    findAll()
 * @method DeleteReason[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeleteReasonRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, DeleteReason::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return DeleteReason
     */
    public function get(int $id): DeleteReason
    {
        if (!$deleteReason = $this->find($id)) {
            throw new EntityNotFoundException('Причина отказа не найдена');
        }

        return $deleteReason;
    }

    public function add(DeleteReason $deleteReason): void
    {
        $this->em->persist($deleteReason);
    }

    public function updateMain(): void
    {
        $qb = $this->createQueryBuilder('d')
            ->update()
            ->set('d.isMain', 'false');
        $qb->getQuery()->execute();
    }
}
