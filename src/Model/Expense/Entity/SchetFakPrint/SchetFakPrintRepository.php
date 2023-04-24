<?php

namespace App\Model\Expense\Entity\SchetFakPrint;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SchetFakPrint|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchetFakPrint|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchetFakPrint[]    findAll()
 * @method SchetFakPrint[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchetFakPrintRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, SchetFakPrint::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return SchetFakPrint
     */
    public function get(int $id): SchetFakPrint
    {
        if (!$schetFakPrint = $this->find($id)) {
            throw new EntityNotFoundException('Счет-фактура не найдена');
        }

        return $schetFakPrint;
    }

    public function add(SchetFakPrint $schetFakPrint): void
    {
        $this->em->persist($schetFakPrint);
    }
}
