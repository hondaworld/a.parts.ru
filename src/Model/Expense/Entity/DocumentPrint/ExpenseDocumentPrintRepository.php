<?php

namespace App\Model\Expense\Entity\DocumentPrint;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExpenseDocumentPrint|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpenseDocumentPrint|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpenseDocumentPrint[]    findAll()
 * @method ExpenseDocumentPrint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseDocumentPrintRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ExpenseDocumentPrint::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ExpenseDocumentPrint
     */
    public function get(int $id): ExpenseDocumentPrint
    {
        if (!$expenseDocumentPrint = $this->find($id)) {
            throw new EntityNotFoundException('Накладная не найдена');
        }

        return $expenseDocumentPrint;
    }

    public function add(ExpenseDocumentPrint $expenseDocumentPrint): void
    {
        $this->em->persist($expenseDocumentPrint);
    }
}
