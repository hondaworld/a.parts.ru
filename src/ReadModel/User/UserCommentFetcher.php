<?php


namespace App\ReadModel\User;


use App\Model\User\Entity\Comment\UserComment;
use App\Model\User\Entity\User\User;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class UserCommentFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(UserComment::class);
    }

    public function get(int $id): UserComment
    {
        return $this->repository->get($id);
    }

    /**
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function all(User $user): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('c.*', 'm.name AS manager_name')
            ->from('user_comments', 'c')
            ->leftJoin('c', 'managers', 'm', 'c.managerID = m.managerID')
            ->where('c.userID = :userID')
            ->setParameter('userID', $user->getId())
            ->orderBy('c.dateofadded', 'DESC')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}