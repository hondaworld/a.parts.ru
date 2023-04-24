<?php

namespace App\Model\Income\Entity\Document;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IncomeDocument|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncomeDocument|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncomeDocument[]    findAll()
 * @method IncomeDocument[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncomeDocumentRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, IncomeDocument::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return IncomeDocument
     */
    public function get(int $id): IncomeDocument
    {
        if (!$incomeDocument = $this->find($id)) {
            throw new EntityNotFoundException('Приходная накладная не найдена');
        }

        return $incomeDocument;
    }

    public function add(IncomeDocument $incomeDocument): void
    {
        $this->em->persist($incomeDocument);
    }
}
