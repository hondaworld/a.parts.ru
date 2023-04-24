<?php


namespace App\ReadModel\Firm;


use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Firm\Entity\ManagerFirm\ManagerFirm;
use App\Model\Manager\Entity\Manager\Manager;
use Doctrine\ORM\EntityManagerInterface;

class ManagerFirmFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ManagerFirm ::class);
    }

    public function get(int $id): ManagerFirm
    {
        return $this->repository->get($id);
    }

    public function allByFirm(Firm $firm): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'l.linkID',
                'if (l.dateofadded = 0, null, l.dateofadded) AS dateofadded',
                'if (l.dateofclosed = 0, null, l.dateofclosed) AS dateofclosed',
                'm.name AS manager',
                'og.name AS org_group',
                'oj.name AS org_job',
            )
            ->from('linkManagerFirm', 'l')
            ->innerJoin('l', 'managers', 'm', 'm.managerID = l.managerID')
            ->innerJoin('l', 'org_groups', 'og', 'og.org_groupID = l.org_groupID')
            ->innerJoin('l', 'org_jobs', 'oj', 'oj.org_jobID = l.org_jobID')
            ->orderBy('m.name')
            ->where('l.firmID = :firmID')
            ->setParameter('firmID', $firm->getId())
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

    public function allByManager(Manager $manager): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'l.linkID',
                'if (l.dateofadded = 0, null, l.dateofadded) AS dateofadded',
                'if (l.dateofclosed = 0, null, l.dateofclosed) AS dateofclosed',
                'f.name AS firm',
                'og.name AS org_group',
                'oj.name AS org_job',
            )
            ->from('linkManagerFirm', 'l')
            ->innerJoin('l', 'firms', 'f', 'f.firmID = l.firmID')
            ->innerJoin('l', 'org_groups', 'og', 'og.org_groupID = l.org_groupID')
            ->innerJoin('l', 'org_jobs', 'oj', 'oj.org_jobID = l.org_jobID')
            ->orderBy('f.name')
            ->where('l.managerID = :managerID')
            ->setParameter('managerID', $manager->getId())
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}