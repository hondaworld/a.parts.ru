<?php

namespace App\Model\Order\Entity\Site;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Site|null find($id, $lockMode = null, $lockVersion = null)
 * @method Site|null findOneBy(array $criteria, array $orderBy = null)
 * @method Site[]    findAll()
 * @method Site[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Site::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Site
     */
    public function get(int $id): Site
    {
        if (!$site = $this->find($id)) {
            throw new EntityNotFoundException('Сайт не найден');
        }

        return $site;
    }

    public function add(Site $site): void
    {
        $this->em->persist($site);
    }
}
