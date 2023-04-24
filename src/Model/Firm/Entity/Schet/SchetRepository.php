<?php

namespace App\Model\Firm\Entity\Schet;

use App\Model\EntityNotFoundException;
use App\Model\Expense\Entity\Document\ExpenseDocument;
use App\Model\User\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Schet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Schet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Schet[]    findAll()
 * @method Schet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchetRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Schet::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Schet
     */
    public function get(int $id): Schet
    {
        if (!$schet = $this->find($id)) {
            throw new EntityNotFoundException('Счет не найден');
        }
        return $schet;
    }

    /**
     * @param User $user
     * @return Schet
     */
    public function findNewByUser(User $user): ?Schet
    {
        return $this->findOneBy(['user' => $user, 'status' => Schet::NEW]);
    }

    /**
     * @param ExpenseDocument $expenseDocument
     * @return Schet[]
     */
    public function findByExpenseDocument(ExpenseDocument $expenseDocument): array
    {
//        $query = "SELECT b.schet_num, b.dateofadded FROM order_goods a INNER JOIN schet b ON a.schetID = b.schetID WHERE a.expenseDocumentID = '".AddSlashes($rowN->expenseDocumentID)."' AND a.schetID <> 0 GROUP BY a.schetID";

        $query = $this->createQueryBuilder('s')
            ->select('s')
            ->innerJoin('s.order_goods', 'og')
            ->andWhere('og.expenseDocument = :expenseDocument')
            ->setParameter('expenseDocument', $expenseDocument)
            ->groupBy('s.schetID');
        return $query->getQuery()->getResult();
    }

    public function add(Schet $schet): void
    {
        $this->em->persist($schet);
    }
}
