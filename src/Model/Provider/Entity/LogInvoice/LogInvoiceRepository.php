<?php

namespace App\Model\Provider\Entity\LogInvoice;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LogInvoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogInvoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogInvoice[]    findAll()
 * @method LogInvoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogInvoiceRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, LogInvoice::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return LogInvoice
     */
    public function get(int $id): LogInvoice
    {
        if (!$logInvoice = $this->find($id)) {
            throw new EntityNotFoundException('Лог не найден');
        }

        return $logInvoice;
    }

    public function add(LogInvoice $logInvoice): void
    {
        $this->em->persist($logInvoice);
    }
}
