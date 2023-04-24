<?php

namespace App\Model\Auto\Entity\Marka;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AutoMarka|null find($id, $lockMode = null, $lockVersion = null)
 * @method AutoMarka|null findOneBy(array $criteria, array $orderBy = null)
 * @method AutoMarka[]    findAll()
 * @method AutoMarka[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutoMarkaRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, AutoMarka::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return AutoMarka
     */
    public function get(int $id): AutoMarka
    {
        if (!$autoMarka = $this->find($id)) {
            throw new EntityNotFoundException('Марка не найдена');
        }

        return $autoMarka;
    }

    public function add(AutoMarka $autoMarka): void
    {
        $this->em->persist($autoMarka);
    }
}
