<?php


namespace App\ReadModel\Auto;


use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\Model\AutoModel;
use Doctrine\ORM\EntityManagerInterface;

class AutoModelFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(AutoModel ::class);
    }

    public function get(int $id): AutoModel
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("auto_modelID, CONCAT(brand.name, ' ', model.name) AS name")
            ->from('auto_model', 'model')
            ->innerJoin('model', 'auto_marka', 'brand', 'model.auto_markaID = brand.auto_markaID')
            ->orderBy('name');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function allByMarka(AutoMarka $autoMarka): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('m.*')
            ->from('auto_model', 'm')
            ->andWhere('m.auto_markaID = :auto_markaID')
            ->setParameter('auto_markaID', $autoMarka->getId())
            ->orderBy('m.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    public function findWithKits(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'm.auto_modelID',
                "CONCAT(ma.name, ' ', m.name) AS name"
            )
            ->from('auto_model', 'm')
            ->innerJoin('m', 'auto_marka', 'ma', 'ma.auto_markaID = m.auto_markaID')
            ->andWhere('exists (SELECT 1 FROM zapCardKits WHERE auto_modelID = m.auto_modelID)')
            ->orderBy('name');

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function findWithTo(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'm.auto_modelID',
                "CONCAT(ma.name, ' ', m.name) AS name"
            )
            ->from('auto_model', 'm')
            ->innerJoin('m', 'auto_marka', 'ma', 'ma.auto_markaID = m.auto_markaID')
            ->andWhere('exists (
                SELECT 1 FROM auto_generation aa 
	            INNER JOIN auto_modification cc ON aa.auto_generationID = cc.auto_generationID 
	            INNER JOIN workPeriod bb ON cc.auto_modificationID = bb.auto_modificationID
	            WHERE aa.auto_modelID = m.auto_modelID
	            )'
            )
            ->orderBy('name');

        return $stmt->executeQuery()->fetchAllAssociative();
    }

    public function findModificationsWithTo(AutoModel $autoModel): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'gen.auto_generationID',
                'modif.auto_modificationID',
                "CONCAT(gen.name, ' (', gen.yearfrom, '-', (if(gen.yearto <> '', gen.yearto, 'н.в.')), ')', ' ', modif.name) AS modification"
            )
            ->from('auto_modification', 'modif')
            ->innerJoin('modif', 'auto_generation', 'gen', 'modif.auto_generationID = gen.auto_generationID')
            ->andWhere('exists (SELECT 1 FROM workPeriod WHERE auto_modificationID = modif.auto_modificationID)')
            ->andWhere('gen.auto_modelID = :auto_modelID')
            ->setParameter('auto_modelID', $autoModel->getId())
            ->orderBy('modification');

        return $stmt->executeQuery()->fetchAllAssociative();
    }

}