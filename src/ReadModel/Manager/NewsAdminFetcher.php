<?php


namespace App\ReadModel\Manager;


use App\Model\EntityNotFoundException;
use App\Model\Manager\Entity\NewsAdmin\NewsAdmin;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class NewsAdminFetcher
{
    private Connection $connection;
    private PaginatorInterface $paginator;
    private $firms;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->firms = $em->getRepository(NewsAdmin::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): NewsAdmin
    {
        if (!$newsAdmin = $this->firms->find($id)) {
            throw new EntityNotFoundException('Новость не найдена');
        }

        return $newsAdmin;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function active(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'n.newsID',
                'n.dateofadded',
                'n.name',
                'n.type',
                'n.description'
            )
            ->from('news_admin', 'n')
            ->andWhere('isHide = 0')
            ->orderBy('dateofadded', 'desc');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'n.newsID',
                'n.dateofadded',
                'n.name',
                'n.type',
                'n.isHide',
            )
            ->from('news_admin', 'n');

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['dateofadded', 'name'], true)) {
            $sort = self::DEFAULT_SORT_FIELD_NAME;
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}