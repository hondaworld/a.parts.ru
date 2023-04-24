<?php


namespace App\ReadModel\Work;


use App\Model\Card\Entity\Category\ZapCategory;
use App\Model\Card\Entity\Group\ZapGroup;
use App\Model\Work\Entity\Category\WorkCategory;
use App\Model\Work\Entity\Group\WorkGroup;
use App\ReadModel\Work\Filter\WorkGroup\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class WorkGroupFetcher
{
    private $connection;
    private $repository;
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'name';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 100;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(WorkGroup::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): WorkGroup
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('workGroupID, name')
            ->from('workGroup')
            ->orderBy('sort');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocTO(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('workGroupID, name')
            ->from('workGroup')
            ->where('isTO > 0')
            ->orderBy('sort');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    /**
     * @param Filter $filter
     * @param WorkCategory $workCategory
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function allByCategory(Filter $filter, WorkCategory $workCategory, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'workGroupID',
                'name',
                'norma',
                'isTO',
                'sort',
            )
            ->from('workGroup', 's')
            ->where('workCategoryID = :workCategoryID')
            ->setParameter('workCategoryID', $workCategory->getId())
        ;

        if ($filter->isTO !== null) {
            $qb->andWhere('s.isTO = :isTO');
            $qb->setParameter('isTO', $filter->isTO);
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['name', 'sort'], true)) {
            $sort = 'name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }


}