<?php

namespace App\Model\Document\Entity\Type;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DocumentType|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentType|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentType[]    findAll()
 * @method DocumentType[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentTypeRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, DocumentType::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return DocumentType
     */
    public function get(int $id): DocumentType
    {
        if (!$documentType = $this->find($id)) {
            throw new EntityNotFoundException('Тип документа не найден');
        }

        return $documentType;
    }

    public function add(DocumentType $documentType): void
    {
        $this->em->persist($documentType);
    }
}
