<?php


namespace App\ReadModel\Document;


use App\Model\Document\Entity\Type\DocumentType;
use Doctrine\ORM\EntityManagerInterface;

class DocumentTypeFetcher
{
    private $connection;
    private $types;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->types = $em->getRepository(DocumentType::class);
    }

    public function get(int $id): DocumentType
    {
        return $this->types->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'doc_typeID',
                'name_short'
            )
            ->from('doc_types')
            ->orderBy('name_short')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function unique(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'doc_typeID',
                'name',
                'name_short'
            )
            ->from('doc_types')
            ->executeQuery();

        return $stmt->fetchAllAssociativeIndexed();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                't.doc_typeID',
                't.name_short',
                't.name',
                't.isHide'
            )
            ->from('doc_types', 't')
            ->orderBy('name_short')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}