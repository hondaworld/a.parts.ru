<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Auto\ZapCardAuto;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class ZapCardAutoFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapCardAuto ::class);
    }

    public function get(int $id): ZapCardAuto
    {
        return $this->repository->get($id);
    }

    /**
     * @param int $zapCardID
     * @param int|null $auto_modelID
     * @param int|null $moto_modelID
     * @param int $year
     * @return int
     * @throws \Doctrine\DBAL\Exception
     */
    public function hasAuto(int $zapCardID, ?int $auto_modelID, ?int $moto_modelID, int $year): int
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('Count(a.zapCard_autoID)')
            ->from('zapCard_auto', 'a')
            ->andWhere('a.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere('a.year = :year')
            ->setParameter('year', $year);

        if ($auto_modelID) {
            $stmt->andWhere('a.auto_modelID = :auto_modelID')->setParameter('auto_modelID', $auto_modelID);
        }
        if ($moto_modelID) {
            $stmt->andWhere('a.moto_modelID = :moto_modelID')->setParameter('moto_modelID', $moto_modelID);
        }

        return $stmt->executeQuery()->fetchOne();
    }

    /**
     * @param int $zapCardID
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function allByZapCard(int $zapCardID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'a.zapCard_autoID',
                'a.year',
                "CONCAT(m1.name, ' ', ma.name) AS auto_model",
                "CONCAT(m2.name, ' ', mm.name) AS moto_model"
            )
            ->from('zapCard_auto', 'a')
            ->leftJoin('a', 'auto_model', 'ma', 'a.auto_modelID = ma.auto_modelID')
            ->leftJoin('ma', 'auto_marka', 'm1', 'ma.auto_markaID = m1.auto_markaID')
            ->leftJoin('a', 'moto_model', 'mm', 'a.moto_modelID = mm.moto_modelID')
            ->leftJoin('mm', 'auto_marka', 'm2', 'mm.auto_markaID = m2.auto_markaID')
            ->andWhere('a.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->orderBy('auto_model, moto_model, a.year')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}