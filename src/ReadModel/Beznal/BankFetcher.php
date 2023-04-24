<?php


namespace App\ReadModel\Beznal;


use App\ReadModel\Beznal\Filter\Bank\Filter;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class BankFetcher
{
    private $connection;
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'name';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
    }

    /**
     * @param string $name
     * @return BankView[]|null
     * @throws Exception
     */
    public function findBanksByBik(string $name): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'b.bankID',
                'b.bik',
                'b.name',
                'b.korschet',
                'b.address',
                'b.description'
            )
            ->from('banks', 'b')
            ->where('b.bik like :bik')
            ->setParameter('bik', '%' . $name . '%')
            ->orWhere('b.name like :bik')
            ->orderBy("bik")
            ->addOrderBy('name')
            ->executeQuery();

//        $stmt->setFetchMode(PDO::FETCH_CLASS, BankView::class);
        $arr = $stmt->fetchAllAssociative();

        $result = [];
        if ($arr) {
            foreach ($arr as $items) {
                $bankView = new BankView();
                foreach ($items as $name => $value) {
                    $bankView->$name = $value;
                }
                $result[] = $bankView;
            }
        }

        return $result ?: null;
    }

    /**
     * @param int $id
     * @return BankView|null
     * @throws Exception
     */
    public function findBankById(int $id): ?BankView
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'b.bankID',
                'b.bik',
                'b.name',
                'b.korschet',
                'b.address',
                'b.description'
            )
            ->from('banks', 'b')
            ->where('b.bankID = :id')
            ->setParameter('id', $id)
            ->executeQuery();

//        $stmt->setFetchMode(PDO::FETCH_CLASS, BankView::class);
        $result = $stmt->fetchAssociative();

        $bankView = new BankView();
        foreach ($result as $name => $value) {
            $bankView->$name = $value;
        }

        return $result ? $bankView : null;
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
            ->select('b.*')
            ->from('banks', 'b');

        if ($filter->bik) {
            $qb->andWhere($qb->expr()->like('b.bik', ':bik'));
            $qb->setParameter('bik', '%' . mb_strtolower($filter->bik) . '%');
        }

        if ($filter->name) {
            $qb->andWhere($qb->expr()->like('b.name', ':name'));
            $qb->setParameter('name', '%' . mb_strtolower($filter->name) . '%');
        }

        if ($filter->address) {
            $qb->andWhere($qb->expr()->like('b.address', ':address'));
            $qb->setParameter('address', '%' . mb_strtolower($filter->address) . '%');
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['name', 'bik', 'korschet', 'address'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = 'name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

}