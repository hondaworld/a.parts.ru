<?php

namespace App\Model\Firm\Entity\ManagerFirm;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ManagerFirm|null find($id, $lockMode = null, $lockVersion = null)
 * @method ManagerFirm|null findOneBy(array $criteria, array $orderBy = null)
 * @method ManagerFirm[]    findAll()
 * @method ManagerFirm[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ManagerFirmRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ManagerFirm::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ManagerFirm
     */
    public function get(int $id): ManagerFirm
    {
        if (!$managerFirm = $this->find($id)) {
            throw new EntityNotFoundException('Привязка не найдена');
        }

        return $managerFirm;
    }

    public function add(ManagerFirm $managerFirm): void
    {
        $this->em->persist($managerFirm);
    }
}
