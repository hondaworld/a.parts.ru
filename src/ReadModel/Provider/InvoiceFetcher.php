<?php


namespace App\ReadModel\Provider;


use App\ReadModel\Provider\Filter\Invoice\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class InvoiceFetcher
{
    private $connection;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;
    private PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(Filter $filter, int $page, array $settings): PaginationInterface
    {

//        SELECT a.*, (a.isDone OR (
//        SELECT Count(*)
//	FROM logInvoice aa
//	LEFT JOIN income cc ON aa.incomeID = cc.incomeID
//	WHERE logInvoiceAllID = a.logInvoiceAllID AND (aa.incomeID = 0 OR cc.priceZak <> aa.priceInvoice)
//) = 0) AS colorID
//FROM logInvoiceAll a
//INNER JOIN providerInvoices b ON a.providerInvoiceID = b.providerInvoiceID";

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ia.logInvoiceAllID',
                'ia.providerInvoiceID',
                'ia.providerName',
                'ia.dateofadded',
                '(ia.isDone OR (
                    SELECT Count(*)
	                FROM logInvoice aa
	                LEFT JOIN income cc ON aa.incomeID = cc.incomeID
	                WHERE logInvoiceAllID = ia.logInvoiceAllID AND (aa.incomeID IS NULL OR cc.priceZak <> aa.priceInvoice)
                    ) = 0) AS isDone'
            )
            ->from('logInvoiceAll', 'ia')
            ->innerJoin('ia', 'providerInvoices', 'pi', 'ia.providerInvoiceID = pi.providerInvoiceID')
            ->andWhere('ia.dateofadded > :dateofadded')
            ->setParameter('dateofadded', (new \DateTime('-3 days'))->format('Y-m-d'));

        if ($filter->providerID) {
            $qb->andWhere('pi.providerID = :providerID');
            $qb->setParameter('providerID', $filter->providerID);
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['providerName', 'dateofadded'], true)) {
            $sort = self::DEFAULT_SORT_FIELD_NAME;
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

}