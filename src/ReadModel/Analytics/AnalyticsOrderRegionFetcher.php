<?php


namespace App\ReadModel\Analytics;


use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Analytics\Filter\OrderRegion\Filter;
use DateTime;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class AnalyticsOrderRegionFetcher
{
    public const DEFAULT_SORT_FIELD_NAME = 'provider_name';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    private Connection $connection;
    private PaginatorInterface $paginator;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
    }

    /**
     * @param Filter $filter
     * @param array $settings
     * @return PaginationInterface|null
     * @throws Exception
     */
    public function all(Filter $filter, array $settings): ?PaginationInterface
    {
        if (!$filter->dateofreport || !$filter->dateofreport['date_from'] || !$filter->dateofreport['date_till'] || !$filter->userID) return null;

        $dateFrom = new DateTime($filter->dateofreport['date_from']);
        $dateTill = new DateTime($filter->dateofreport['date_till']);

        //        SELECT SUM((ROUND(a.price-a.price*a.discount/100)) * c.quantity) AS summ_in, SUM(d.price * c.quantity) AS summ_out, SUM((ROUND(a.price-a.price*a.discount/100) - d.price) * c.quantity) AS summ, SUM(c.quantity) AS quantity, '' AS provider, d.providerPriceID, d.incomeID
//		FROM order_goods a
//		INNER JOIN orders b ON a.orderID = b.orderID
//		INNER JOIN expense c ON a.goodID = c.goodID
//		INNER JOIN income d ON c.incomeID = d.incomeID
//		INNER JOIN zapCards e ON d.zapCardID = e.zapCardID
//		INNER JOIN expenseDocuments h ON a.expenseDocumentID = h.expenseDocumentID
//		WHERE a.expenseDocumentID <> 0 AND d.price > 0 AND a.number <> '15400PLMA03' $where
//		GROUP BY d.incomeID

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'SUM(ROUND(og.price - og.price * og.discount / 100) * e.quantity) AS income',
                'SUM((ROUND(og.price - og.price * og.discount / 100) - i.price) * e.quantity) AS profit',
                'SUM(e.quantity) AS quantity',
                'p.providerID',
                'p.name AS provider_name'
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->innerJoin('e', 'income', 'i', 'e.incomeID = i.incomeID')
            ->innerJoin('i', 'providerPrices', 'pp', 'i.providerPriceID = pp.providerPriceID')
            ->innerJoin('pp', 'providers', 'p', 'pp.providerID = p.providerID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'og.expenseDocumentID = ed.expenseDocumentID')
            ->andWhere('i.price > 0')
            ->andWhere('ed.expenseDocumentID IS NOT NULL')
            ->andWhere("og.number <> '15400PLMA03'")
            ->groupBy('p.providerID')
        ;

        $qb->andWhere($qb->expr()->gte('ed.dateofadded', ':date_from'));
        $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
        $qb->andWhere($qb->expr()->lte('ed.dateofadded', ':date_till'));
        $qb->setParameter('date_till', $dateTill->format('Y-m-d 23:59:59'));
        $qb->andWhere('o.userID = :userID');
        $qb->setParameter('userID', $filter->userID);


        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['provider_name', 'income', 'profit', 'quantity'], true)) {
            $sort = 'provider_name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, 10000000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param Filter $filter
     * @param Provider $provider
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function numbers(Filter $filter, Provider $provider): array
    {
        if (!$filter->dateofreport || !$filter->dateofreport['date_from'] || !$filter->dateofreport['date_till'] || !$filter->userID) return [];

        $dateFrom = new DateTime($filter->dateofreport['date_from']);
        $dateTill = new DateTime($filter->dateofreport['date_till']);

        //        SELECT SUM((ROUND(a.price-a.price*a.discount/100)) * c.quantity) AS summ_in, SUM(d.price * c.quantity) AS summ_out, SUM((ROUND(a.price-a.price*a.discount/100) - d.price) * c.quantity) AS summ, SUM(c.quantity) AS quantity, '' AS provider, d.providerPriceID, d.incomeID
//		FROM order_goods a
//		INNER JOIN orders b ON a.orderID = b.orderID
//		INNER JOIN expense c ON a.goodID = c.goodID
//		INNER JOIN income d ON c.incomeID = d.incomeID
//		INNER JOIN zapCards e ON d.zapCardID = e.zapCardID
//		INNER JOIN expenseDocuments h ON a.expenseDocumentID = h.expenseDocumentID
//		WHERE a.expenseDocumentID <> 0 AND d.price > 0 AND a.number <> '15400PLMA03' $where
//		GROUP BY d.incomeID

        $qb = $this->connection->createQueryBuilder()
            ->select(
                'SUM(ROUND(og.price - og.price * og.discount / 100) * e.quantity) AS income',
                'SUM((ROUND(og.price - og.price * og.discount / 100) - i.price) * e.quantity) AS profit',
                'SUM(e.quantity) AS quantity',
                'og.number',
                'c.name AS creater_name'
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'creaters', 'c', 'og.createrID = c.createrID')
            ->innerJoin('og', 'orders', 'o', 'og.orderID = o.orderID')
            ->innerJoin('og', 'expense', 'e', 'og.goodID = e.goodID')
            ->innerJoin('e', 'income', 'i', 'e.incomeID = i.incomeID')
            ->innerJoin('i', 'providerPrices', 'pp', 'i.providerPriceID = pp.providerPriceID')
            ->innerJoin('pp', 'providers', 'p', 'pp.providerID = p.providerID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'og.expenseDocumentID = ed.expenseDocumentID')
            ->andWhere('i.price > 0')
            ->andWhere('ed.expenseDocumentID IS NOT NULL')
            ->andWhere("og.number <> '15400PLMA03'")
            ->groupBy('og.number, og.createrID')
        ;

        $qb->andWhere($qb->expr()->gte('ed.dateofadded', ':date_from'));
        $qb->setParameter('date_from', $dateFrom->format('Y-m-d 00:00:00'));
        $qb->andWhere($qb->expr()->lte('ed.dateofadded', ':date_till'));
        $qb->setParameter('date_till', $dateTill->format('Y-m-d 23:59:59'));
        $qb->andWhere('o.userID = :userID');
        $qb->setParameter('userID', $filter->userID);
        $qb->andWhere('p.providerID = :providerID');
        $qb->setParameter('providerID', $provider->getId());

        $qb->orderBy('og.number');

        return $qb->executeQuery()->fetchAllAssociative();
    }
}