<?php

namespace App\Model\Document\Entity\Document;

use App\Model\Document\Entity\Identification\DocumentIdentification;
use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Document|null find($id, $lockMode = null, $lockVersion = null)
 * @method Document|null findOneBy(array $criteria, array $orderBy = null)
 * @method Document[]    findAll()
 * @method Document[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Document::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Document
     */
    public function get(int $id): Document
    {
        if (!$document = $this->find($id)) {
            throw new EntityNotFoundException('Документ не найден');
        }

        return $document;
    }

    public function add(Document $document): void
    {
        $this->em->persist($document);
    }

    public function hasByIdentification(DocumentIdentification $identification): bool
    {
        $query = $this->createQueryBuilder('d')
            ->select('COUNT(d.documentID)')
            ->andWhere('d.identification = :identification')
            ->setParameter('identification', $identification->getId());

        return $query->getQuery()->getSingleScalarResult() > 0;
    }
}
