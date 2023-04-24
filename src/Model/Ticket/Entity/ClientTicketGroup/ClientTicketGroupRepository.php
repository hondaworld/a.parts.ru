<?php

namespace App\Model\Ticket\Entity\ClientTicketGroup;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClientTicketGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientTicketGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientTicketGroup[]    findAll()
 * @method ClientTicketGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientTicketGroupRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ClientTicketGroup::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ClientTicketGroup
     */
    public function get(int $id): ClientTicketGroup
    {
        if (!$group = $this->find($id)) {
            throw new EntityNotFoundException('Группа тикетов не найдена');
        }

        return $group;
    }

    public function add(ClientTicketGroup $group): void
    {
        $this->em->persist($group);
    }
}
