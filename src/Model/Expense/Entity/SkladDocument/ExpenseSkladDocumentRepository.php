<?php

namespace App\Model\Expense\Entity\SkladDocument;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExpenseSkladDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpenseSkladDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpenseSkladDocument[]    findAll()
 * @method ExpenseSkladDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseSkladDocumentRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ExpenseSkladDocument::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ExpenseSkladDocument
     */
    public function get(int $id): ExpenseSkladDocument
    {
        if (!$expenseSkladDocument = $this->find($id)) {
            throw new EntityNotFoundException('Накладная не найдена');
        }

        return $expenseSkladDocument;
    }

    public function add(ExpenseSkladDocument $expenseSkladDocument): void
    {
        $this->em->persist($expenseSkladDocument);
    }
}
