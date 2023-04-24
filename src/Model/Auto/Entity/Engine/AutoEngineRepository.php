<?php

namespace App\Model\Auto\Entity\Engine;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AutoEngine|null find($id, $lockMode = null, $lockVersion = null)
 * @method AutoEngine|null findOneBy(array $criteria, array $orderBy = null)
 * @method AutoEngine[]    findAll()
 * @method AutoEngine[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutoEngineRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, AutoEngine::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return AutoEngine
     */
    public function get(int $id): AutoEngine
    {
        if (!$autoEngine = $this->find($id)) {
            throw new EntityNotFoundException('Двигатель не найден');
        }

        return $autoEngine;
    }

    public function add(AutoEngine $autoEngine): void
    {
        $this->em->persist($autoEngine);
    }
}
