<?php


namespace App\ReadModel\Sklad;


use App\Model\Card\Entity\Location\ZapSkladLocation;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use PDO;

class ZapSkladLocationFetcher
{
    private Connection $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapSkladLocation::class);
    }

    public function get(int $id): ZapSkladLocation
    {
        return $this->repository->get($id);
    }

    /**
     * @throws Exception
     */
    public function findNumbersByLocation(int $locationID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('c.number', 'c.zapCardID', 'l.zapSkladID', 's.name AS sklad')
            ->from('zapSkladLocation', 'l')
            ->innerJoin('l', 'zapCards', 'c', 'l.zapCardID = c.zapCardID')
            ->innerJoin('l', 'zapSklad', 's', 'l.zapSkladID = s.zapSkladID')
            ->where('l.locationID = :locationID')
            ->setParameter('locationID', $locationID)
            ->orderBy('c.number')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    /**
     * Возвращает минимумы карточек деталей по ID
     *
     * @param array $zapCards
     * @return array
     * @throws Exception
     */
    public function findQuantityMinByZapCards(array $zapCards): array
    {
        if (!$zapCards) return [];
        $result = [];
        $qb = $this->connection->createQueryBuilder()
            ->select('l.zapCardID', 'l.zapSkladID', 'l.quantityMin')
            ->from('zapSkladLocation', 'l');

        $qb->andWhere($qb->expr()->in('l.zapCardID', $zapCards));
        $arr = $qb->executeQuery()->fetchAllAssociative();

        foreach ($arr as $item) {
            $result[$item['zapCardID']][$item['zapSkladID']] = $item['quantityMin'];
        }

        return $result;
    }

    /**
     * Возвращает минимумы карточек деталей
     *
     * @return array
     * @throws Exception
     */
    public function findPositiveQuantityMin(): array
    {
        //SELECT a.zapCardID, SUM(a.quantityMin) AS quantityMin, b.number, b.createrID FROM zapSkladLocation a INNER JOIN zapCards b ON a.zapCardID = b.zapCardID WHERE a.zapSkladID IN (1,5) GROUP BY a.zapCardID, b.number, b.createrID HAVING SUM(a.quantityMin) > 0

        $qb = $this->connection->createQueryBuilder()
            ->select('l.zapCardID', 'SUM(l.quantityMin) AS quantityMin')
            ->from('zapSkladLocation', 'l')
            ->groupBy('l.zapCardID')
            ->having('SUM(l.quantityMin) > 0');

        $qb->andWhere($qb->expr()->in('l.zapSkladID', [ZapSklad::MSK, ZapSklad::SPB, ZapSklad::SPB2]));
        return $qb->executeQuery()->fetchAllKeyValue();
    }

    /**
     * Возвращает количество карточек деталей по ID на складах
     *
     * @param array $zapCards
     * @return array
     * @throws Exception
     */
    public function findQuantityInByZapCards(array $zapCards): array
    {
        if (!$zapCards) return [];
        $result = [];
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'l.zapCardID',
                'l.zapSkladID',
                'zs.name_short AS sklad_name',
                'sl.name_short AS location',
//                'SUM(isk.quantityIn) AS quantityIn'
//                '(SELECT SUM(isk.quantityIn) FROM income_sklad isk INNER JOIN income i WHERE i.zapCardID = l.zapCardID AND isk.zapSkladID = l.zapSkladID) AS quantityIn'
            )
            ->from('zapSkladLocation', 'l')
            ->innerJoin('l', 'shopLocation', 'sl', 'sl.locationID = l.locationID')
            ->innerJoin('l', 'zapSklad', 'zs', 'l.zapSkladID = zs.zapSkladID')
//            ->innerJoin('l', 'income_sklad', 'isk', 'l.zapSkladID = isk.zapSkladID')
//            ->innerJoin('l', 'income', 'i', 'l.zapSkladID = isk.zapSkladID AND l.zapCardID = i.zapCardID')
            ->groupBy('l.zapCardID, l.zapSkladID');

        $qb->andWhere($qb->expr()->in('l.zapCardID', $zapCards));
        $arr = $qb->executeQuery()->fetchAllAssociative();

        foreach ($arr as $item) {
            $result[$item['zapCardID']][$item['zapSkladID']] = [
                'sklad_name' => $item['sklad_name'],
                'location' => $item['location'],
//                'quantityIn' => $item['quantityIn'],
            ];
        }

        return $result;
    }

    /**
     * @param int $zapCardID
     * @return array
     * @throws Exception
     */
    public function allByZapCard(int $zapCardID): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'a.zapSkladLocationID',
                'a.zapCardID',
                'a.zapSkladID',
                'a.quantityMin',
                'a.quantityMinIsReal',
                'a.quantityMax',
                's.name AS sklad_name',
                'sl.name_short AS location',
            )
            ->from('zapSkladLocation', 'a')
            ->innerJoin('a', 'zapSklad', 's', 'a.zapSkladID = s.zapSkladID')
            ->leftJoin('a', 'shopLocation', 'sl', 'a.locationID = sl.locationID')
            ->andWhere('a.zapCardID = :zapCardID')
            ->setParameter('zapCardID', $zapCardID)
            ->andWhere('s.isHide = 0')
            ->orderBy('sklad_name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}