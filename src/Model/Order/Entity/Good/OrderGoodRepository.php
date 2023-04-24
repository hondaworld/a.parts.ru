<?php

namespace App\Model\Order\Entity\Good;

use App\Model\EntityNotFoundException;
use App\Model\User\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OrderGood|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrderGood|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrderGood[]    findAll()
 * @method OrderGood[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrderGoodRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, OrderGood::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return OrderGood
     */
    public function get(int $id): OrderGood
    {
        if (!$orderGood = $this->find($id)) {
            throw new EntityNotFoundException('Товар не найден');
        }

        return $orderGood;
    }

    public function add(OrderGood $orderGood): void
    {
        $this->em->persist($orderGood);
    }

    public function remove(OrderGood $orderGood): void
    {
        $this->em->remove($orderGood);
    }


    /**
     * @param array $arr
     * @return OrderGood[]
     */
    public function findByIDs(array $arr): array
    {
        $query = $this->createQueryBuilder('og')
            ->select('og')
        ;
        $query->andWhere($query->expr()->in('og.goodID', $arr));

        return $query->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @return OrderGood[]
     */
    public function expenses(User $user): array
    {
//        $sub = $this->em->createQueryBuilder()->select('e')->from('App\Model\Expense\Entity\Expense\Expense', 'e')->getQuery()->getResult();
//        $sub = $this->em->getConnection()->createQueryBuilder()->select('goodID')->from('expense', 'e')->executeQuery()->fetchFirstColumn();

        $query = $this->createQueryBuilder('og')
            ->select('og')
            ->innerJoin('og.order', 'o')
            ->innerJoin('og.expenses', 'e')
            ->andWhere('o.user = :user')
            ->setParameter('user', $user)
            ->andWhere('og.expenseDocument IS NULL')
            ->groupBy('og.goodID')
//            ->andWhere('EXISTS (SELECT goodID FROM App\Model\Expense\Entity\Expense WHERE o.goodID = goodID)')
        ;
//        $query->andWhere($query->expr()->in('og.goodID', $sub));
//        $query->andWhere($query->expr()->exists('SELECT e FROM App\Model\Expense\Entity\Expense\Expense e WHERE e.order_good = og.expenses'));
//        $query->andWhere($query->expr()->exists($sub));

        return $query->getQuery()->getResult();
    }
}
