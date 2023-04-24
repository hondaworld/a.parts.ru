<?php

namespace App\Model\Manager\Entity\NewsAdmin;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewsAdmin|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewsAdmin|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewsAdmin[]    findAll()
 * @method NewsAdmin[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NewsAdminRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, NewsAdmin::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return NewsAdmin
     */
    public function get(int $id): NewsAdmin
    {
        if (!$newsAdmin = $this->find($id)) {
            throw new EntityNotFoundException('Новость не найдена');
        }

        return $newsAdmin;
    }

    public function add(NewsAdmin $newsAdmin): void
    {
        $this->em->persist($newsAdmin);
    }
}
