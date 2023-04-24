<?php


namespace App\ReadModel\Sklad;


use App\Model\Sklad\Entity\Opt\PriceListOpt;
use App\Model\Sklad\Entity\PriceList\PriceList;
use Doctrine\ORM\EntityManagerInterface;

class PriceListOptFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(PriceListOpt::class);
    }

    /**
     * @param PriceList $priceList
     * @return array
     */
    public function findByPriceList(PriceList $priceList): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('l.*', 'p.name AS priceList', 'o.name AS opt')
            ->from('linkPrice_list', 'l')
            ->innerJoin('l', 'price_lists', 'p', 'l.price_listID = p.price_listID')
            ->innerJoin('l', 'opt', 'o', 'l.optID = o.optID')
            ->where('p.price_listID = :price_listID')
            ->setParameter('price_listID', $priceList->getId())
            ->orderBy('priceList')
            ->addOrderBy('o.number')->executeQuery()->fetchAllAssociative();

        $arr = [];

        if ($qb) {
            foreach ($qb as $item) {
                $arr[$item['optID']] = $item;
            }
        }

        return $arr;
    }
}