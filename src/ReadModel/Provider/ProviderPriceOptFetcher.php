<?php


namespace App\ReadModel\Provider;


use App\Model\Provider\Entity\Opt\ProviderPriceOpt;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\User\Entity\Opt\Opt;
use Doctrine\ORM\EntityManagerInterface;

class ProviderPriceOptFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ProviderPriceOpt::class);
    }

    /**
     * @param Provider $provider
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function findByProvider(Provider $provider): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('l.*', 'p.description AS providerPrice', 'o.name AS opt')
            ->from('linkOpt', 'l')
            ->innerJoin('l', 'providerPrices', 'p', 'l.providerPriceID = p.providerPriceID')
            ->innerJoin('l', 'opt', 'o', 'l.optID = o.optID')
            ->where('p.providerID = :providerID')
            ->setParameter('providerID', $provider->getId())
            ->orderBy('providerPrice')
            ->addOrderBy('o.number')->executeQuery()->fetchAllAssociative();

        $arr = [];

        if ($qb) {
            foreach ($qb as $item) {
                $arr[$item['providerPriceID']][$item['optID']] = $item;
            }
        }

        return $arr;
    }

    /**
     * @param int $providerPriceID
     * @param int $optID
     * @return float
     * @throws \Doctrine\DBAL\Exception
     */
    public function findProfitByProviderPriceAndOpt(int $providerPriceID, int $optID): float
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('l.profit')
            ->from('linkOpt', 'l')
            ->andWhere('l.providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPriceID)
            ->andWhere('l.optID = :optID')
            ->setParameter('optID', $optID)
            ->setMaxResults(1);

        return $qb->executeQuery()->fetchOne();
    }
}