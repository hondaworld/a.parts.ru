<?php


namespace App\ReadModel\Manager;


use App\Model\EntityNotFoundException;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Manager\Manager;
use App\ReadModel\Manager\Filter\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class ManagerFetcher
{
    private $connection;
    private $paginator;
    private $managers;

    public const DEFAULT_SORT_FIELD_NAME = 'name';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 5;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->managers = $em->getRepository(Manager::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): Manager
    {

        if (!$manager = $this->managers->find($id)) {
            throw new EntityNotFoundException('Менеджер не найден');
        }

        return $manager;
    }

    public function setSettings(int $id, string $settings): void
    {
        $sql = "UPDATE managers SET settings_admin = :settings_admin WHERE managerID = :managerID";
        $statement = $this->connection->prepare($sql);
        $statement->bindValue('settings_admin', $settings);
        $statement->bindValue('managerID', $id);
        $statement->executeQuery();
    }

    public function findForAuthByLogin(string $login): ?AuthView
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'managerID',
                'login',
                'password_admin',
                'firstname',
                'name',
                'photo',
                'isHide',
                'isManager',
                'isAdmin',
                'settings_admin'
            )
            ->from('managers')
            ->where('login = :login')
            ->setParameter('login', $login)
            ->executeQuery()
        ;

//        $stmt->setFetchMode(PDO::FETCH_CLASS, AuthView::class);
        $result = $stmt->fetchAssociative();
        $authView = new AuthView();

        if ($result) {
            foreach ($result as $name => $value) {
                $authView->$name = $value;
            }
            $stmt = $this->connection->createQueryBuilder()
                ->select(
                    's.id',
                    's.name',
                    's.entity',
                    's.pattern',
                    'a.name AS action',
                )
                ->addSelect('ifnull((SELECT 1 FROM linkMenuActionManagerGroup la INNER JOIN linkManagerGroup lg ON la.manager_group_id = lg.manager_group_id WHERE lg.manager_id = :managerID AND la.menu_action_id = a.id LIMIT 1), 0) AS active')
                ->from('menu_sections', 's')
                ->innerJoin('s', 'menu_actions', 'a', 's.id = a.menu_section_id')
                ->setParameter('managerID', $authView->managerID)
                ->executeQuery();

            $arr = $stmt->fetchAllAssociative();

            $sections = [];
            if ($arr) {
                foreach ($arr as $item) {

                    if (!isset($sections[$item['id']])) {
                        $sections[$item['id']] = [
                            'id' => $item['id'],
                            'name' => $item['name'],
                            'entity' => $item['entity'],
                            'pattern' => $item['pattern'],
                            'actions' => []
                        ];
                    }
                    $sections[$item['id']]['actions'][$item['action']] = $item['active'];
                }
            }
            $authView->sections = $sections;
        }

        return $result ? $authView : null;
    }

    public function assoc($onlyManagers = false): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('managerID, name')
            ->from('managers')
            ->orderBy('name');

        if ($onlyManagers) {
            $qb->andWhere('isManager = 1 OR isAdmin = 1');
        }

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocNicks($onlyManagers = false): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('managerID, nick')
            ->from('managers')
            ->orderBy('name');

        if ($onlyManagers) {
            $qb->andWhere('isManager = 1 OR isAdmin = 1');
        }

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocByIds(array $managerID): array
    {
        if (!$managerID) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select('managerID, name')
            ->from('managers')
            ->orderBy('name')
        ;

        $qb->andWhere($qb->expr()->in('managerID', $managerID));

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocByFirm(Firm $firm): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('managerID, name')
            ->from('managers')
            ->where('managerID NOT IN (SELECT managerID FROM linkManagerFirm WHERE firmID = :firmID)')
            ->setParameter('firmID', $firm->getId())
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(Filter $filter, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'managerID',
                'name',
                'nick',
                'TRIM(CONCAT(lastname, \' \', firstname)) AS user_name',
                'login',
                'phonemob',
                'email',
                'isHide',
                'isManager',
                'isAdmin'
            )
            ->from('managers');

        if ($filter->name) {
            $qb->andWhere($qb->expr()->like('name', ':name'));
            $qb->setParameter('name', '%' . mb_strtolower($filter->name) . '%');
        }

        if ($filter->user_name) {
            $qb->andWhere($qb->expr()->like('CONCAT(firstname, \' \', lastname)', ':user_name'));
            $qb->setParameter('user_name', '%' . mb_strtolower($filter->user_name) . '%');
        }

        if ($filter->login) {
            $qb->andWhere($qb->expr()->like('login', ':login'));
            $qb->setParameter('login', mb_strtolower($filter->login) . '%');
        }

        if ($filter->email) {
            $qb->andWhere($qb->expr()->like('LOWER(email)', ':email'));
            $qb->setParameter('email', '%' . mb_strtolower($filter->email) . '%');
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['name', 'user_name', 'email', 'phonemob', 'isAdmin'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}