<?php


namespace App\ReadModel\Auto;


use App\Model\Auto\Entity\Auto\Auto;
use App\Model\User\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use App\ReadModel\Auto\Filter\Auto\Filter;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class AutoFetcher
{
    private $connection;
    private $paginator;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'model_name';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Auto ::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): Auto
    {
        return $this->repository->get($id);
    }

    public function allByUser(User $user): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select("a.*, CONCAT(marka.name, ' ', model.name) AS model_name")
            ->from('autos', 'a')
            ->leftJoin('a', 'auto_model', 'model', 'a.auto_modelID = model.auto_modelID')
            ->leftJoin('model', 'auto_marka', 'marka', 'model.auto_markaID = marka.auto_markaID')
            ->innerJoin('a', 'linkUserAuto', 'l', 'a.autoID = l.autoID')
            ->andWhere('l.userID = :userID')
            ->setParameter('userID', $user->getId())
            ->orderBy('model_name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
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
                'a.autoID',
                'TRIM(CONCAT(marka.name, \' \', model.name)) AS model_name',
                'a.vin',
                'a.number',
                'a.year',
                'a.isHide',
            )
            ->from('autos', 'a')
            ->leftJoin('a', 'auto_model', 'model', 'a.auto_modelID = model.auto_modelID')
            ->leftJoin('model', 'auto_marka', 'marka', 'model.auto_markaID = marka.auto_markaID')
        ;

        if ($filter->vin) {
            $qb->andWhere($qb->expr()->like('lower(a.vin)', ':vin'));
            $qb->setParameter('vin', '%' . mb_strtolower($filter->vin) . '%');
        }

        if ($filter->number) {
            $qb->andWhere($qb->expr()->like('lower(a.number)', ':number'));
            $qb->setParameter('number', '%' . mb_strtolower($filter->number) . '%');
        }

        $sort = isset($settings['sort']) ? $settings['sort'] : self::DEFAULT_SORT_FIELD_NAME;
        $direction = isset($settings['direction']) ? $settings['direction'] : self::DEFAULT_SORT_DIRECTION;
        $size = isset($settings['inPage']) ? $settings['inPage'] : self::PER_PAGE;

        if (!in_array($sort, ['model_name', 'vin', 'number'], true)) {
            $sort = 'model_name';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

}