<?php

namespace App\Model\Expense\Entity\Document;

use App\Model\EntityNotFoundException;
use App\Model\Flusher;
use App\Model\User\Entity\User\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ExpenseDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method ExpenseDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method ExpenseDocument[]    findAll()
 * @method ExpenseDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenseDocumentRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;
    private Flusher $flusher;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em, Flusher $flusher)
    {
        parent::__construct($registry, ExpenseDocument::class);
        $this->em = $em;
        $this->flusher = $flusher;
    }

    /**
     * @param int $id
     * @return ExpenseDocument
     */
    public function get(int $id): ExpenseDocument
    {
        if (!$expenseDocument = $this->find($id)) {
            throw new EntityNotFoundException('Расходная накладная не найдена');
        }

        return $expenseDocument;
    }

    /**
     * @param User $user
     * @return ExpenseDocument
     */
    public function getOrCreate(User $user): ExpenseDocument
    {
        $expenseDocument = $this->findOneBy(['user' => $user, 'status' => ExpenseDocument::STATUS_NEW]);
        if (!$expenseDocument) {
            $expenseDocument = new ExpenseDocument($user);
            $this->add($expenseDocument);
            $this->flusher->flush();
        }
        return $expenseDocument;
    }

    public function getNextUserDocument(): ?ExpenseDocument
    {
        return $this->findOneBy(['isUserEmail' => 1]);
    }

    public function add(ExpenseDocument $expenseDocument): void
    {
        $this->em->persist($expenseDocument);
    }
}
