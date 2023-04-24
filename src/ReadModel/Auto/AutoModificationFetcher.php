<?php


namespace App\ReadModel\Auto;


use App\Model\Auto\Entity\Generation\AutoGeneration;
use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\Modification\AutoModification;
use Doctrine\ORM\EntityManagerInterface;

class AutoModificationFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(AutoModification ::class);
    }

    public function get(int $id): AutoModification
    {
        return $this->repository->get($id);
    }

    public function assocForTO(int $autoModificationID): array
    {
//        SELECT d.auto_modificationID, CONCAT(a.name, ' ', b.name, ' (', c.yearfrom, ' - ', if(c.yearto = 0, 'н.в.', c.yearto), ') ', d.name) AS auto
//		FROM auto_marka a
//		INNER JOIN auto_model b ON a.auto_markaID = b.auto_markaID
//		INNER JOIN auto_generation c ON b.auto_modelID = c.auto_modelID
//		INNER JOIN auto_modification d ON c.auto_generationID = d.auto_generationID
//		WHERE d.auto_modificationID <> '" . $auto_modificationID . "'
//		ORDER BY a.name, b.name, c.yearfrom, d.name


        $qb = $this->connection->createQueryBuilder()
            ->select("d.auto_modificationID, CONCAT(a.name, ' ', b.name, ' (', c.yearfrom, ' - ', if(c.yearto = 0, 'н.в.', c.yearto), ') ', d.name) AS auto")
            ->from('auto_marka', 'a')
            ->innerJoin('a', 'auto_model', 'b', 'a.auto_markaID = b.auto_markaID')
            ->innerJoin('b', 'auto_generation', 'c', 'b.auto_modelID = c.auto_modelID')
            ->innerJoin('c', 'auto_modification', 'd', 'c.auto_generationID = d.auto_generationID')
            ->where("d.auto_modificationID <> :auto_modificationID")
            ->setParameter('auto_modificationID', $autoModificationID)
            ->orderBy('a.name, b.name, c.yearfrom, d.name');

        return $qb->executeQuery()->fetchAllKeyValue();

    }

    public function allByGeneration(AutoGeneration $autoGeneration): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('m.*')
            ->from('auto_modification', 'm')
            ->andWhere('m.auto_generationID = :auto_generationID')
            ->setParameter('auto_generationID', $autoGeneration->getId())
            ->orderBy('m.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}