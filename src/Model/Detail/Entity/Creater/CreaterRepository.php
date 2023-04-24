<?php

namespace App\Model\Detail\Entity\Creater;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Creater|null find($id, $lockMode = null, $lockVersion = null)
 * @method Creater|null findOneBy(array $criteria, array $orderBy = null)
 * @method Creater[]    findAll()
 * @method Creater[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CreaterRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Creater::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Creater
     */
    public function get(int $id): Creater
    {
        if (!$creater = $this->find($id)) {
            throw new EntityNotFoundException('Производитель не найден');
        }

        return $creater;
    }

    public function add(Creater $creater): void
    {
        $this->em->persist($creater);
    }

    public function all(): array
    {
        $creaters = [];
        foreach ($this->findAll() as $item) {
            $creaters[$item->getId()] = $item;
        }
        return $creaters;
    }
}
