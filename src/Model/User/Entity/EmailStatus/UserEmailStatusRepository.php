<?php

namespace App\Model\User\Entity\EmailStatus;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserEmailStatus|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserEmailStatus|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserEmailStatus[]    findAll()
 * @method UserEmailStatus[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserEmailStatusRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, UserEmailStatus::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return UserEmailStatus
     * @throws EntityNotFoundException
     */
    public function get(int $id): UserEmailStatus
    {
        if (!$userEmailStatus = $this->find($id)) {
            throw new EntityNotFoundException('E-mail статус не найден');
        }

        return $userEmailStatus;
    }

    public function add(UserEmailStatus $userEmailStatus): void
    {
        $this->em->persist($userEmailStatus);
    }
}
