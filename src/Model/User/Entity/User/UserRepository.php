<?php

namespace App\Model\User\Entity\User;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, User::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return User
     */
    public function get(int $id): User
    {
        if (!$user = $this->find($id)) {
            throw new EntityNotFoundException('Клиент не найден');
        }

        return $user;
    }

    public function add(User $user): void
    {
        $this->em->persist($user);
    }

    public function hasByPhoneMobile(string $phonemob, int $id = 0): bool
    {
        $query = $this->createQueryBuilder('u')
            ->select('COUNT(u.userID)')
            ->andWhere('u.phonemob = :phonemob')
            ->setParameter('phonemob', $phonemob);

        if ($id) {
            $query->andWhere('u.userID <> :id')->setParameter('id', $id);
        }

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    public function findByPhoneMobile(string $phonemob): ?User
    {
        return $this->findOneBy(['phonemob' => $phonemob]);
    }

    /**
     * @param string $value
     * @return User[]
     */
    public function findByValue(string $value): array
    {
        $result = [];
        $query = $this->createQueryBuilder('u');

        $phonemob = preg_replace('/[^0-9+]/', '', $value);
        if ($phonemob != '') {
            $query->orWhere($query->expr()->like('u.phonemob', ':phonemob'));
            $query->setParameter('phonemob', '%' . $phonemob . '%');
        }

        $query->orWhere($query->expr()->like("CONCAT(u.user_name.firstname, ' ', u.user_name.lastname)", ':name'));
        $query->setParameter('name', '%' . $value . '%');

        $query->orWhere($query->expr()->like("u.ur.organization", ':organization'));
        $query->setParameter('organization', '%' . $value . '%');

        $arr = $query->getQuery()->getResult();

        foreach ($arr as $item) {
            $result[$item->getId()] = $item;
        }

        return $result;
    }

    /**
     * @param array $users
     * @return User[]
     */
    public function findByUsers(array $users): array
    {
        if (empty($users)) return [];

        $result = [];
        $query = $this->createQueryBuilder('u')
            ->select('u, o')
            ->innerJoin('u.opt', 'o');
        $query->andWhere($query->expr()->in('u.userID', $users));

        $arr = $query->getQuery()->getResult();

        foreach ($arr as $item) {
            $result[$item->getId()] = $item;
        }

        return $result;
    }

    /**
     * @return User[]
     */
    public function findWithService(): array
    {
        $result = [];
        $query = $this->createQueryBuilder('u')
            ->select('u, o')
            ->innerJoin('u.opt', 'o')
            ->orderBy('u.dateofservice');
        $query->andWhere('u.dateofservice IS NOT NULL');

        $arr = $query->getQuery()->getResult();

        foreach ($arr as $item) {
            $result[$item->getId()] = $item;
        }

        return $result;
    }

    /**
     * @return User[]
     */
    public function findWithDelivery(): array
    {
        $result = [];
        $query = $this->createQueryBuilder('u')
            ->select('u, o')
            ->innerJoin('u.opt', 'o')
            ->orderBy('u.dateofdelivery');
        $query->andWhere('u.dateofdelivery IS NOT NULL');

        $arr = $query->getQuery()->getResult();

        foreach ($arr as $item) {
            $result[$item->getId()] = $item;
        }

        return $result;
    }

    /**
     * @return User[]
     */
    public function findForEmailPrices(): array
    {
        $query = $this->createQueryBuilder('u')
            ->select('u')
            ->andWhere("u.email.email_send <> '' AND u.email.email_send_isActive = true OR u.email_price.email_price <> ''")
            ->andWhere("u.email_price.isEmailPrice = true")
            ->andWhere("u.isHide = false");

        return $query->getQuery()->getResult();
    }

    /**
     * @return User[]
     */
    public function findForEmailPricesSummary(): array
    {
        $query = $this->createQueryBuilder('u')
            ->select('u')
            ->andWhere("u.email.email_send <> '' AND u.email.email_send_isActive = true OR u.email_price.email_price <> ''")
            ->andWhere("u.email_price.isEmailPriceSummary = true")
            ->andWhere("u.isHide = false");

        return $query->getQuery()->getResult();
    }

    /**
     * @param \DateTime $dateFrom
     * @param \DateTime $dateTill
     * @return User[]
     * @throws \Doctrine\DBAL\Exception
     */
    public function findUsersNotReview(\DateTime $dateFrom, \DateTime $dateTill): array
    {
//        SELECT b.userID
//	FROM order_goods a
//	INNER JOIN orders b ON a.orderID = b.orderID
//	INNER JOIN users c ON b.userID = c.userID
//	INNER JOIN expenseDocuments h ON a.expenseDocumentID = h.expenseDocumentID
//	WHERE a.expenseDocumentID <> 0 AND a.number <> '15400PLMA03' AND h.dateofadded >= '".$date_from."' AND h.dateofadded <= '".$date_to."' AND c.optID = 1 AND isReviewSend = 0
//	GROUP BY b.userID

        $qb = $this->em->getConnection()->createQueryBuilder('u')
            ->select('u.userID')
            ->from('users', 'u')
            ->innerJoin('u', 'orders', 'o', 'u.userID = o.userID')
            ->innerJoin('o', 'order_goods', 'og', 'o.orderID = og.orderID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'og.expenseDocumentID = ed.expenseDocumentID')
            ->andWhere("og.number <> :number")
            ->setParameter('number', '15400PLMA03')
            ->andWhere("u.optID = 1")
            ->andWhere("u.isReviewSend = 0")
            ->andWhere('ed.dateofadded >= :datefrom')
            ->setParameter('datefrom', $dateFrom->format('Y-m-d') . ' 00:00:00')
            ->andWhere('ed.dateofadded < :datetill')
            ->setParameter('datetill', $dateTill->format('Y-m-d') . ' 00:00:00')
            ->groupBy("u.userID");

        $arr = $qb->executeQuery()->fetchFirstColumn();

        if (!$arr) return [];

        return $this->findByUsers($arr);
    }

    /**
     * @return User[]
     */
    public function findWithPriceEmail(): array
    {
        $qb = $this->createQueryBuilder('u')
            ->where("u.price.email <> ''");

        return $qb->getQuery()->getResult();
    }

    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?User
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
