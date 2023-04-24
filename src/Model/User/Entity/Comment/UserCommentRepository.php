<?php

namespace App\Model\User\Entity\Comment;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method UserComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method UserComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method UserComment[]    findAll()
 * @method UserComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserCommentRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, UserComment::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return UserComment
     */
    public function get(int $id): UserComment
    {
        if (!$userComment = $this->find($id)) {
            throw new EntityNotFoundException('Запись не найдена');
        }
        return $userComment;
    }

    public function add(UserComment $userComment): void
    {
        $this->em->persist($userComment);
    }
}
