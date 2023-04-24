<?php


namespace App\ReadModel\Income;


use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Sklad\IncomeSklad;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityManagerInterface;

class IncomeSkladFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(IncomeSklad::class);
    }

    public function get(int $id): IncomeSklad
    {
        return $this->repository->get($id);
    }

    /**
     * Вся информация по складам приходов
     *
     * $arr[incomeID][zapSkladID] = item;
     *
     * @param array $arIncomes
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByIncomes(array $arIncomes): array
    {
        if (!$arIncomes) return [];
        $arr = [];
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('income_sklad')
            ->where('incomeID in (' . implode(',', $arIncomes) . ')');

        $items = $qb->executeQuery()->fetchAllAssociative();

        if ($items) {
            foreach ($items as $item) {
                $arr[$item['incomeID']][$item['zapSkladID']] = $item;
            }
        }

        return $arr;
    }

    public function findByIncome(Income $income): array
    {
        $arr = [];
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('income_sklad')
            ->where('incomeID = :incomeID')
            ->setParameter("incomeID", $income->getId());

        $items = $qb->executeQuery()->fetchAllAssociative();

        if ($items) {
            foreach ($items as $item) {
                $arr[$item['zapSkladID']] = $item;
            }
        }

        return $arr;
    }

    /**
     * @param int $incomeID
     * @return string|null
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSkladNameByIncome(int $incomeID): ?string
    {
//        $query = "SELECT a.zapSkladID, b.name_short FROM income_sklad a INNER JOIN zapSklad b ON a.zapSkladID = b.zapSkladID WHERE a.incomeID = ".$row->incomeID." AND a.quantity > 0";

            $qb = $this->connection->createQueryBuilder()
                ->select('zs.name_short')
                ->from('income_sklad', 'i')
                ->innerJoin('i', 'zapSklad', 'zs', 'i.zapSkladID = zs.zapSkladID')
                ->where('i.incomeID = :incomeID')
                ->setParameter("incomeID", $incomeID)
                ->andWhere('i.quantity > 0');
            return $qb->executeQuery()->fetchOne() ?: null;
    }

    /**
     * Получение массива ID складов и количества деталей из приходов, которые находятся в пути на склады.
     * Исключаются те приходы, которые везутся на заказ.
     *
     * @param int $zapCardID
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findOrderedZapCardInAllSklads(int $zapCardID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.zapSkladID",
                "ifnull(Sum(a.quantity), 0)",
            )
            ->from('income_sklad', 'a')
            ->innerJoin('a', 'income', 'c', 'a.incomeID = c.incomeID')
            ->leftJoin('c', 'order_goods', 'og', 'c.incomeID = og.incomeID')
            ->andWhere('c.status IN (2,6,7,9)')
            ->andWhere("og.incomeID is null")
            ->andWhere("zapCardID = :zapCardID")
            ->setParameter('zapCardID', $zapCardID)
            ->groupBy("zapSkladID");
        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function findQuantityZapCardInAllSklads(int $zapCardID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                "a.zapSkladID",
                "ifnull(Sum(a.quantity), 0) AS quantity",
                "ifnull(Sum(a.quantityIn), 0) AS quantityIn",
                "ifnull(Sum(a.quantityPath), 0) AS quantityPath",
                "ifnull(Sum(a.reserve), 0) AS reserve",
                "ifnull(Sum(a.quantityReturn), 0) AS quantityReturn",
                "ifnull((SELECT quantityMin FROM zapSkladLocation WHERE zapCardID = c.zapCardID AND zapSkladID = a.zapSkladID), 0) AS quantityMin",
            )
            ->from('income_sklad', 'a')
            ->innerJoin('a', 'income', 'c', 'a.incomeID = c.incomeID')
            ->andWhere("c.zapCardID = :zapCardID")
            ->setParameter('zapCardID', $zapCardID)
            ->groupBy("a.zapSkladID");
        return $qb->executeQuery()->fetchAllAssociativeIndexed();
    }

}