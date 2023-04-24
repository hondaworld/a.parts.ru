<?php


namespace App\ReadModel\Menu;


use App\Model\EntityNotFoundException;
use App\Model\Menu\Entity\Group\MenuGroup;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class MenuGroupFetcher
{
    private $connection;
    private $groups;
    /**
     * @var PaginatorInterface
     */
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'sort';
    public const DEFAULT_SORT_DIRECTION = 'asc';

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->groups = $em->getRepository(MenuGroup::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): MenuGroup
    {

        if (!$group = $this->groups->find($id)) {
            throw new EntityNotFoundException('Группа меню не найдена');
        }

        return $group;
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'name'
            )
            ->from('menu_groups', 'g')
            ->orderBy('sort')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function menu(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'name',
                'icon'
            )
            ->from('menu_groups', 'g')
            ->where('isHide = 0')
            ->orderBy('sort')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    public function all(?string $sort, ?string $direction): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'g.id',
                'g.name',
                'g.icon',
                'g.sort',
                'g.isHide'
            )
            ->from('menu_groups', 'g');

        $sort = $sort ?: self::DEFAULT_SORT_FIELD_NAME;
        $direction = $direction ?: self::DEFAULT_SORT_DIRECTION;

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, 1000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}