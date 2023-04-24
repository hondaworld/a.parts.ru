<?php

namespace App\Model\Expense\Entity\SchetFak;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SchetFak|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchetFak|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchetFak[]    findAll()
 * @method SchetFak[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchetFakRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, SchetFak::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return SchetFak
     */
    public function get(int $id): SchetFak
    {
        if (!$schetFak = $this->find($id)) {
            throw new EntityNotFoundException('Счет-фактура не найдена');
        }

        return $schetFak;
    }

    public function add(SchetFak $schetFak): void
    {
        $this->em->persist($schetFak);
    }
}
