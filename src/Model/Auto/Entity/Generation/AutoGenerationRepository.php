<?php

namespace App\Model\Auto\Entity\Generation;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AutoGeneration|null find($id, $lockMode = null, $lockVersion = null)
 * @method AutoGeneration|null findOneBy(array $criteria, array $orderBy = null)
 * @method AutoGeneration[]    findAll()
 * @method AutoGeneration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutoGenerationRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, AutoGeneration::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return AutoGeneration
     */
    public function get(int $id): AutoGeneration
    {
        if (!$autoEngine = $this->find($id)) {
            throw new EntityNotFoundException('Поколение не найдено');
        }

        return $autoEngine;
    }

    public function add(AutoGeneration $autoEngine): void
    {
        $this->em->persist($autoEngine);
    }
}
