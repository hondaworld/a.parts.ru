<?php

namespace App\Model\Provider\Entity\Provider;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Provider|null find($id, $lockMode = null, $lockVersion = null)
 * @method Provider|null findOneBy(array $criteria, array $orderBy = null)
 * @method Provider[]    findAll()
 * @method Provider[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProviderRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Provider::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Provider
     */
    public function get(int $id): Provider
    {
        if (!$provider = $this->find($id)) {
            throw new EntityNotFoundException('Поставщик не найден');
        }

        return $provider;
    }

    public function add(Provider $provider): void
    {
        $this->em->persist($provider);
    }
}
