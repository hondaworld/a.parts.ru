<?php


namespace App\ReadModel\Sklad;


use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\ORM\EntityManagerInterface;

class ZapSkladFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapSklad::class);
    }

    public function get(int $id): ZapSklad
    {
        return $this->repository->get($id);
    }

    public function assoc(int $id = null): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'zapSkladID',
                'name_short'
            )
            ->from('zapSklad')
            ->where('isHide = 0')
            ->orderBy('name_short');

        if ($id) {
            $stmt->orWhere('zapSkladID = :id')
                ->setParameter('id', $id);
        }

        return $stmt->executeQuery()->fetchAllKeyValue();
    }

    public function assocByManager(int $managerID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'zapSkladID',
                'name_short'
            )
            ->from('zapSklad')
            ->where('zapSkladID = ' . ZapSklad::OSN_SKLAD_ID)
            ->orderBy('name_short');

        $arrOsn =  $stmt->executeQuery()->fetchAllKeyValue();

        $stmt = $this->connection->createQueryBuilder()
            ->select(
                's.zapSkladID',
                'name_short'
            )
            ->from('zapSklad', 's')
            ->innerJoin('s', 'linkReportManagerSklad', 'l', 's.zapSkladID = l.zapSkladID')
            ->where('l.managerID = :managerID')
            ->setParameter('managerID', $managerID)
            ->orderBy('name_short');

        $arr = $stmt->executeQuery()->fetchAllKeyValue();

        if ($arr) return $arr;
        return $arrOsn;
    }

    public function assocForAddGoods(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'zapSkladID',
                'name_short'
            )
            ->from('zapSklad')
            ->where('isHide = 0 AND zapSkladID NOT IN (5,6)')
            ->orderBy('name_short');
        return $stmt->executeQuery()->fetchAllKeyValue();
    }

    public function assocExcludeById(int $id): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'zapSkladID',
                'name_short'
            )
            ->from('zapSklad')
            ->where('isHide = 0')
            ->orderBy('name_short');

        $stmt->orWhere('zapSkladID <> :id')
            ->setParameter('id', $id);

        return $stmt->executeQuery()->fetchAllKeyValue();
    }

    public function assocZapCardEmpty(int $zapCardID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'zapSkladID',
                'name_short'
            )
            ->from('zapSklad')
            ->where('isHide = 0')
            ->andWhere('zapSkladID NOT IN (SELECT zapSkladID FROM zapSkladLocation WHERE zapCardID = :zapCardID)')
            ->setParameter('zapCardID', $zapCardID)
            ->orderBy('name_short');

        return $stmt->executeQuery()->fetchAllKeyValue();
    }

    public function assocAll(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'zapSkladID',
                'name_short'
            )
            ->from('zapSklad')
            ->orderBy('name_short');

        return $stmt->executeQuery()->fetchAllKeyValue();
    }

    public function allSklads(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('zapSklad');

        return $stmt->executeQuery()->fetchAllAssociativeIndexed();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('z.*', 'o.name AS opt')
            ->from('zapSklad', 'z')
            ->leftJoin('z', 'opt', 'o', 'z.optID = o.optID')
            ->orderBy('z.name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}