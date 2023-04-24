<?php


namespace App\ReadModel\Order;


use App\Model\Expense\Entity\Shipping\Shipping;
use App\Model\User\Entity\User\User;
use App\ReadModel\Order\Filter\Shippings\Filter;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class OrderShippingFetcher
{
    private Connection $connection;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;
    private PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Shipping ::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): Shipping
    {
        return $this->repository->get($id);
    }

    /**
     * @param User $user
     * @return Shipping[]
     */
    private function allByUser(User $user): array
    {
        return $this->repository->createQueryBuilder('s')
            ->select('s', 'e', 'st')
            ->innerJoin('s.expenseDocument', 'e')
            ->innerJoin('s.status', 'st')
            ->andWhere('s.user = :user')
            ->setParameter('user', $user)
//            ->orderBy('s.dateofadded', 'desc')
            ->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     * @throws Exception
     */
    public function all(User $user, int $page, array $settings): PaginationInterface
    {
        $arrShipping = $this->allByUser($user);

        $result = $this->getResult($arrShipping);

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['dateofadded', 'gruz_user_name', 'gruz_user_town', 'gruz_firm_name', 'pay_type_name', 'delivery_tk', 'tracknumber', 'status_name'], true)) {
            $sort = 'dateofadded';
        }

//        dump($result);
        $result = $this->sortResult($result, $sort, $direction);
//        dump($result);

        return $this->paginator->paginate($result, $page, $size, [
            'defaultSortFieldName' => $sort,
            'defaultSortDirection' => $direction
        ]);
    }

    /**
     * @param Filter $filter
     * @return Shipping[]
     * @throws Exception
     */
    private function allByFilter(Filter $filter): array
    {
        $qb = $this->repository->createQueryBuilder('s')
            ->select('s', 'e', 'st', 'u')
            ->innerJoin('s.expenseDocument', 'e')
            ->innerJoin('s.user', 'u')
            ->innerJoin('s.status', 'st');

        if ($filter->dateofadded) {
            if ($filter->dateofadded['date_from']) {
                $dateFrom = new DateTime($filter->dateofadded['date_from']);
                $qb->andWhere($qb->expr()->gte('s.dateofadded', ':date_from'));
                $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
            }

            if ($filter->dateofadded['date_till']) {
                $dateTill = new DateTime($filter->dateofadded['date_till']);
                $qb->andWhere($qb->expr()->lte('s.dateofadded', ':date_till'));
                $qb->setParameter('date_till', $dateTill->format('Y-m-d 23:59:59'));
            }
        }

        if ($filter->user_name) {
            $qb->andWhere($qb->expr()->like('u.name', ':user_name'));
            $qb->setParameter('user_name', '%' . $filter->user_name . '%');
        }

        if ($filter->tracknumber) {
            $qb->andWhere($qb->expr()->like('s.tracknumber', ':tracknumber'));
            $qb->setParameter('tracknumber', '%' . $filter->tracknumber . '%');
        }

        if ($filter->status) {
            $qb->andWhere('s.status = :status');
            $qb->setParameter('status', $filter->status);
        }

        if ($filter->delivery_tkID) {
            $qb->andWhere('s.delivery_tk = :delivery_tk');
            $qb->setParameter('delivery_tk', $filter->delivery_tkID);
        }

        if ($filter->pay_type_name) {
            $qb->andWhere('s.pay_type = :pay_type');
            $qb->setParameter('pay_type', $filter->pay_type_name);
        }

        if ($filter->gruz_firm_name) {
            $qb->andWhere($qb->expr()->orX('e.exp_firm = :firmID', 'e.gruz_firm = :firmID'));
            $qb->setParameter('firmID', $filter->gruz_firm_name);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     * @throws Exception
     */
    public function allFilter(Filter $filter, int $page, array $settings): PaginationInterface
    {
        $arrShipping = $this->allByFilter($filter);

        $result = $this->getResult($arrShipping);

        foreach ($result as $k => $shippingView) {
            if ($filter->gruz_user_town) {
                if (!$shippingView->isEqualGruzUserTown($filter->gruz_user_town)) {
                    unset($result[$k]);
                }
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['user_name', 'dateofadded', 'gruz_user_name', 'gruz_user_town', 'gruz_firm_name', 'pay_type_name', 'delivery_tk', 'tracknumber', 'status_name'], true)) {
            $sort = 'dateofadded';
        }

//        dump($result);
        $result = $this->sortResult($result, $sort, $direction);
//        dump($result);

        return $this->paginator->paginate($result, $page, $size, [
            'defaultSortFieldName' => $sort,
            'defaultSortDirection' => $direction
        ]);
    }

    /**
     * @param Shipping[] $arrShipping
     * @return ShippingView[]
     */
    public function getResult(array $arrShipping): array
    {
        $result = [];
        foreach ($arrShipping as $shipping) {
            $shippingView = new ShippingView($shipping);

            $result[] = $shippingView;
        }
        return $result;
    }

    /**
     * @param ShippingView[] $result
     * @param string $sort
     * @param string $direction
     * @return ShippingView[]
     */
    public function sortResult(array $result, string $sort, string $direction): array
    {
        usort($result, function ($a, $b) use ($sort, $direction) {
            if ($direction == 'asc') {
                return $a->$sort <=> $b->$sort;
            } else {
                return $b->$sort <=> $a->$sort;
            }
        });
        return $result;
    }
}