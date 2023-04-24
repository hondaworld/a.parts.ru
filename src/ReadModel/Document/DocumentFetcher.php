<?php


namespace App\ReadModel\Document;

use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;

class DocumentFetcher
{
    private $connection;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
    }

    /**
     * @param Manager $manager
     * @return array|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByManager(Manager $manager): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'd.documentID',
                'd.doc_identID',
                'i.name AS document_identification',
                'CONCAT(d.serial, " ", d.number) AS number',
                'd.isHide',
                'd.isMain',
            )
            ->from('documents', 'd')
            ->innerJoin('d', 'doc_idents', 'i', 'd.doc_identID = i.doc_identID')
            ->where('d.managerID = :managerID')
            ->setParameter('managerID', $manager->getId())
            ->orderBy("d.documentID")
            ->executeQuery();

        $result = $stmt->fetchAllAssociative();

        return $result ?: null;
    }

    /**
     * @param User $user
     * @return array|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByUser(User $user): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'd.documentID',
                'd.doc_identID',
                'i.name AS document_identification',
                'CONCAT(d.serial, " ", d.number) AS number',
                'd.isHide',
                'd.isMain',
            )
            ->from('documents', 'd')
            ->innerJoin('d', 'doc_idents', 'i', 'd.doc_identID = i.doc_identID')
            ->where('d.userID = :userID')
            ->setParameter('userID', $user->getId())
            ->orderBy("d.documentID")
            ->executeQuery();

        $result = $stmt->fetchAllAssociative();

        return $result ?: null;
    }

    /**
     * @param Firm $firm
     * @return array|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByFirm(Firm $firm): ?array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'd.documentID',
                'd.doc_identID',
                'i.name AS document_identification',
                'CONCAT(d.serial, " ", d.number) AS number',
                'd.isHide',
                'd.isMain',
            )
            ->from('documents', 'd')
            ->innerJoin('d', 'doc_idents', 'i', 'd.doc_identID = i.doc_identID')
            ->where('d.firmID = :firmID')
            ->setParameter('firmID', $firm->getId())
            ->orderBy("d.documentID")
            ->executeQuery();

        $result = $stmt->fetchAllAssociative();

        return $result ?: null;
    }
}