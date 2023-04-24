<?php


namespace App\ReadModel\Expense;


use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Expense\Entity\Expense\Expense;
use App\Model\Income\Entity\Income\Income;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;

class ExpenseFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Expense::class);
    }

    public function get(int $id): Expense
    {
        return $this->repository->get($id);
    }

    public function findByIncomeWithDocument(Income $income): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ed.expenseDocumentID',
                'ed.doc_typeID',
                'ed.document_num',
                'ed.dateofadded',
                'if (og.zapSkladID IS NULL, isk.zapSkladID, og.zapSkladID) AS zapSkladID',
                'ed.firmID',
                'e.quantity'
            )
            ->from('expense', 'e')
            ->innerJoin('e', 'order_goods', 'og', 'e.goodID = og.goodID')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'ed.expenseDocumentID = og.expenseDocumentID')
            ->leftJoin('og', 'income_sklad', 'isk', 'og.incomeID = isk.incomeID AND isk.quantity > 0')
            ->where('e.incomeID = :incomeID')
            ->setParameter('incomeID', $income->getId());

        return $qb->executeQuery()->fetchAllAssociative();
    }

    public function findByZapCardWithDocument(ZapCard $zapCard): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'ed.expenseDocumentID',
                'ed.doc_typeID',
                'ed.document_num',
                'ed.dateofadded',
                'if (og.zapSkladID IS NULL, isk.zapSkladID, og.zapSkladID) AS zapSkladID',
                'ed.firmID',
                'og.quantity'
            )
            ->from('order_goods', 'og')
            ->innerJoin('og', 'expenseDocuments', 'ed', 'ed.expenseDocumentID = og.expenseDocumentID')
            ->leftJoin('og', 'income_sklad', 'isk', 'og.incomeID = isk.incomeID AND isk.quantity > 0')
            ->andWhere('og.number = :number')
            ->setParameter('number', $zapCard->getNumber()->getValue())
            ->andWhere('og.createrID = :createrID')
            ->setParameter('createrID', $zapCard->getCreater()->getId())
        ;

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * В отгрузках ли заказная деталь
     *
     * @param int $goodID
     * @return bool
     * @throws Exception
     */
    public function isExpenseByGoodID(int $goodID): bool
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('Count(e.expenseID)')
            ->from('expense', 'e')
            ->where('e.goodID = :goodID')
            ->setParameter('goodID', $goodID);
        return $qb->executeQuery()->fetchOne() > 0;
    }

    /**
     * Получить все отгрузки детали
     *
     * @param int $goodID
     * @return array
     * @throws Exception
     */
    public function findByGoodID(int $goodID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'e.expenseID',
                'e.dateofadded',
                'e.goodID',
                'e.incomeID',
                'e.quantity',
                'i.zapCardID',
                'i.status',
            )
            ->from('expense', 'e')
            ->innerJoin('e', 'income', 'i', 'i.incomeID = e.incomeID')
            ->where('e.goodID = :goodID')
            ->setParameter('goodID', $goodID);
        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * Получение суммы закупки из приходов
     *
     * @param int $goodID
     * @return float
     * @throws Exception
     */
    public function getSumPriceZakByGoodID(int $goodID): ?float
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('SUM(b.price * e.quantity)')
            ->from('expense', 'e')
            ->innerJoin('e', 'income', 'b', 'e.incomeID = b.incomeID')
            ->where('e.goodID = :goodID')
            ->setParameter('goodID', $goodID);
        return $qb->executeQuery()->fetchOne();
    }

}