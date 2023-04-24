<?php

namespace App\Model\Beznal\Entity\Beznal;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Beznal|null find($id, $lockMode = null, $lockVersion = null)
 * @method Beznal|null findOneBy(array $criteria, array $orderBy = null)
 * @method Beznal[]    findAll()
 * @method Beznal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BeznalRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Beznal::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Beznal
     */
    public function get(int $id): Beznal
    {
        if (!$beznal = $this->find($id)) {
            throw new EntityNotFoundException('Реквизит не найден');
        }

        return $beznal;
    }

    public function add(Beznal $beznal): void
    {
        $this->em->persist($beznal);
    }
}
