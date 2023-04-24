<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Card\ZapCard;
use App\ReadModel\Card\Filter\ZapCardHistory\Filter;
use App\ReadModel\Expense\ExpenseFetcher;
use App\ReadModel\Income\IncomeFetcher;
use App\ReadModel\Income\IncomeGoodFetcher;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ZapCardHistoryFetcher
{
    private $connection;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofadded';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;
    private IncomeFetcher $incomeFetcher;
    private IncomeGoodFetcher $incomeGoodFetcher;
    private ExpenseFetcher $expenseFetcher;

    public function __construct(
        EntityManagerInterface $em,
        PaginatorInterface $paginator,
        IncomeFetcher $incomeFetcher,
        IncomeGoodFetcher $incomeGoodFetcher,
        ExpenseFetcher $expenseFetcher
    )
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapCard::class);
        $this->paginator = $paginator;
        $this->incomeFetcher = $incomeFetcher;
        $this->incomeGoodFetcher = $incomeGoodFetcher;
        $this->expenseFetcher = $expenseFetcher;
    }

    /**
     * @param ZapCard $zapCard
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     * @throws \Doctrine\DBAL\Exception
     */
    public function all(ZapCard $zapCard, Filter $filter, int $page, array $settings): ?PaginationInterface
    {

        $pn = $this->incomeFetcher->findPNByZapCardWithDocument($zapCard);
        $vz = $this->incomeFetcher->findVZByZapCardWithDocument($zapCard);
        $incomeGoods = $this->incomeGoodFetcher->findByZapCardWithDocument($zapCard);
        $expenses = $this->expenseFetcher->findByZapCardWithDocument($zapCard);

        $all = array_merge_recursive($pn, $vz, $incomeGoods, $expenses);

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['dateofadded'], true)) {
            $sort = 'dateofadded';
        }

        usort($all, function ($a, $b) use ($sort, $direction) {
            if (strtolower($direction) == 'asc') {
                return $a[$sort] <=> $b[$sort];
            } else {
                return $b[$sort] <=> $a[$sort];
            }
        });

        $all = array_filter($all, function($item) use ($filter) {

            $result = true;

            if ($filter->firmID) {
                $result = $result && $item['firmID'] == $filter->firmID;
            }

            if ($filter->zapSkladID) {
                $result = $result && $item['zapSkladID'] == $filter->zapSkladID;
            }

            if ($filter->doc_typeID) {
                $result = $result && $item['doc_typeID'] == $filter->doc_typeID;
            }

            if ($filter->document_num) {
                $result = $result && intval($item['document_num']) == intval($filter->document_num);
            }

            if ($filter->dateofadded) {
                if ($filter->dateofadded['date_from']) {
                    $result = $result &&  new \DateTime($item['dateofadded']) >= new \DateTime($filter->dateofadded['date_from']);
                }
                if ($filter->dateofadded['date_till']) {
                    $result = $result &&  new \DateTime($item['dateofadded']) <= (new \DateTime($filter->dateofadded['date_till']))->modify('+1 day');
                }
            }

            return $result;
        });

        $paginator = $this->paginator->paginate(
            $all,
            $page,
            $size,
            ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]
        );
        $paginator->setItems(array_slice($all, $size * ($page - 1), $size));

        return $paginator;
//        return $this->paginator->paginate($all, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}