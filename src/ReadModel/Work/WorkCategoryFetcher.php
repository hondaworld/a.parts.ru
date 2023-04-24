<?php


namespace App\ReadModel\Work;


use App\Model\Work\Entity\Category\WorkCategory;
use Doctrine\ORM\EntityManagerInterface;

class WorkCategoryFetcher
{
    private $connection;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'number';
    public const DEFAULT_SORT_DIRECTION = 'asc';

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(WorkCategory::class);
    }

    public function get(int $id): WorkCategory
    {
        return $this->repository->get($id);
    }

    public function assoc(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('workCategoryID, name')
            ->from('workCategory')
            ->orderBy('number');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    /**
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function all(): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                'workCategoryID',
                'name',
                'number',
            )
            ->from('workCategory', 'wc');

        $sort = $settings['number'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;

        if (!in_array($sort, ['number'], true)) {
            $sort = 'number';
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $qb->fetchAllAssociative();
    }

}