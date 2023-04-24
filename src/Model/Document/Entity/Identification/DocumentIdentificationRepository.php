<?php

namespace App\Model\Document\Entity\Identification;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DocumentIdentification|null find($id, $lockMode = null, $lockVersion = null)
 * @method DocumentIdentification|null findOneBy(array $criteria, array $orderBy = null)
 * @method DocumentIdentification[]    findAll()
 * @method DocumentIdentification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DocumentIdentificationRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, DocumentIdentification::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return DocumentIdentification
     */
    public function get(int $id): DocumentIdentification
    {
        if (!$documentIdentification = $this->find($id)) {
            throw new EntityNotFoundException('Идентификационный документ не найден');
        }

        return $documentIdentification;
    }

    public function add(DocumentIdentification $documentIdentification): void
    {
        $this->em->persist($documentIdentification);
    }

    public function updateMain(): void
    {
        $qb = $this->createQueryBuilder('d')
            ->update()
            ->set('d.isMain', 'false');
        $qb->getQuery()->execute();
    }
}
