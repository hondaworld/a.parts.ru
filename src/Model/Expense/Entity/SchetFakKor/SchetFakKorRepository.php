<?php

namespace App\Model\Expense\Entity\SchetFakKor;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method SchetFakKor|null find($id, $lockMode = null, $lockVersion = null)
 * @method SchetFakKor|null findOneBy(array $criteria, array $orderBy = null)
 * @method SchetFakKor[]    findAll()
 * @method SchetFakKor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SchetFakKorRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, SchetFakKor::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return SchetFakKor
     */
    public function get(int $id): SchetFakKor
    {
        if (!$schetFakKor = $this->find($id)) {
            throw new EntityNotFoundException('Корректировочная счет-фактура не найдена');
        }

        return $schetFakKor;
    }

    public function add(SchetFakKor $schetFakKor): void
    {
        $this->em->persist($schetFakKor);
    }
}
