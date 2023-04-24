<?php


namespace App\ReadModel\Document;


use App\Model\Document\Entity\Identification\DocumentIdentification;
use Doctrine\ORM\EntityManagerInterface;

class DocumentIdentificationFetcher
{
    private $connection;
    private $identifications;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->identifications = $em->getRepository(DocumentIdentification::class);
    }

    public function get(int $id): DocumentIdentification
    {
        return $this->identifications->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'doc_identID',
                'name'
            )
            ->from('doc_idents')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'i.doc_identID',
                'i.name',
                'i.isHide',
                'i.isMain'
            )
            ->from('doc_idents', 'i')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}