<?php


namespace App\ReadModel\Income;


use App\Model\Income\Entity\Order\IncomeOrder;
use App\Model\Provider\Entity\Provider\Provider;
use App\ReadModel\Income\Filter\IncomeOrder\Filter;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class IncomeOrderFetcher
{
    private $connection;
    private $repository;
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(IncomeOrder::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): IncomeOrder
    {
        return $this->repository->get($id);
    }

    public function findByIds(array $arIds): array
    {
        if (!$arIds) return [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'incomeOrderID',
                'document_num',
                'dateofadded',
            )
            ->from('incomeOrders')
            ->where('incomeOrderID in (' . implode(',', $arIds) . ')');

        return $qb->executeQuery()->fetchAllAssociativeIndexed();
    }

    /**
     * @param Provider $provider
     * @return int
     * @throws Exception
     */
    public function getNextDocumentNumber(Provider $provider): int
    {
        $documentNumber = $this->connection->createQueryBuilder()
            ->select(
                'IfNull(Max(document_num), 0)',
            )
            ->from('incomeOrders', 'a')
            ->andWhere('a.providerID = :providerID')
            ->setParameter('providerID', $provider->getId())
            ->executeQuery()
            ->fetchOne()
        ;
        if ($documentNumber == 0) {
            $documentNumber = $provider->getIncomeOrderNumber();
            if ($documentNumber < 1) $documentNumber = 1;
        } else {
            $documentNumber++;
        }
        return $documentNumber;
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     * @throws \Exception
     */
    public function all(Filter $filter, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'io.incomeOrderID',
                'io.providerID',
                'p.name AS provider',
                "io.zapSkladID",
                'io.dateofadded',
                'io.document_num',
                'io.isOrdered',
                'zs.name_short AS sklad',
            )
            ->from('incomeOrders', 'io')
            ->leftJoin('io', 'zapSklad', 'zs', 'io.zapSkladID = zs.zapSkladID')
            ->leftJoin('io', 'providers', 'p', 'io.providerID = p.providerID')
        ;

        if ($filter->providerID) {
            $qb->andWhere('io.providerID = :providerID');
            $qb->setParameter('providerID', $filter->providerID);
        }

        if ($filter->zapSkladID) {
            $qb->andWhere('io.zapSkladID = :zapSkladID');
            $qb->setParameter('zapSkladID', $filter->zapSkladID);
        }

        if ($filter->document_num) {
            $qb->andWhere('io.document_num = :document_num');
            $qb->setParameter('document_num', intval($filter->document_num));
        }

        if ($filter->dateofadded) {
            if ($filter->dateofadded['date_from']) {
                $qb->andWhere($qb->expr()->gte('io.dateofadded', ':date_from'));
                $qb->setParameter('date_from', (new \DateTime($filter->dateofadded['date_from']))->format('Y-m-d 00:00:00'));
            }
            if ($filter->dateofadded['date_till']) {
                $qb->andWhere($qb->expr()->lte('io.dateofadded', ':date_till'));
                $qb->setParameter('date_till', (new \DateTime($filter->dateofadded['date_till']))->format('Y-m-d 23:59:59'));
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['dateofadded', 'document_num', 'provider', 'sklad'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}