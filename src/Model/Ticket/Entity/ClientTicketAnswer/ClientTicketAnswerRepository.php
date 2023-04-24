<?php

namespace App\Model\Ticket\Entity\ClientTicketAnswer;

use App\Model\EntityNotFoundException;
use App\Model\Ticket\Entity\ClientTicket\ClientTicket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClientTicketAnswer|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientTicketAnswer|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientTicketAnswer[]    findAll()
 * @method ClientTicketAnswer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientTicketAnswerRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ClientTicketAnswer::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ClientTicketAnswer
     */
    public function get(int $id): ClientTicketAnswer
    {
        if (!$answer = $this->find($id)) {
            throw new EntityNotFoundException('Ответ не найден');
        }

        return $answer;
    }

    public function add(ClientTicketAnswer $answer): void
    {
        $this->em->persist($answer);
    }

    public function findLastUserAnswer(ClientTicket $ticket): ?ClientTicketAnswer
    {
        return $this->findOneBy(['ticket' => $ticket, 'manager' => -1], ['dateofadded' => 'desc']);
    }
}
