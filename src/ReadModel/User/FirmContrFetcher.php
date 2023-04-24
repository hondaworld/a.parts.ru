<?php


namespace App\ReadModel\User;


use App\Model\EntityNotFoundException;
use App\Model\User\Entity\FirmContr\FirmContr;
use App\ReadModel\User\Filter\FirmContr\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use PDO;

class FirmContrFetcher
{
    private $connection;
    private $paginator;
    private $firmContrRepository;

    public const DEFAULT_SORT_FIELD_NAME = 'organization';
    public const DEFAULT_SORT_DIRECTION = 'asc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->firmContrRepository = $em->getRepository(FirmContr::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): FirmContr
    {
        if (!$firmContr = $this->firmContrRepository->find($id)) {
            throw new EntityNotFoundException('Контрагент не найден');
        }
        return $firmContr;
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('firmcontrID', 'organization')
            ->from('firmcontr')
            ->orderBy('organization');

        return $qb->executeQuery()->fetchAllKeyValue();
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
                'f.firmcontrID',
                'f.organization',
                'f.isHide',
            )
            ->from('firmcontr', 'f')
            ;

        if ($filter->organization) {
            $qb->andWhere($qb->expr()->like('f.organization', ':organization'));
            $qb->setParameter('organization', '%' . mb_strtolower($filter->organization) . '%');
        }

        $sort = isset($settings['sort']) ? $settings['sort'] : self::DEFAULT_SORT_FIELD_NAME;
        $direction = isset($settings['direction']) ? $settings['direction'] : self::DEFAULT_SORT_DIRECTION;
        $size = isset($settings['inPage']) ? $settings['inPage'] : self::PER_PAGE;

        if (!in_array($sort, ['organization'], true)) {
            $sort = self::DEFAULT_SORT_FIELD_NAME;
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }
}