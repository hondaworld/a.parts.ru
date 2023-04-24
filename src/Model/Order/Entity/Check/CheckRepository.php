<?php

namespace App\Model\Order\Entity\Check;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Check|null find($id, $lockMode = null, $lockVersion = null)
 * @method Check|null findOneBy(array $criteria, array $orderBy = null)
 * @method Check[]    findAll()
 * @method Check[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CheckRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Check::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Check
     */
    public function get(int $id): Check
    {
        if (!$check = $this->find($id)) {
            throw new EntityNotFoundException('Чек не найден');
        }

        return $check;
    }

    public function add(Check $check): void
    {
        $this->em->persist($check);
    }
}
