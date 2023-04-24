<?php

namespace App\Model\Order\Entity\Order;

use App\Model\EntityNotFoundException;
use App\Model\User\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Order|null find($id, $lockMode = null, $lockVersion = null)
 * @method Order|null findOneBy(array $criteria, array $orderBy = null)
 * @method Order[]    findAll()
 * @method Order[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Order::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Order
     */
    public function get(int $id): Order
    {
        if (!$order = $this->find($id)) {
            throw new EntityNotFoundException('Заказ не найден');
        }

        return $order;
    }

    public function add(Order $order): void
    {
        $this->em->persist($order);
    }

    public function getWorking(User $user): ?Order
    {
        $query = $this->em->getConnection()->createQueryBuilder()
            ->select('o.orderID')
            ->from('orders', 'o')
            ->innerJoin('o', 'order_goods', 'og', 'o.orderID = og.orderID')
            ->andWhere('og.expenseDocumentID is null')
            ->andWhere('og.isDeleted = 0')
            ->andWhere('o.status = 2')
            ->andWhere('og.incomeID is null')
            ->andWhere('o.userID = :userID')
            ->setParameter('userID', $user->getId())
            ->andWhere('og.zapSkladID is not null AND goodID NOT IN (SELECT goodID FROM zapCardReserve) OR og.zapSkladID is null')
            ->setMaxResults(1)
            ->orderBy('og.dateofadded', 'DESC');

        $orderID = $query->executeQuery()->fetchOne();

        if (!$orderID) return null;

        return $this->find($orderID);
    }

    /**
     * @param array $arr
     * @return Order[]
     */
    public function findByIDs(array $arr): array
    {
        if (!$arr) return [];
        $query = $this->createQueryBuilder('o')
            ->select('o', 'd', 'p', 'c')
            ->leftJoin('o.delivery', 'd')
            ->leftJoin('o.user_contact', 'c')
            ->leftJoin('o.payMethod', 'p');
        $query->andWhere($query->expr()->in('o.orderID', $arr));

        return $query->getQuery()->getResult();
    }

    public function removeNotConfirmedOrders(): int
    {
        $date = (new \DateTime())->modify('-24 hours');

        $query = $this->createQueryBuilder('o')
            ->select('o')
            ->andWhere('o.status = :status')
            ->setParameter('status', Order::ORDER_STATUS_NEW)
            ->andWhere('o.dateofadded < :dateofadded')
            ->setParameter('dateofadded', $date->format('Y-m-d H') . ':0:0')
            ->andWhere("o.lastOrderPage = ''");

        $orders = $query->getQuery()->getResult();
        foreach ($orders as $order) {
            $this->em->remove($order);
        }
        return count($orders);
    }
}
