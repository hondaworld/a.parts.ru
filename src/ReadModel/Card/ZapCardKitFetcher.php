<?php


namespace App\ReadModel\Card;


use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Detail\Entity\Kit\ZapCardKit;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ZapCardKitFetcher
{
    private $connection;
    private $paginator;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'sort';
    public const DEFAULT_SORT_DIRECTION = 'asc';

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ZapCardKit ::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ZapCardKit
    {
        return $this->repository->get($id);
    }

    /**
     * @param AutoModel $autoModel
     * @param array $settings
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function all(AutoModel $autoModel, array $settings): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'id',
                'name',
                'sort',
                'isHide',
            )
            ->from('zapCardKits', 'z')
            ->where('auto_modelID = :auto_modelID')
            ->setParameter('auto_modelID', $autoModel->getId())
        ;

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['sort'], true)) {
            $sort = 'sort';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $qb->fetchAllAssociative();
    }

}