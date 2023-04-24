<?php


namespace App\ReadModel\Ticket;


use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Ticket\Entity\ClientTicketGroup\ClientTicketGroup;
use Doctrine\ORM\EntityManagerInterface;

class ClientTicketGroupFetcher
{
    private $connection;
    private $clientTicketGroupRepository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->clientTicketGroupRepository = $em->getRepository(ClientTicketGroup::class);
    }

    public function get(int $id): ClientTicketGroup
    {
        return $this->clientTicketGroupRepository->get($id);
    }

    public function assoc(): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'groupID',
                'name'
            )
            ->from('client_ticket_groups')
            ->orderBy('name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

    public function assocForManager(Manager $manager): array
    {
        $stmt = $this->connection->createQueryBuilder()
            ->select(
                'g.groupID',
                'g.name'
            )
            ->from('client_ticket_groups', 'g')
            ->innerJoin('g', 'client_ticket_group_managers', 'gm', 'g.groupID = gm.groupID')
            ->andWhere('gm.managerID = :managerID')
            ->setParameter('managerID', $manager->getId())
            ->orderBy('g.name')
            ->executeQuery();

        return $stmt->fetchAllKeyValue();
    }

}