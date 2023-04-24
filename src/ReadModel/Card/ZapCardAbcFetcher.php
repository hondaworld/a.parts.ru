<?php


namespace App\ReadModel\Card;


use App\Model\Card\Entity\Abc\ZapCardAbc;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class ZapCardAbcFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapCardAbc ::class);
    }

    public function get(int $id): ZapCardAbc
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'a.abc',
                'a.abc'
            )
            ->from('zapCard_abc', 'a')
            ->groupBy('a.abc')
            ->orderBy('a.abc')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function assocByZapCardID(int $zapCardID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'zs.name_short',
                'a.abc'
            )
            ->from('zapCard_abc', 'a')
            ->innerJoin('a', 'zapSklad', 'zs', 'a.zapSkladID = zs.zapSkladID')
            ->where('a.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->orderBy('zs.name_short')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function assocByZapCardsAndZapSklad(array $zapCards, int $zapSkladID): array
    {
        if (empty($zapCards)) return [];
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'a.zapCardID',
                'a.abc'
            )
            ->from('zapCard_abc', 'a')
            ->andWhere('a.zapSkladID = :zapSkladID')
            ->setParameter('zapSkladID', $zapSkladID)
            ;

        $stmt->andWhere($stmt->expr()->in('a.zapCardID', $zapCards));

        return $stmt->executeQuery()->fetchAllKeyValue();
    }

    public function findByZapCards(array $zapCards): array
    {
        if (!$zapCards) return [];
        $arr = [];

        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'a.zapCardID',
                'zs.name_short AS sklad_name',
                'a.abc'
            )
            ->from('zapCard_abc', 'a')
            ->innerJoin('a', 'zapSklad', 'zs', 'a.zapSkladID = zs.zapSkladID')
            ->where('a.zapCardID IN (' . implode(',', $zapCards) . ')')
            ->orderBy('a.zapCardID, zs.name_short')
            ;
        $items = $stmt->executeQuery()->fetchAllAssociative();
        if ($items) {
            foreach ($items as $item) {
                $arr[$item['zapCardID']][$item['sklad_name']] = $item['abc'];
            }
        }

        return $arr;
    }

}