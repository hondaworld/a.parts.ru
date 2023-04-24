<?php


namespace App\ReadModel\Reseller;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Reseller\Entity\Avito\AvitoNotice;
use App\ReadModel\Reseller\Filter\AvitoNotice\Filter;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class AvitoNoticeFetcher
{
    private $connection;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'oem';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(AvitoNotice::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): AvitoNotice
    {
        return $this->repository->get($id);
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function assocMakes(): array
    {
        return $this->connection->createQueryBuilder()
            ->select('m.id', 'm.name')
            ->from('avito_makes', 'm')
            ->orderBy('m.name')
            ->executeQuery()
            ->fetchAllKeyValue();
    }

    /**
     * @param int $make
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function assocModels(int $make): array
    {
        return $this->connection->createQueryBuilder()
            ->select('m.id', 'm.name')
            ->from('avito_models', 'm')
            ->where('m.avito_make_id = :avito_make_id')
            ->setParameter('avito_make_id', $make)
            ->orderBy('m.name')
            ->executeQuery()
            ->fetchAllKeyValue();
    }

    /**
     * @param int $model
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function assocGenerations(int $model): array
    {
        return $this->connection->createQueryBuilder()
            ->select('g.id', 'g.name')
            ->from('avito_generations', 'g')
            ->where('g.avito_model_id = :avito_model_id')
            ->setParameter('avito_model_id', $model)
            ->orderBy('g.name')
            ->executeQuery()
            ->fetchAllKeyValue();
    }

    /**
     * @param int $generation
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function assocModifications(int $generation): array
    {
        return $this->connection->createQueryBuilder()
            ->select('m.id', 'm.name')
            ->from('avito_modifications', 'm')
            ->where('m.avito_generation_id = :avito_generation_id')
            ->setParameter('avito_generation_id', $generation)
            ->orderBy('m.name')
            ->executeQuery()
            ->fetchAllKeyValue();
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
                'an.id',
                'an.zapCardID',
                'an.oem',
                'an.brand',
                'an.title',
                'an.type_id',
                'an.avito_id',
                "CONCAT (ifNull(ma.name, ''), ' ', ifNull(mo.name, ''), ' ', ifNull(gen.name, ''), ' ', ifNull(modif.name, '')) AS auto",
                'ma.name AS make_name',
                'mo.name AS model_name',
                'gen.name AS generation_name',
                'modif.name AS modification_name',
            )
            ->from('avito_notices', 'an')
            ->leftJoin('an', 'avito_makes', 'ma', 'an.make = ma.id')
            ->leftJoin('an', 'avito_models', 'mo', 'an.model = mo.id')
            ->leftJoin('an', 'avito_generations', 'gen', 'an.generation = gen.id')
            ->leftJoin('an', 'avito_modifications', 'modif', 'an.modification = modif.id');

        if ($filter->oem) {
            $number = (new DetailNumber($filter->oem))->getValue();
            $qb->andWhere($qb->expr()->like('an.oem', ':oem'));
            $qb->setParameter('oem', '%' . $number . '%');
        }

        if ($filter->brand) {
            $qb->andWhere($qb->expr()->like('an.brand', ':brand'));
            $qb->setParameter('brand', '%' . $filter->brand . '%');
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['oem', 'brand'], true)) {
            $sort = 'oem';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    /**
     * @param int $zapSkladID
     * @return array
     * @throws Exception
     */
    public function allForExcel(int $zapSkladID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'an.*',
                'ma.name AS make_name',
                'mo.name AS model_name',
                'gen.name AS generation_name',
                'modif.name AS modification_name'
            )
//            ->addSelect(
//                'ifNull((SELECT Sum(b.quantityIn + b.quantityPath - b.reserve) FROM income i INNER JOIN income_sklad b ON i.incomeID = b.incomeID WHERE i.zapCardID = an.zapCardID AND b.zapSkladID = :zapSkladID AND i.status = :status), 0) AS quantity'
//            )
            ->addSelect(
                'ifNull((SELECT Sum(i.quantityIn + i.quantityPath - i.reserve) FROM income i WHERE i.zapCardID = an.zapCardID AND i.status = :status), 0) AS quantity'
            )
            ->from('avito_notices', 'an')
            ->leftJoin('an', 'avito_makes', 'ma', 'an.make = ma.id')
            ->leftJoin('an', 'avito_models', 'mo', 'an.model = mo.id')
            ->leftJoin('an', 'avito_generations', 'gen', 'an.generation = gen.id')
            ->leftJoin('an', 'avito_modifications', 'modif', 'an.modification = modif.id')
//            ->setParameter('zapSkladID', $zapSkladID)
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
            ->where("an.type_id <>''")
            ->orderBy('an.id');

        return $qb->executeQuery()->fetchAllAssociative();
    }
}