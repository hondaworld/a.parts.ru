<?php


namespace App\ReadModel\User;


use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use Doctrine\ORM\EntityManagerInterface;

class TemplateFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(Template::class);
    }

    public function get(int $id): Template
    {
        return $this->repository->get($id);
    }

    public function assocByGroup(int $templateGroupID): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('templateID, name')
            ->from('templates')
            ->where('templateGroupID = :templateGroupID')
            ->setParameter('templateGroupID', $templateGroupID)
            ->orderBy('templateID');

        return $qb->executeQuery()->fetchAllKeyValue();
    }

    public function allByGroup(TemplateGroup $templateGroup): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select('t.*')
            ->from('templates', 't')
            ->where('t.templateGroupID = :templateGroupID')
            ->setParameter('templateGroupID', $templateGroup->getId())
            ->orderBy('t.templateID')
            ->executeQuery();

        return $stmt->fetchAllAssociative();
    }

}