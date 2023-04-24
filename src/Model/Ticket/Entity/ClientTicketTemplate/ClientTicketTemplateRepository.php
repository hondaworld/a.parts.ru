<?php

namespace App\Model\Ticket\Entity\ClientTicketTemplate;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ClientTicketTemplate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ClientTicketTemplate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ClientTicketTemplate[]    findAll()
 * @method ClientTicketTemplate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ClientTicketTemplateRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ClientTicketTemplate::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ClientTicketTemplate
     */
    public function get(int $id): ClientTicketTemplate
    {
        if (!$template = $this->find($id)) {
            throw new EntityNotFoundException('Шаблон тикетов не найден');
        }

        return $template;
    }

    public function add(ClientTicketTemplate $template): void
    {
        $this->em->persist($template);
    }
}
