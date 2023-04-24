<?php


namespace App\ReadModel\User;


use App\Model\EntityNotFoundException;
use App\Model\User\Entity\BalanceHistory\UserBalanceHistory;
use App\Model\User\Entity\Sms\UserSms;
use App\Model\User\Entity\User\User;
use App\ReadModel\User\Filter\UserBalanceHistory\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class UserSmsFetcher
{
    private $connection;
    private $paginator;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(UserSms::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): UserSms
    {

        if (!$userBalanceHistory = $this->repository->find($id)) {
            throw new EntityNotFoundException('Запись не найдена');
        }

        return $userBalanceHistory;
    }

    /**
     * @param User $user
     * @param int $page
     * @return PaginationInterface
     * @throws \Exception
     */
    public function all(User $user, int $page): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                's.id',
                's.managerID',
                's.dateofadded',
                'm.name AS manager_name',
                "s.status_code",
                "s.status_text",
                "s.sms_id",
                "s.phonemob",
                "s.sender",
                "s.text",
            )
            ->from('user_sms', 's')
            ->leftJoin('s', 'managers', 'm', 's.managerID = m.managerID')
            ->where('s.userID = :userID')
            ->setParameter('userID', $user->getId())
            ;

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['dateofadded'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}