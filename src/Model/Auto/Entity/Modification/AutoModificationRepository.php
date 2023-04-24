<?php

namespace App\Model\Auto\Entity\Modification;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method AutoModification|null find($id, $lockMode = null, $lockVersion = null)
 * @method AutoModification|null findOneBy(array $criteria, array $orderBy = null)
 * @method AutoModification[]    findAll()
 * @method AutoModification[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AutoModificationRepository extends ServiceEntityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, AutoModification::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return AutoModification
     */
    public function get(int $id): AutoModification
    {
        if (!$autoModification = $this->find($id)) {
            throw new EntityNotFoundException('Модификация не найдена');
        }

        return $autoModification;
    }

    public function add(AutoModification $autoModification): void
    {
        $this->em->persist($autoModification);
    }
}
