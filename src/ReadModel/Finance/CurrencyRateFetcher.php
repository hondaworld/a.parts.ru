<?php


namespace App\ReadModel\Finance;


use App\Model\Finance\Entity\Currency\Currency;
use App\Model\Finance\Entity\CurrencyRate\CurrencyRate;
use App\ReadModel\Finance\Filter\CurrencyRate\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class CurrencyRateFetcher
{
    private $connection;
    private $currencyRates;
    private PaginatorInterface $paginator;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->currencyRates = $em->getRepository(CurrencyRate::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): CurrencyRate
    {
        return $this->currencyRates->get($id);
    }

    /**
     * @param Currency $currency
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(Currency $currency, Filter $filter, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.currencyRateID',
                'a.dateofadded',
                'a.numbers',
                'a.rate',
                'b.name_short AS currency_from',
                'c.name_short AS currency_to'
            )
            ->from('currencyRate', 'a')
            ->innerJoin('a', 'currency', 'b', 'a.currencyID = b.currencyID')
            ->innerJoin('a', 'currency', 'c', 'a.currencyID_to = c.currencyID')
            ->where('a.currencyID_to = :currencyID')
            ->setParameter('currencyID', $currency->getId())
            ;

        if ($filter->dateofadded) {
            if ($filter->dateofadded['date_from']) {
                $qb->andWhere($qb->expr()->gte('a.dateofadded', ':date_from'));
                $qb->setParameter('date_from', (new \DateTime($filter->dateofadded['date_from']))->format('Y-m-d'));
            }
            if ($filter->dateofadded['date_till']) {
                $qb->andWhere($qb->expr()->lte('a.dateofadded', ':date_till'));
                $qb->setParameter('date_till', (new \DateTime($filter->dateofadded['date_till']))->format('Y-m-d'));
            }
        }
//
//        if ($filter->login) {
//            $qb->andWhere($qb->expr()->like('login', ':login'));
//            $qb->setParameter('login', mb_strtolower($filter->login) . '%');
//        }
//
//        if ($filter->email) {
//            $qb->andWhere($qb->expr()->like('LOWER(email)', ':email'));
//            $qb->setParameter('email', '%' . mb_strtolower($filter->email) . '%');
//        }

        $sort = isset($settings['sort']) ? $settings['sort'] : self::DEFAULT_SORT_FIELD_NAME;
        $direction = isset($settings['direction']) ? $settings['direction'] : self::DEFAULT_SORT_DIRECTION;
        $size = isset($settings['inPage']) ? $settings['inPage'] : self::PER_PAGE;

        if (!in_array($sort, ['dateofadded'], true)) {
            $sort = 'dateofadded';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

}