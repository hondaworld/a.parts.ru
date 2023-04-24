<?php

namespace App\Model\Manager\Entity\Manager;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Manager|null find($id, $lockMode = null, $lockVersion = null)
 * @method Manager|null findOneBy(array $criteria, array $orderBy = null)
 * @method Manager[]    findAll()
 * @method Manager[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ManagerRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        $this->em = $em;
        parent::__construct($registry, Manager::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     * @param int $id
     * @return Manager
     */
    public function get(int $id): Manager
    {
        if (!$manager = $this->find($id)) {
            throw new EntityNotFoundException('Менеджер не найден');
        }

        return $manager;
    }

    public function hasByLogin(string $login, int $id = 0): bool
    {
        $query = $this->createQueryBuilder('m')
            ->select('COUNT(m.managerID)')
            ->andWhere('m.login = :login')
            ->setParameter('login', $login);

        if ($id) {
            $query->andWhere('m.managerID <> :id')->setParameter('id', $id);
        }

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    public function getSuperAdmin(): Manager
    {
        return $this->find(1);
    }

    public function add(Manager $manager): void
    {
        $this->em->persist($manager);
    }
}
