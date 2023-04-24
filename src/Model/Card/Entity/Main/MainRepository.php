<?php

namespace App\Model\Card\Entity\Main;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Main|null find($id, $lockMode = null, $lockVersion = null)
 * @method Main|null findOneBy(array $criteria, array $orderBy = null)
 * @method Main[]    findAll()
 * @method Main[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MainRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Main::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Main
     */
    public function get(int $id): Main
    {
        if (!$main = $this->find($id)) {
            throw new EntityNotFoundException('Запись не найдена');
        }

        return $main;
    }

    /**
     * @return Main
     */
    public function getSettings(): Main
    {
        return $this->get(Main::DEFAULT_ID);
    }
}
