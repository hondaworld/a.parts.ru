<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Reserve\ZapCardReserve;
use App\Model\Income\Entity\Status\IncomeStatus;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class ZapCardReserveFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapCardReserve::class);
    }

    public function get(int $id): ZapCardReserve
    {
        return $this->repository->get($id);
    }

    public function allByZapCardAndZapSklad(int $zapCardID, int $zapSkladID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'a.goodID',
                'a.orderID',
                'a.number',
                'c.userID',
                'ifnull(Sum(a.quantity), 0) AS reserve',
                'MIN(a.dateofclosed) AS dateofclosed'
            )
            ->from('zapCardReserve', 'a')
            ->innerJoin('a', 'income', 'b', 'a.incomeID = b.incomeID')
            ->innerJoin('a', 'orders', 'c', 'a.orderID = c.orderID')
            ->andWhere('b.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere('a.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSkladID)
            ->groupBy('a.goodID, a.orderID, a.number, c.userID');

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * В резерве ли заказная деталь, находящаяся не в статусе "На складе"
     *
     * @param int $goodID
     * @return bool
     * @throws Exception
     */
    public function isOrderGoodReserveInPath(int $goodID): bool
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('Count(r.reserveID)')
            ->from('zapCardReserve', 'r')
            ->innerJoin('r', 'income', 'i', 'r.incomeID = i.incomeID')
            ->where('r.goodID = :goodID')
            ->setParameter('goodID', $goodID)
            ->andWhere('i.status <> :status')
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE);
        return $qb->executeQuery()->fetchOne() > 0;
    }

    /**
     * Получение количества и даты резерва заказной детали
     *
     * @param int $goodID
     * @return array
     */
    public function getOrderGoodReserve(int $goodID): ?array
    {
        try {

            $qb = $this->connection->createQueryBuilder()
                ->select(
                    'ifnull(Sum(quantity), 0) AS quantity',
                    'MIN(dateofclosed) AS dateofclosed',
                )
                ->from('zapCardReserve', 'r')
                ->where('r.goodID = :goodID')
                ->setParameter('goodID', $goodID);
            return $qb->executeQuery()->fetchAssociative();
        } catch (Exception $e) {
            return null;
        }
    }

}