<?php


namespace App\ReadModel\Contact;

use App\Model\Contact\Entity\Country\Country;
use App\Model\EntityNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class TownRegionFetcher
{
    private $connection;
    private PaginatorInterface $paginator;

    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->paginator = $paginator;
    }

    public function assoc(Country $country): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'regionID',
                'name'
            )
            ->from('townRegions')
            ->where('countryID = :countryID')
            ->setParameter('countryID', $country->getId())
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(Country $country, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('*')
            ->from('townRegions')
            ->where('countryID = :countryID')
            ->setParameter('countryID', $country->getId())
            ->orderBy("name")
        ;

        $size = isset($settings['inPage']) ? $settings['inPage'] : self::PER_PAGE;

        return $this->paginator->paginate($qb, $page, $size);
    }
}