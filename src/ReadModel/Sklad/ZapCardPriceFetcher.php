<?php


namespace App\ReadModel\Sklad;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Sklad\Filter\ZapCardPrice\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ZapCardPriceFetcher
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

    public function findUniqueNumbers(ZapSklad $zapSklad, string $number): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('number',)
            ->from('zapCards', 'zc')
            ->innerJoin('zc', 'zapSkladLocation', 'zsl', 'zc.zapCardID = zsl.zapCardID')
            ->andWhere('number like :number')
            ->setParameter("number", $number . '%')
            ->andWhere('zsl.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSklad->getId())
            ->orderBy('number')
            ->groupBy('number')
            ->executeQuery();

        return $qb->fetchFirstColumn();
    }

    /**
     * @param ZapSklad $zapSklad
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(ZapSklad $zapSklad, Filter $filter, int $page, array $settings): PaginationInterface
    {
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
                'zsl.quantityMin',
                'zsl.quantityMax',
                'zsl.zapSkladLocationID',
            )
            ->from('zapCards', 'zc')
            ->leftJoin('zc', 'zapGroup', 'zg', 'zc.zapGroupID = zg.zapGroupID')
            ->innerJoin('zc', 'shop_types', 'st', 'zc.shop_typeID = st.shop_typeID')
            ->innerJoin('zc', 'creaters', 'c', 'zc.createrID = c.createrID')
            ->innerJoin('zc', 'zapSkladLocation', 'zsl', 'zc.zapCardID = zsl.zapCardID')
            ->andWhere('zsl.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSklad->getId())
        ;

        if ($filter->shop_typeID) {
            $qb->andWhere('zc.shop_typeID = :shop_typeID');
            $qb->setParameter('shop_typeID', $filter->shop_typeID);
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


        if ($filter->quantity1_from != null) {
            $qb->andWhere('(SELECT ifnull(Sum(b.quantityIn + b.quantityPath - b.reserve), 0) FROM income a INNER JOIN income_sklad b ON a.incomeID = b.incomeID WHERE a.status = 8 AND a.zapCardID = zc.zapCardID AND b.zapSkladID = 1) >= :quantity1_from');
            $qb->setParameter('quantity1_from', $filter->quantity1_from);
        }

        if ($filter->quantity1_till != null) {
            $qb->andWhere('(SELECT ifnull(Sum(b.quantityIn + b.quantityPath - b.reserve), 0) FROM income a INNER JOIN income_sklad b ON a.incomeID = b.incomeID WHERE a.status = 8 AND a.zapCardID = zc.zapCardID AND b.zapSkladID = 1) <= :quantity1_till');
            $qb->setParameter('quantity1_till', $filter->quantity1_till);
        }

        if ($filter->quantity5_from != null) {
            $qb->andWhere('(SELECT ifnull(Sum(b.quantityIn + b.quantityPath - b.reserve), 0) FROM income a INNER JOIN income_sklad b ON a.incomeID = b.incomeID WHERE a.status = 8 AND a.zapCardID = zc.zapCardID AND b.zapSkladID = 5) >= :quantity5_from');
            $qb->setParameter('quantity5_from', $filter->quantity5_from);
        }

        if ($filter->quantity5_till != null) {
            $qb->andWhere('(SELECT ifnull(Sum(b.quantityIn + b.quantityPath - b.reserve), 0) FROM income a INNER JOIN income_sklad b ON a.incomeID = b.incomeID WHERE a.status = 8 AND a.zapCardID = zc.zapCardID AND b.zapSkladID = 5) <= :quantity5_till');
            $qb->setParameter('quantity5_till', $filter->quantity5_till);
        }

        if ($filter->quantity6_from != null) {
            $qb->andWhere('(SELECT ifnull(Sum(b.quantityIn + b.quantityPath - b.reserve), 0) FROM income a INNER JOIN income_sklad b ON a.incomeID = b.incomeID WHERE a.status = 8 AND a.zapCardID = zc.zapCardID AND b.zapSkladID = 6) >= :quantity6_from');
            $qb->setParameter('quantity6_from', $filter->quantity6_from);
        }

        if ($filter->quantity6_till != null) {
            $qb->andWhere('(SELECT ifnull(Sum(b.quantityIn + b.quantityPath - b.reserve), 0) FROM income a INNER JOIN income_sklad b ON a.incomeID = b.incomeID WHERE a.status = 8 AND a.zapCardID = zc.zapCardID AND b.zapSkladID = 6) <= :quantity6_till');
            $qb->setParameter('quantity6_till', $filter->quantity6_till);
        }

        if ($filter->quantity) {
            if ($filter->quantity == 'necessary') {
                $qb->andWhere('(SELECT ifnull(Sum(b.quantityIn + b.quantityPath - b.reserve), 0) FROM income a INNER JOIN income_sklad b ON a.incomeID = b.incomeID WHERE a.status = 8 AND a.zapCardID = zc.zapCardID AND b.zapSkladID = zsl.zapSkladID) < zsl.quantityMin');
            }
            if ($filter->quantity == 'unnecessary') {
                $qb->andWhere('(SELECT ifnull(Sum(b.quantityIn + b.quantityPath - b.reserve), 0) FROM income a INNER JOIN income_sklad b ON a.incomeID = b.incomeID WHERE a.status = 8 AND a.zapCardID = zc.zapCardID AND b.zapSkladID = zsl.zapSkladID) > zsl.quantityMin');
            }
        }

        if ($filter->abc) {
            $qb->innerJoin('zc', 'zapCard_abc', 'zcabc', 'zc.zapCardID = zcabc.zapCardID AND zsl.zapSkladID = zcabc.zapSkladID');
            $qb->andWhere('upper(zcabc.abc) = :abc');
            $qb->setParameter('abc', strtoupper($filter->abc));
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

        if (!in_array($sort, ['group_name', 'zc.number', 'creater_name', 'shop_type_name'], true)) {
            $sort = 'zc.number';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}