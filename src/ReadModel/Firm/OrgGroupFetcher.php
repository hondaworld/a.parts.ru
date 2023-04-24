<?php


namespace App\ReadModel\Firm;


use App\Model\Firm\Entity\OrgGroup\OrgGroup;
use Doctrine\ORM\EntityManagerInterface;

class OrgGroupFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(OrgGroup ::class);
    }

    public function get(int $id): OrgGroup
    {
        return $this->repository->get($id);
    }

    public function assoc(int $org_groupID = null): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'org_groupID',
                'name',
            )
            ->from('org_groups')
            ->where('isHide = 0')
            ->orderBy('name');

        if ($org_groupID) {
            $stmt->orWhere('org_groupID = :org_groupID')
                ->setParameter('org_groupID', $org_groupID);
        };

        return $stmt->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'org_groupID',
                'i.name',
                'i.isHide',
                'i.isMain'
            )
            ->from('org_groups', 'i')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}