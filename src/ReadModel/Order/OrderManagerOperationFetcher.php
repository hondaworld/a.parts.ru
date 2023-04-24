<?php


namespace App\ReadModel\Order;


use App\Model\Order\Entity\ManagerOperation\ManagerOrderOperation;
use App\Model\User\Entity\User\User;
use App\ReadModel\Order\Filter\ManagerOperation\Filter;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class OrderManagerOperationFetcher
{
    private $connection;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;
    private PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ManagerOrderOperation ::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ManagerOrderOperation
    {
        return $this->repository->get($id);
    }

    /**
     * @param User $user
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     * @throws Exception
     */
    public function all(User $user, Filter $filter, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'b.id',
                'b.managerID',
                'm.name AS manager_name',
                "b.description",
                "b.orderID",
                'b.dateofadded',
                'b.number',
            )
            ->from('managerOrderOperations', 'b')
            ->leftJoin('b', 'managers', 'm', 'b.managerID = m.managerID')
            ->where('b.userID = :userID')
            ->setParameter('userID', $user->getId())
        ;

        if ($filter->managerID) {
            $qb->andWhere('b.managerID = :managerID');
            $qb->setParameter('managerID', $filter->managerID);
        }

        if ($filter->orderID) {
            $qb->andWhere('b.orderID = :orderID');
            $qb->setParameter('orderID', $filter->orderID);
        }

        if ($filter->number) {
            $qb->andWhere('b.number = :number');
            $qb->setParameter('number', $filter->number);
        }

        if ($filter->dateofadded) {
            if ($filter->dateofadded['date_from']) {
                $qb->andWhere($qb->expr()->gte('b.dateofadded', ':date_from'));
                $qb->setParameter('date_from', (new DateTime($filter->dateofadded['date_from']))->format('Y-m-d 00:00:00'));
            }
            if ($filter->dateofadded['date_till']) {
                $qb->andWhere($qb->expr()->lte('b.dateofadded', ':date_till'));
                $qb->setParameter('date_till', (new DateTime($filter->dateofadded['date_till']))->format('Y-m-d 23:59:59'));
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['dateofadded', 'number', 'orderID', 'manager_name'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}