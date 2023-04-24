<?php


namespace App\ReadModel\Menu;


use App\Model\EntityNotFoundException;
use App\Model\Menu\Entity\Section\MenuSection;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class MenuSectionFetcher
{
    private $connection;
    private $sections;
    /**
     * @var PaginatorInterface
     */
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'sort';
    public const DEFAULT_SORT_DIRECTION = 'asc';

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->sections = $em->getRepository(MenuSection::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): MenuSection
    {

        if (!$section = $this->sections->find($id)) {
            throw new EntityNotFoundException('Секция меню не найдена');
        }

        return $section;
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'name'
            )
            ->from('menu_sections', 'g')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(int $groupID, int $parentID, ?string $sort, ?string $direction): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('s.*')
            ->from('menu_sections', 's')
            ->where('s.menu_group_id = :groupID AND s.parent_id = :parentID')
            ->setParameter('groupID', $groupID)
            ->setParameter('parentID', $parentID);

        $sort = $sort ?: self::DEFAULT_SORT_FIELD_NAME;
        $direction = $direction ?: self::DEFAULT_SORT_DIRECTION;

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, 1000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    public function allSortedWithKeyId(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                's.id',
                's.name',
                's.parent_id',
                's.menu_group_id',
                's.url',
                's.entity',
                's.sort'
            )
            ->from('menu_sections', 's')
            ->orderBy('sort', 'asc')
            ->executeQuery();

        return $qb->fetchAllAssociativeIndexed();
    }

    public function menu(): array
    {
        $sections = $this->connection->createQueryBuilder()
            ->select(
                's.id',
                's.name',
                's.parent_id',
                's.icon',
                's.url',
                's.entity',
                's.pattern',
                's.menu_group_id',
                's.sort',
                's.isHide'
            )
            ->from('menu_sections', 's')
            ->orderBy('sort', 'asc')
            ->executeQuery()
            ->fetchAllAssociative();

        $menu = [];
        foreach ($sections as $section) {
            $menu[$section['menu_group_id']][$section['id']] = $section;
        }

        return $menu;
    }
}