<?php

namespace App\Model\Ticket\Entity\ClientTicket;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClientTicket|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientTicket|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientTicket[]    findAll()
 * @method ClientTicket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientTicketRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ClientTicket::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ClientTicket
     */
    public function get(int $id): ClientTicket
    {
        if (!$ticket = $this->find($id)) {
            throw new EntityNotFoundException('Тикет не найден');
        }

        return $ticket;
    }

    /**
     * @return ClientTicket[]
     */
    public function findNotClosed(): array
    {
        $date = (new \DateTime())->modify('-7 days');

        $qb = $this->createQueryBuilder('t')
            ->andWhere("t.dateofclosed IS NULL")
            ->andWhere("t.dateofanswer < :date")
            ->setParameter('date', $date)
            ->andWhere('t.answer <> -1');

        return $qb->getQuery()->getResult();
    }

    public function add(ClientTicket $ticket): void
    {
        $this->em->persist($ticket);
    }
}
