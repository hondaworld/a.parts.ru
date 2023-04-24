<?php


namespace App\ReadModel\Finance;


use App\Model\Finance\Entity\Currency\Currency;
use Doctrine\ORM\EntityManagerInterface;

class CurrencyFetcher
{
    private $connection;
    private $currency;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->currency = $em->getRepository(Currency::class);
    }

    public function get(int $id): Currency
    {
        return $this->currency->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'currencyID',
                'name_short'
            )
            ->from('currency')
            ->orderBy('name_short')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('c.*')
            ->addSelect('(SELECT rate FROM currencyRate WHERE currencyID_to = c.currencyID AND currencyID = (SELECT currencyID FROM currency WHERE isNational = 1) ORDER BY dateofadded DESC LIMIT 1) AS last_rate')
            ->addSelect('(SELECT dateofadded FROM currencyRate WHERE currencyID_to = c.currencyID AND currencyID = (SELECT currencyID FROM currency WHERE isNational = 1) ORDER BY dateofadded DESC LIMIT 1) AS last_date')
            ->from('currency', 'c')
            ->orderBy('c.name_short')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}