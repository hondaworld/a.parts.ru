<?php

namespace App\Model\Provider\Entity\LogInvoiceAll;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method LogInvoiceAll|null find($id, $lockMode = null, $lockVersion = null)
 * @method LogInvoiceAll|null findOneBy(array $criteria, array $orderBy = null)
 * @method LogInvoiceAll[]    findAll()
 * @method LogInvoiceAll[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LogInvoiceAllRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, LogInvoiceAll::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return LogInvoiceAll
     */
    public function get(int $id): LogInvoiceAll
    {
        if (!$logInvoiceAll = $this->find($id)) {
            throw new EntityNotFoundException('Лог не найден');
        }

        return $logInvoiceAll;
    }

    public function add(LogInvoiceAll $logInvoiceAll): void
    {
        $this->em->persist($logInvoiceAll);
    }
}
