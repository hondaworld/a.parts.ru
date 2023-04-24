<?php


namespace App\ReadModel\User;


use App\Model\EntityNotFoundException;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\User\User;
use App\ReadModel\User\Filter\User\Filter;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class UserFetcher
{
    private Connection $connection;
    private PaginatorInterface $paginator;
    private $users;

    public const DEFAULT_SORT_FIELD_NAME = 'name';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->users = $em->getRepository(User::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): User
    {

        if (!$user = $this->users->find($id)) {
            throw new EntityNotFoundException('Клиент не найден');
        }

        return $user;
    }

    /**
     * @throws Exception
     */
    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('userID', 'name')
            ->from('users')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    /**
     * @throws Exception
     */
    public function assocOpt(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('userID', 'name')
            ->from('users')
            ->andWhere('optID <> 1')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocFromBalanceByProviderID($providerID)
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('u.userID', 'u.name')
            ->from('users', 'u')
            ->innerJoin('u', 'firmBalanceHistory', 'b', 'u.userID = b.userID')
            ->andWhere('b.providerID = :providerID')
            ->setParameter('providerID', $providerID)
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    /**
     * @param string $name
     * @return UserView[]|null
     * @throws Exception
     */
    public function findByName(string $name): ?array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'userID',
                'name',
                'firstname',
                'lastname',
                'middlename',
                'organization',
                'phonemob',
            )
            ->from('users')
            ->where('name like :name')
            ->orWhere('CONCAT(lastname, \' \', firstname) like :name')
            ->orWhere('organization like :name')
            ->orWhere('phonemob like :name')
            ->setParameter("name", '%' . $name . '%')
            ->orderBy('name')
            ->executeQuery();

//        $qb->setFetchMode(PDO::FETCH_CLASS, UserView::class);
        $arr = $qb->fetchAllAssociative();

        $result = [];
        if ($arr) {
            foreach ($arr as $items) {
                $userView = new UserView();
                foreach ($items as $name => $value) {
                    $userView->$name = $value;
                }
                $result[] = $userView;
            }
        }

        return $result ?: null;
    }

    /**
     * @param int $autoID
     * @return UserView[]|null
     * @throws Exception
     */
    public function findByAuto(int $autoID): ?array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'u.userID',
                'name',
                'firstname',
                'lastname',
                'middlename',
                'organization',
                'phonemob',
            )
            ->from('users', 'u')
            ->innerJoin('u', 'linkUserAuto', 'l', 'u.userID = l.userID')
            ->where('l.autoID = :autoID')
            ->setParameter("autoID", $autoID)
            ->orderBy('name')
            ->executeQuery();

//        $qb->setFetchMode(PDO::FETCH_CLASS, UserView::class);
        $arr = $qb->fetchAllAssociative();

        $result = [];
        if ($arr) {
            foreach ($arr as $items) {
                $userView = new UserView();
                foreach ($items as $name => $value) {
                    $userView->$name = $value;
                }
                $result[] = $userView;
            }
        }

        return $result ?: null;
    }

    /**
     * @param array $users
     * @return array
     * @throws Exception
     */
    public function findByIDsWithRegion(array $users): array
    {
        if (!$users) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'u.userID',
                'name',
                '(SELECT regionID FROM towns a INNER JOIN contacts b ON a.townID = b.townID WHERE b.userID = u.userID ORDER BY b.isMain DESC LIMIT 1) AS regionID'
            )
            ->from('users', 'u');
        $qb->andWhere($qb->expr()->in('u.userID', $users));
        return $qb->executeQuery()->fetchAllAssociativeIndexed();
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(Filter $filter, int $page, array $settings): ?PaginationInterface
    {
        if (!$filter->name && !$filter->userName && !$filter->phonemob && !$filter->ownerManagerID && !$filter->town && $filter->isOpt === null) return null;
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'DISTINCT u.userID',
                'TRIM(CONCAT(u.lastname, \' \', u.firstname)) AS user_name',
                'u.name',
                'u.phonemob',
                'u.email_send',
                'm.name AS manager',
                'o.name AS opt',
                'u.optID',
                'u.isUr',
                'u.isHide',
                'tr.name AS region',
                't.name AS town',
            )
            ->from('users', 'u')
            ->leftJoin('u', 'managers', 'm', 'u.ownerManagerID = m.managerID')
            ->leftJoin('u', 'contacts', 'c', 'u.userID = c.userID AND c.isMain = 1')
            ->leftJoin('c', 'towns', 't', 'c.townID = t.townID')
            ->leftJoin('t', 'townRegions', 'tr', 't.regionID = tr.regionID')
            ->innerJoin('u', 'opt', 'o', 'u.optID = o.optID');

        if ($filter->name) {
            $qb->andWhere($qb->expr()->like('u.name', ':name'));
            $qb->setParameter('name', '%' . mb_strtolower($filter->name) . '%');
        }

        if ($filter->userName) {
            $qb->andWhere($qb->expr()->like('TRIM(CONCAT(u.lastname, \' \', u.firstname))', ':userName'));
            $qb->setParameter('userName', '%' . mb_strtolower($filter->userName) . '%');
        }

        if ($filter->phonemob) {
            $qb->andWhere($qb->expr()->like('u.phonemob', ':phonemob'));
            $qb->setParameter('phonemob', '%' . preg_replace('/[^0-9+]/', '', $filter->phonemob) . '%');
        }

        if ($filter->ownerManagerID) {
            $qb->andWhere('u.ownerManagerID = :ownerManagerID');
            $qb->setParameter('ownerManagerID', $filter->ownerManagerID);
        }

        if ($filter->town) {
            $qb->andWhere($qb->expr()->like('CONCAT(tr.name, \' - \', t.name)', ':town'));
            $qb->setParameter('town', '%' . $filter->town . '%');
        }

        if ($filter->isOpt !== null && $filter->isOpt !== '') {
            if ($filter->isOpt)
                $qb->andWhere('u.optID <> :optID');
            else
                $qb->andWhere('u.optID = :optID');
            $qb->setParameter('optID', Opt::DEFAULT_OPT_ID);
        }

        if (!$filter->isShowHide) {
            $qb->andWhere('u.isHide = 0');
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['name', 'phonemob'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}