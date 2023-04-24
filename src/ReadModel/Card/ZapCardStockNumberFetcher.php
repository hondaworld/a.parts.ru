<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Stock\ZapCardStock;
use App\Model\Card\Entity\StockNumber\ZapCardStockNumber;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping as ORM;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ZapCardStockNumberFetcher
{
    private Connection $connection;
    private PaginatorInterface $paginator;
    /**
     * @ORM\Column(type="string")
     */
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'number';
    public const DEFAULT_SORT_DIRECTION = 'asc';

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapCardStockNumber ::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ZapCardStockNumber
    {
        return $this->repository->get($id);
    }

    /**
     * @param string $number
     * @param int $createrID
     * @return ZapCardStockView
     */
    public function findByNumberAndCreater(string $number, int $createrID): ZapCardStockView
    {
        $zapCardStockView = new ZapCardStockView();
        try {
            $qb = $this->connection->createQueryBuilder()
                ->select('s.stockID', 's.name', 's.text', 'n.price_stock')
                ->from('zapCardStock_numbers', 'n')
                ->innerJoin('n', 'zapCardStocks', 's', 'n.stockID = s.stockID')
                ->andWhere('n.number = :number')
                ->setParameter('number', $number)
                ->andWhere('n.createrID = :createrID')
                ->setParameter('createrID', $createrID)
                ->andWhere('s.isHide = 0')
                ->setMaxResults(1);

            $items = $qb->executeQuery()->fetchAssociative();
            if ($items) {
                $zapCardStockView->stockID = $items['stockID'];
                $zapCardStockView->name = $items['name'];
                $zapCardStockView->text = $items['text'];
                $zapCardStockView->price_stock = $items['price_stock'];
            }
        } catch (Exception $e) {

        }
        return $zapCardStockView;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function findAllWithNumberAndCreater(): array
    {
        $result = [];

        $arr = $this->connection->createQueryBuilder()
            ->select('n.number, n.createrID, s.stockID', 's.name', 's.text', 'n.price_stock')
            ->from('zapCardStock_numbers', 'n')
            ->innerJoin('n', 'zapCardStocks', 's', 'n.stockID = s.stockID')
            ->andWhere('s.isHide = 0')
            ->executeQuery()
            ->fetchAllAssociative();

        foreach ($arr as $item) {
            $result[$item['createrID']][$item['number']] = $item;
        }
        return $result;
    }

    /**
     * @param ZapCardStock $stock
     * @param array $settings
     * @return PaginationInterface
     */
    public function allByStock(ZapCardStock $stock, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'n.numberID',
                'n.number',
                'n.createrID',
                'n.price_stock',
                'c.name AS creater'
            )
            ->from('zapCardStock_numbers', 'n')
            ->innerJoin('n', 'creaters', 'c', 'n.createrID = c.createrID')
            ->where('n.stockID = :stockID')
            ->setParameter('stockID', $stock->getId());

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['number', 'creater'], true)) {
            $sort = 'number';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, 10000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

}