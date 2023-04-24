<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\ReadModel\Card\Filter\ZapCard\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ZapCardFetcher
{
    private $connection;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'zc.number';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapCard::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ZapCard
    {
        return $this->repository->get($id);
    }

    public function findUniqueNumbers(string $number): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('number',)
            ->from('zapCards')
            ->where('number like :number')
            ->setParameter("number", $number . '%')
            ->orderBy('number')
            ->groupBy('number')
            ->executeQuery();

//        $qb->setFetchMode(\PDO::FETCH_COLUMN, 0);
        return $qb->fetchFirstColumn();
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(Filter $filter, int $page, array $settings): ?PaginationInterface
    {
        if (!$filter->shop_typeID && !$filter->zapGroupID && !$filter->createrID && !$filter->number && !$filter->auto_modelID && !$filter->year && $filter->managerID === null) return null;
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'zc.zapCardID',
                'zc.zapGroupID',
                'zc.shop_typeID',
                'zc.createrID',
                'zc.number',
                'zc.price',
                'zc.isDeleted',
                'zg.name AS group_name',
                'st.name AS shop_type_name',
                'c.name AS creater',
                'm.nick AS manager_nick',
            )
            ->from('zapCards', 'zc')
            ->leftJoin('zc', 'zapGroup', 'zg', 'zc.zapGroupID = zg.zapGroupID')
            ->leftJoin('zc', 'managers', 'm', 'm.managerID = zc.managerID')
            ->innerJoin('zc', 'shop_types', 'st', 'zc.shop_typeID = st.shop_typeID')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID');

        if ($filter->shop_typeID) {
            $qb->andWhere('zc.shop_typeID = :shop_typeID');
            $qb->setParameter('shop_typeID', $filter->shop_typeID);
        }

        if ($filter->managerID !== '' && $filter->managerID !== null) {
            if ($filter->managerID == 0) {
                $qb->andWhere('zc.managerID IS NULL');
            } else {
                $qb->andWhere('zc.managerID = :managerID');
                $qb->setParameter('managerID', $filter->managerID);
            }
        }

        if ($filter->zapGroupID) {
            $qb->andWhere('zc.zapGroupID = :zapGroupID');
            $qb->setParameter('zapGroupID', $filter->zapGroupID);
        }

        if ($filter->createrID) {
            $qb->andWhere('zc.createrID = :createrID');
            $qb->setParameter('createrID', $filter->createrID);
        }

        if ($filter->number) {
            $number = (new DetailNumber($filter->number))->getValue();
            if ($filter->searchWholeNumber) {
                $qb->andWhere('zc.number = :number');
                $qb->setParameter('number', $number);
            } else {
                $qb->andWhere($qb->expr()->like('zc.number', ':number'));
                $qb->setParameter('number', '%' . $number . '%');
            }
        }

        if ($filter->auto_modelID || $filter->year) {
            $qb->innerJoin('zc', 'zapCard_auto', 'zca', 'zc.zapCardID = zca.zapCardID');

            if ($filter->auto_modelID) {
                $qb->andWhere('zca.auto_modelID = :auto_modelID');
                $qb->setParameter('auto_modelID', $filter->auto_modelID);
            }

            if ($filter->year) {
                $qb->andWhere('zca.year = :year');
                $qb->setParameter('year', $filter->year);
            }
        }

        if ($filter->showDeleted !== null && $filter->showDeleted !== '') {
            if (!$filter->showDeleted)
                $qb->andWhere('zc.isDeleted = false');
        } else {
            $qb->andWhere('zc.isDeleted = false');
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['group_name', 'zc.number', 'creater_name', 'shop_type_name', 'manager_nick'], true)) {
            $sort = 'zc.number';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}