<?php


namespace App\ReadModel\Firm;


use App\Model\EntityNotFoundException;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\User\User;
use App\ReadModel\User\Filter\User\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class FirmFetcher
{
    private $connection;
    private $paginator;
    private $firms;

    public const DEFAULT_SORT_FIELD_NAME = 'name_short';
    public const DEFAULT_SORT_DIRECTION = 'asc';

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->firms = $em->getRepository(Firm::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): Firm
    {

        if (!$firm = $this->firms->find($id)) {
            throw new EntityNotFoundException('Организация не найдена');
        }

        return $firm;
    }

    public function getMainFirmID(): ?int
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('firmID')
            ->from('firms')
            ->where('isMain = 1')
            ->setMaxResults(1)
        ;
        return $qb->executeQuery()->fetchOne();
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('firmID', 'name_short')
            ->from('firms')
            ->orderBy('name_short');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocNotHide(int $firmID = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('firmID', 'name_short')
            ->from('firms')
            ->where('isHide = 0')
            ->orderBy('name_short');

        if ($firmID != null) {
            $qb->orWhere('firmID = :firmID')
                ->setParameter('firmID', $firmID);
        }

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocByManager(Manager $manager): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('firmID', 'name_short')
            ->from('firms')
            ->where('firmID NOT IN (SELECT firmID FROM linkManagerFirm WHERE managerID = :managerID)')
            ->setParameter('managerID', $manager->getId())
            ->orderBy('name_short');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocByUserBalanceHistory(int $userID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('firmID', 'name_short')
            ->from('firms')
            ->where('firmID IN (SELECT firmID FROM userBalanceHistory WHERE userID = :userID)')
            ->setParameter('userID', $userID)
            ->orderBy('name_short');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocFromBalanceByProviderID(int $providerID)
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('f.firmID', 'f.name_short')
            ->from('firms', 'f')
            ->innerJoin('f', 'firmBalanceHistory', 'b', 'f.firmID = b.firmID')
            ->andWhere('b.providerID = :providerID')
            ->setParameter('providerID', $providerID)
            ->orderBy('name_short');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    /**
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'f.firmID',
                'f.name_short',
                'f.name',
                'f.isHide',
            )
            ->from('firms', 'f');

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['name_short', 'name'], true)) {
            $sort = self::DEFAULT_SORT_FIELD_NAME;
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, 1, 1000, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}