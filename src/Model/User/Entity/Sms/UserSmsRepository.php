<?php

namespace App\Model\User\Entity\Sms;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserSms|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserSms|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserSms[]    findAll()
 * @method UserSms[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserSmsRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, UserSms::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return UserSms
     */
    public function get(int $id): UserSms
    {
        if (!$userSms = $this->find($id)) {
            throw new EntityNotFoundException('Запись не найдена');
        }
        return $userSms;
    }

    public function add(UserSms $userSms): void
    {
        $this->em->persist($userSms);
    }
}
