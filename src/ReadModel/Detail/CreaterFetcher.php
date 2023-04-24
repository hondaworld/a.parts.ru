<?php


namespace App\ReadModel\Detail;


use App\Model\Detail\Entity\Creater\Creater;
use App\Model\EntityNotFoundException;
use App\ReadModel\Detail\Filter\Creater\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class CreaterFetcher
{
    private $connection;
    private $paginator;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'name';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 50;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Creater::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): Creater
    {

        if (!$creater = $this->repository->find($id)) {
            throw new EntityNotFoundException('Производитель не найден');
        }

        return $creater;
    }

    public function allArray(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('createrID, name, alt_names, isOriginal, tableName')
            ->from('creaters')
            ->orderBy('isOriginal', 'DESC')
            ->addOrderBy('name');

        return $qb->executeQuery()->fetchAllAssociativeIndexed();
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('createrID, name')
            ->from('creaters')
            ->orderBy('isOriginal', 'DESC')
            ->addOrderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocAlternatives(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('createrID, creater_weightID')
            ->from('creaters')
            ->where('creater_weightID <> 0');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocTableNames(): array
    {
        $tableNames = [];
        for ($i = 1; $i <= 10; $i++) {
            $tableNames['shopPrice' . $i] = 'shopPrice' . $i;
        }
        $tableNames['shopPriceN'] = 'shopPriceN';
        return $tableNames;
    }

    public function getTableNamesAndOriginal(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('tableName, isOriginal')
            ->from('creaters')
            ->where("tableName <> ''")
            ->groupBy('tableName, isOriginal');

        return $qb->executeQuery()->fetchAllAssociative();
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
                'c.createrID',
                'c.creater_weightID',
                'c.name',
                'c.name_rus',
                'c.isOriginal',
                'c.tableName',
                'c.alt_names',
                'c.photo',
                'c.isHide',
                'a.name AS alt_name'
            )
            ->from('creaters', 'c')
            ->leftJoin('c', 'creaters', 'a', 'c.creater_weightID = a.createrID');

        if ($filter->name) {
            $qb->andWhere($qb->expr()->like('c.name', ':name'));
            $qb->setParameter('name', '%' . mb_strtolower($filter->name) . '%');
        }

        if ($filter->isOriginal !== null && $filter->isOriginal !== '') {
            $qb->andWhere('c.isOriginal = :isOriginal');
            $qb->setParameter('isOriginal', $filter->isOriginal);
        }

        if ($filter->tableName) {
            $qb->andWhere('c.tableName = :tableName');
            $qb->setParameter('tableName', $filter->tableName == 'shopPriceN' ? '' : $filter->tableName);
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['name', 'name_rus', 'isOriginal', 'tableName'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}