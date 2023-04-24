<?php


namespace App\ReadModel\Provider;


use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Provider\Entity\Provider\Provider;
use App\ReadModel\Provider\Filter\Price\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ProviderPriceFetcher
{
    private $connection;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'p.name';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ProviderPrice::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ProviderPrice
    {
        return $this->repository->get($id);
    }

    public function assocRazdDecimal(): array
    {
        return [
            '' => 'Авто',
            '.' => 'Точка',
            ',' => 'Запятая',
        ];
    }

    public function assoc(int $excludeID = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("providerPriceID, CONCAT(name, ' ', description) AS name")
            ->from('providerPrices')
            ->orderBy('description');

        if ($excludeID != null) {
            $qb->where('providerPriceID <> :providerPriceID')->setParameter('providerPriceID', $excludeID);
        }

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocDescriptions(bool $isOnlyNotHide = false): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("providerPriceID, description")
            ->from('providerPrices')
            ->orderBy('description');

        if ($isOnlyNotHide) {
            $qb->where('isHide = 0');
        }

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function allArray(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("*")
            ->from('providerPrices');

        return $qb->executeQuery()->fetchAllAssociativeIndexed();
    }

    public function isProviderPriceStock(int $providerPriceID, int $stockID): bool
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("Count(*)")
            ->from('zapCardStock_providers', 'z')
            ->innerJoin('z', 'providerPrices', 'p', 'z.providerID = p.providerID')
            ->andWhere('z.stockID = :stockID')
            ->setParameter('stockID', $stockID)
            ->andWhere('p.providerPriceID = :providerPriceID')
            ->setParameter('providerPriceID', $providerPriceID);

        return $qb->executeQuery()->fetchOne() > 0;
    }

    public function assocWithProvider(int $providerPriceID = null): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select("providerPriceID, pp.description AS name, p.name AS provider")
            ->from('providerPrices', 'pp')
            ->innerJoin('pp', 'providers', 'p', 'pp.providerID = p.providerID')
            ->where('pp.isHide = 0')
            ->orderBy('p.name, pp.description');

        if ($providerPriceID != null) {
            $qb->orWhere('providerPriceID = :providerPriceID')->setParameter('providerPriceID', $providerPriceID);
        }

        $arr = $qb->executeQuery()->fetchAllAssociative();

        $result = [];

        foreach ($arr as $item) {
            $result[$item['provider']][$item['name']] = $item['providerPriceID'];
        }

        return $result;
    }

    public function assocByProvider(Provider $provider): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('providerPriceID, description')
            ->from('providerPrices')
            ->where('providerID = :providerID')
            ->setParameter('providerID', $provider->getId())
            ->orderBy('description');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function assocClientsHide(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('providerPriceID, description')
            ->from('providerPrices')
            ->where('clients_hide = 1')
            ->orderBy('description');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function findWithPriceEmail(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('price, price_email, email_from, isNotCheckExt')
            ->from('providerPrices')
            ->where("price_email <> ''");

        return $qb->executeQuery()->fetchAllAssociative();
    }

    /**
     * @param Provider $provider
     * @return array
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
            ->addOrderBy('o.number');

        $qb->executeQuery()->fetchAllAssociative();

        $arr = [];

        if ($qb) {
            foreach ($qb as $item) {
                $arr[$item['providerPriceID']][$item['optID']] = $item;
            }
        }

        return $arr;
    }

    public function findUploaded(string $directory): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'r.*',
                'p.name AS provider_name',
                "IFNULL(c.name, 'Все') AS creater"
            )
            ->addSelect('(SELECT 1 FROM shopPriceWorking WHERE price = r.price) AS is_uploading')
            ->from('providerPrices', 'r')
            ->innerJoin('r', 'providers', 'p', 'r.providerID = p.providerID')
            ->leftJoin('r', 'creaters', 'c', 'r.createrID = c.createrID')
            ->executeQuery()
            ->fetchAllAssociative();

        $arr = [];

        if ($qb) {
            foreach ($qb as $item) {
                if ($item['price'] != '' && file_exists($directory . '/' . $item['price'])) {

                    $item['file']['size'] = (filesize($directory . '/' . $item['price']) / 1000) . " kB";
                    $item['file']['date'] = filemtime($directory . '/' . $item['price']);

                    if (isset($arr[$item['providerID']])) {
                        $arr[$item['providerID']]['prices'][] = $item;
                    } else {
                        $arr[$item['providerID']]['name'] = $item['provider_name'];
                        $arr[$item['providerID']]['prices'] = [];
                        $arr[$item['providerID']]['prices'][] = $item;
                    }
                }
            }
        }

        return $arr;
    }

    public function findUploadedId(string $directory): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('r.providerPriceID, r.price')
            ->from('providerPrices', 'r')
            ->where("r.price <> ''")
            ->andWhere('r.price NOT IN (SELECT price FROM shopPriceWorking)')
            ->executeQuery()
            ->fetchAllAssociative();

        $arr = [];

        $arFiles = [];
        $fileList = scandir($directory . '/');
        foreach ($fileList as $fileName) {
            $fileTime = filemtime($directory . '/' . $fileName);
            $arFiles[$fileTime][] = $fileName;
        }
        ksort($arFiles);

        foreach ($arFiles as $arFilesItems) {
            foreach ($arFilesItems as $fileName) {
                if ($qb) {
                    foreach ($qb as $item) {
                        if ($fileName == $item['price']) {
                            $arr[] = $item['providerPriceID'];
                        }
                    }
                }
            }
        }


        return $arr;
    }

    public function findUploadedBadFiles(string $directory): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('r.price')
            ->from('providerPrices', 'r')
            ->where("r.price <> ''")
            ->executeQuery()
            ->fetchFirstColumn();

        $arr = [];

        $arFiles = [];
        $fileList = scandir($directory . '/');
        foreach ($fileList as $fileName) {
            $arFiles[$fileName][] = $fileName;
        }
        ksort($arFiles);

        foreach ($arFiles as $arFilesItems) {
            foreach ($arFilesItems as $fileName) {
                if (!in_array($fileName, $qb) && file_exists($directory . '/' . $fileName) && ($fileName != ".") && ($fileName != "..") && ($fileName != ".ftpquota")) {
                    $arr[] = [
                        'name' => $fileName,
                        'size' => (filesize($directory . '/' . $fileName) / 1000) . " kB",
                        'date' => filemtime($directory . '/' . $fileName),
                    ];

                }
            }
        }
        return $arr;
    }

    public function findUploadedArchiveFiles(string $directory): array
    {
        $arFiles = [];
        $fileList = scandir($directory . '/');
        foreach ($fileList as $fileName) {
            if (($fileName != ".") && ($fileName != "..") && ($fileName != ".ftpquota")) {
                $arFiles[$fileName] = [
                    'name' => $fileName,
                    'size' => (filesize($directory . '/' . $fileName) / 1000) . " kB",
                    'date' => filemtime($directory . '/' . $fileName),
                ];
            }
        }
        ksort($arFiles);

        return $arFiles;
    }

    /**
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     */
    public function all(Filter $filter, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'r.*',
                'p.name AS provider_name',
                'c.name AS currency_name',
            )
            ->from('providerPrices', 'r')
            ->innerJoin('r', 'providers', 'p', 'r.providerID = p.providerID')
            ->innerJoin('r', 'currency', 'c', 'r.currencyID = c.currencyID');

        if ($filter->providerID) {
            $qb->andWhere('r.providerID = :providerID');
            $qb->setParameter('providerID', $filter->providerID);
        }

        if ($filter->name) {
            $qb->andWhere($qb->expr()->like('r.name', ':name'));
            $qb->setParameter('name', '%' . mb_strtolower($filter->name) . '%');
        }

        if ($filter->price) {
            $qb->andWhere($qb->expr()->like('r.price', ':price'));
            $qb->setParameter('price', '%' . mb_strtolower($filter->price) . '%');
        }

        if ($filter->description) {
            $qb->andWhere($qb->expr()->like('r.description', ':description'));
            $qb->setParameter('description', '%' . mb_strtolower($filter->description) . '%');
        }

        if ($filter->showHide !== null && $filter->showHide !== '') {
            if (!$filter->showHide)
                $qb->andWhere('r.isHide = false');
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['provider_name', 'r.name', 'r.description', 'dateofchanged'], true)) {
            $sort = 'r.name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    public function isPriceWorking(ProviderPrice $providerPrice)
    {
        return $this->connection->createQueryBuilder()
            ->select('Count(*) c')
            ->from('shopPriceWorking')
            ->where('price = :price')
            ->setParameter('price', $providerPrice->getPrice()->getPrice())
            ->executeQuery()
            ->fetchOne();
    }
}