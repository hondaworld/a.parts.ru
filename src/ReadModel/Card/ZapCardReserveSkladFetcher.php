<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Reserve\ZapCardReserve;
use App\Model\Card\Entity\ReserveSklad\ZapCardReserveSklad;
use Doctrine\ORM\EntityManagerInterface;

class ZapCardReserveSkladFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapCardReserveSklad::class);
    }

    public function get(int $id): ZapCardReserveSklad
    {
        return $this->repository->get($id);
    }

    public function allByZapCardAndZapSklad(int $zapCardID, int $zapSkladID): array
    {
        // Отправляются со склада
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'b.name_short AS sklad_name',
                'ifnull(Sum(a.quantity), 0) AS reserve'
            )
            ->from('zapCardReserve_sklad', 'a')
            ->innerJoin('a', 'zapSklad', 'b', 'a.zapSkladID_to = b.zapSkladID')
            ->innerJoin('a', 'expense_sklad', 'c', 'a.expenseID = c.expenseID')
            ->andWhere('c.status IN (0,3)')
            ->andWhere('a.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere('a.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSkladID)
            ->groupBy('a.zapSkladID_to');

        $items1 = $qb->executeQuery()->fetchAllAssociative();

        // Оприходуются на склад
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'b.name_short AS sklad_name',
                'ifnull(Sum(a.quantity), 0) AS reserve'
            )
            ->from('zapCardReserve_sklad', 'a')
            ->innerJoin('a', 'zapSklad', 'b', 'a.zapSkladID_to = b.zapSkladID')
            ->innerJoin('a', 'expense_sklad', 'c', 'a.expenseID = c.expenseID')
            ->andWhere('c.status = 1')
            ->andWhere('a.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere('a.zapSkladID_to = :zapSkladID')
            ->setParameter('zapSkladID', $zapSkladID)
            ->groupBy('a.zapSkladID_to');

        $items2 = $qb->executeQuery()->fetchAllAssociative();

        return array_merge($items1, $items2);
    }

}