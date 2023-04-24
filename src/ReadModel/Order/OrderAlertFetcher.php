<?php


namespace App\ReadModel\Order;


use App\Model\Order\Entity\Alert\OrderAlert;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class OrderAlertFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(OrderAlert ::class);
    }

    public function get(int $id): OrderAlert
    {
        return $this->repository->get($id);
    }

    public function findByOrderGoods(array $goods): array
    {
        if (!$goods) return [];
        $arr = [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'oa.goodID',
                'oa.dateofadded',
                'oat.name'
            )
            ->from('order_alerts', 'oa')
            ->innerJoin('oa', 'order_alert_types', 'oat', 'oa.typeID = oat.typeID')
            ;
        $qb->andWhere($qb->expr()->in('a.goodID', $goods));

        $items = $qb->executeQuery()->fetchAllAssociative();
        if ($items) {
            foreach ($items as $item) {
                $arr[$item['goodID']] = $item;
            }
        }
        return $arr;
    }

    public function findByOrderGood(int $goodID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'oa.alertID',
                'oa.goodID',
                'oa.dateofadded',
                'oat.name'
            )
            ->from('order_alerts', 'oa')
            ->innerJoin('oa', 'order_alert_types', 'oat', 'oa.typeID = oat.typeID')
            ->where('oa.goodID = :goodID')
            ->setParameter('goodID', $goodID)
            ;

        return $qb->executeQuery()->fetchAllAssociative();
    }

}