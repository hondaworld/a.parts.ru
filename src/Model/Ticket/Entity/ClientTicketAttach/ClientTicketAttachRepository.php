<?php

namespace App\Model\Ticket\Entity\ClientTicketAttach;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClientTicketAttach|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientTicketAttach|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientTicketAttach[]    findAll()
 * @method ClientTicketAttach[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientTicketAttachRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ClientTicketAttach::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ClientTicketAttach
     */
    public function get(int $id): ClientTicketAttach
    {
        if (!$attach = $this->find($id)) {
            throw new EntityNotFoundException('Файл не найден');
        }

        return $attach;
    }

    public function add(ClientTicketAttach $attach): void
    {
        $this->em->persist($attach);
    }
}
