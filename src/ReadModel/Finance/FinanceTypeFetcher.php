<?php


namespace App\ReadModel\Finance;


use App\Model\Finance\Entity\FinanceType\FinanceType;
use Doctrine\ORM\EntityManagerInterface;

class FinanceTypeFetcher
{
    private $connection;
    private $financeTypes;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->financeTypes = $em->getRepository(FinanceType::class);
    }

    public function get(int $id): FinanceType
    {
        return $this->financeTypes->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'finance_typeID',
                'name'
            )
            ->from('finance_types')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function assocWithFirm(int $id = null): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'ft.finance_typeID',
                "CONCAT(ft.name, ' (', f.name_short, ')') AS name"
            )
            ->from('finance_types', 'ft')
            ->innerJoin('ft', 'firms', 'f', 'ft.firmID = f.firmID')
            ->andWhere('ft.isHide = 0')
            ->orderBy('ft.name');

        if ($id) {
            $stmt->orWhere('ft.finance_typeID = :id')->setParameter('id', $id);
        }

        return $stmt->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('ft.*', 'f.name AS firm')
            ->from('finance_types', 'ft')
            ->innerJoin('ft', 'firms', 'f', 'ft.firmID = f.firmID')
            ->orderBy('ft.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}