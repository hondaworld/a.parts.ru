<?php

namespace App\Model\Finance\Entity\Nalog;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Model\EntityNotFoundException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Nalog|null find($id, $lockMode = null, $lockVersion = null)
 * @method Nalog|null findOneBy(array $criteria, array $orderBy = null)
 * @method Nalog[]    findAll()
 * @method Nalog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NalogRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Nalog::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Nalog
     */
    public function get(int $id): Nalog
    {
        if (!$nalog = $this->find($id)) {
            throw new EntityNotFoundException('Налоговая схема не найдена');
        }

        return $nalog;
    }

    public function add(Nalog $nalog): void
    {
        $this->em->persist($nalog);
    }
}
