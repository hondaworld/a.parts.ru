<?php


namespace App\ReadModel\Provider;


use App\Model\EntityNotFoundException;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Provider\Entity\Provider\Provider;
use App\ReadModel\Provider\Filter\Provider\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class ProviderFetcher
{
    private $connection;
    private $paginator;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'p.name';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Provider::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): Provider
    {

        if (!$provider = $this->repository->find($id)) {
            throw new EntityNotFoundException('Поставщик не найден');
        }

        return $provider;
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('providerID', 'name')
            ->from('providers')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocOrderedByHide(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('providerID', "if (isHide, CONCAT(name, ' - скрыт'), name)")
            ->from('providers')
            ->orderBy('isHide')
            ->addOrderBy('name')
        ;

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocIncomeInWarehouse(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('providerID', 'name')
            ->from('providers')
            ->where('providerID IN (SELECT providerID FROM providerPrices aa INNER JOIN income bb ON aa.providerPriceID = bb.providerPriceID WHERE bb.status = :status)')
            ->setParameter('status', IncomeStatus::INCOME_IN_WAREHOUSE)
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function allExisting(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('providerID', 'name', 'isHide')
            ->from('providers')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllAssociativeIndexed();
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
                'p.providerID',
                'p.userID',
                'p.name',
                'u.name AS user_name',
                'p.koef_dealer',
                'p.isDealer',
                'p.isHide',
            )
            ->from('providers', 'p')
            ->innerJoin('p', 'users', 'u', 'p.userID = u.userID')
            ;

        if ($filter->name) {
            $qb->andWhere($qb->expr()->like('p.name', ':name'));
            $qb->setParameter('name', '%' . mb_strtolower($filter->name) . '%');
        }

        if ($filter->showHide !== null && $filter->showHide !== '') {
            if (!$filter->showHide)
                $qb->andWhere('p.isHide = false');
        }

        $sort = isset($settings['sort']) ? $settings['sort'] : self::DEFAULT_SORT_FIELD_NAME;
        $direction = isset($settings['direction']) ? $settings['direction'] : self::DEFAULT_SORT_DIRECTION;
        $size = isset($settings['inPage']) ? $settings['inPage'] : self::PER_PAGE;

        if (!in_array($sort, ['p.name', 'user_name'], true)) {
            $sort = 'p.name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}