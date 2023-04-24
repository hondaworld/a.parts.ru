<?php


namespace App\ReadModel\Firm;


use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\OrgJob\OrgJob;
use Doctrine\ORM\EntityManagerInterface;

class OrgJobFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(OrgJob ::class);
    }

    public function get(int $id): OrgJob
    {
        return $this->repository->get($id);
    }

    public function assoc(int $org_jobID = null): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'org_jobID',
                'name',
            )
            ->from('org_jobs')
            ->where('isHide = 0')
            ->orderBy('name');

        if ($org_jobID) {
            $stmt->orWhere('org_jobID = :org_jobID')
                ->setParameter('org_jobID', $org_jobID);
        };

        return $stmt->executeQuery()->fetchAllKeyValue();
    }

    public function all(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'org_jobID',
                'i.name',
                'i.isHide',
                'i.isMain'
            )
            ->from('org_jobs', 'i')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}