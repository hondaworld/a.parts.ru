<?php


namespace App\ReadModel\Ticket;


use App\Model\EntityNotFoundException;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Ticket\Entity\ClientTicket\ClientTicket;
use App\ReadModel\Ticket\Filter\ClientTicket\Filter;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

class ClientTicketFetcher
{
    private $connection;
    private $paginator;
    private $repository;

    public const DEFAULT_SORT_FIELD_NAME = 'dateofanswer';
    public const DEFAULT_SORT_DIRECTION = 'desc';
    public const PER_PAGE = 20;

    public function __construct(EntityManagerInterface $em, PaginatorInterface $paginator)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ClientTicket::class);
        $this->paginator = $paginator;
    }

    public function get(int $id): ClientTicket
    {

        if (!$clientTicket = $this->repository->find($id)) {
            throw new EntityNotFoundException('Запись не найдена');
        }

        return $clientTicket;
    }

    public function getNextDocumentNumber(): int
    {
        $documentNumber = $this->connection->createQueryBuilder()
            ->select('ticket_num')
            ->from('client_tickets', 't')
            ->orderBy('ticket_num', 'desc')
            ->executeQuery()
            ->fetchOne();

        if (!$documentNumber) return 1;
        return $documentNumber + 1;
    }

    /**
     * @param Manager $manager
     * @param Filter $filter
     * @param int $page
     * @param array $settings
     * @return PaginationInterface
     * @throws \Exception
     */
    public function all(Manager $manager, Filter $filter, int $page, array $settings): PaginationInterface
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                't.ticketID',
                't.ticket_num',
                't.dateofanswer',
                't.dateofclosed',
                't.managerofclosed',
                't.user_email',
                't.user_subject',
                't.user_name',
                't.managerID',
                't.answer',
                't.isRead',
                'u.name AS client_name',
                's.name_short AS site_name',
                '(SELECT text FROM client_ticket_answers WHERE ticketID = t.ticketID ORDER BY dateofadded DESC LIMIT 1) AS text',
                '(SELECT 1 FROM client_ticket_answers cta INNER JOIN client_ticket_attaches ctat ON cta.answerID = ctat.answerID WHERE ticketID = t.ticketID LIMIT 1) AS attach'
            )
            ->from('client_tickets', 't')
            ->leftJoin('t', 'users', 'u', 't.userID = u.userID')
            ->leftJoin('t', 'client_ticket_groups', 'g', 't.groupID = g.groupID')
            ->leftJoin('t', 'client_ticket_group_managers', 'gm', 't.groupID = gm.groupID')
            ->leftJoin('t', 'sites', 's', 't.siteID = s.siteID')
//            ->leftJoin('b', 'schet', 's', 'b.schetID = s.schetID')
//            ->leftJoin('b', 'expenseDocuments', 'ed', 'b.expenseDocumentID = ed.expenseDocumentID')
            ->where('gm.managerID = :managerID')
            ->setParameter('managerID', $manager->getId())
            ->andWhere('t.dateofdeleted IS NULL')
        ;

        if ($filter->answered) {
            $qb->andWhere(
                $qb->expr()->or(
                    $qb->expr()->like('LOWER(t.user_email)', ':answered'),
                    $qb->expr()->like('LOWER(t.user_name)', ':answered'),
                    $qb->expr()->like('LOWER(u.name)', ':answered'),
                )
            );
            $qb->setParameter('answered', '%' . mb_strtolower($filter->answered) . '%');
        }

        if ($filter->text) {
            $qb->andWhere($qb->expr()->like('LOWER((SELECT text FROM client_ticket_answers WHERE ticketID = t.ticketID ORDER BY dateofadded DESC LIMIT 1))', ':text'));
            $qb->setParameter('text', '%' . mb_strtolower($filter->text) . '%');
        }

        if ($filter->ticket_num) {
            $qb->andWhere('t.ticket_num = :ticket_num');
            $qb->setParameter('ticket_num', $filter->ticket_num);
        }

        if ($filter->groupID) {
            $qb->andWhere('t.groupID = :groupID');
            $qb->setParameter('groupID', $filter->groupID);
        }

        if ($filter->managerClosed) {
            $qb->andWhere('t.managerofclosed = :managerClosed');
            $qb->setParameter('managerClosed', $filter->managerClosed);
        }

        if ($filter->dateofanswer) {
            if ($filter->dateofanswer['date_from']) {
                $qb->andWhere($qb->expr()->gte('t.dateofanswer', ':date_from'));
                $qb->setParameter('date_from', (new \DateTime($filter->dateofanswer['date_from']))->format('Y-m-d 00:00:00'));
            }
            if ($filter->dateofanswer['date_till']) {
                $qb->andWhere($qb->expr()->lte('t.dateofanswer', ':date_till'));
                $qb->setParameter('date_till', (new \DateTime($filter->dateofanswer['date_till']))->format('Y-m-d 23:59:59'));
            }
        }

        $sort = $settings['sort'] ?? self::DEFAULT_SORT_FIELD_NAME;
        $direction = $settings['direction'] ?? self::DEFAULT_SORT_DIRECTION;
        $size = $settings['inPage'] ?? self::PER_PAGE;

        if (!in_array($sort, ['dateofanswer'], true)) {
//            throw new \UnexpectedValueException('Невозможно отсортировать ' . $sort);
            $sort = self::DEFAULT_SORT_FIELD_NAME;
        }

        $qb->orderBy($sort, $direction === 'desc' ? 'desc' : 'asc');

        return $this->paginator->paginate($qb, $page, $size, ['defaultSortFieldName' => $sort, 'defaultSortDirection' => $direction]);
    }

    public function getNewTickets(Manager $manager): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select(
                't.ticketID',
                't.ticket_num',
                't.dateofanswer',
                't.user_subject',
            )
            ->from('client_tickets', 't')
            ->leftJoin('t', 'client_ticket_groups', 'g', 't.groupID = g.groupID')
            ->leftJoin('t', 'client_ticket_group_managers', 'gm', 't.groupID = gm.groupID')
            ->where('gm.managerID = :managerID')
            ->setParameter('managerID', $manager->getId())
            ->andWhere('t.dateofdeleted IS NULL AND isRead = 0')
            ->orderBy('t.dateofanswer')
        ;


        return $qb->executeQuery()->fetchAllAssociative();
    }
}