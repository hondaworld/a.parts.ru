<?php

namespace App\Model\Provider\Entity\Price;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProviderPrice|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProviderPrice|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProviderPrice[]    findAll()
 * @method ProviderPrice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProviderPriceRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ProviderPrice::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ProviderPrice
     */
    public function get(int $id): ProviderPrice
    {
        if (!$providerPrice = $this->find($id)) {
            throw new EntityNotFoundException('Прайс не найден');
        }

        return $providerPrice;
    }

    /**
     * @return ProviderPrice[]
     */
    public function findWithPriceEmail(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where("p.price.price_email <> ''");

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string $price
     * @param string $email_from
     * @return ProviderPrice
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByPrice(string $price, string $email_from = ''): ?ProviderPrice
    {
        $qb = $this->createQueryBuilder('p')
            ->where("price like :price AND (email_from = '' OR email_from = :email_from)")
            ->setParameter('price', $price . '%')
            ->setParameter('email_from', $email_from);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @return array
     */
    public function findAllWithPrice(): array
    {
        return $this->createQueryBuilder('p')
            ->where("p.price.price <> ''")
            ->andWhere('p.price.price NOT IN (SELECT price FROM shopPriceWorking)')
            ->getQuery()
            ->getArrayResult();
    }

    public function findByPrice(string $fileName): ?ProviderPrice
    {
        return $this->findOneBy(['price.price' => $fileName]);
    }

    public function add(ProviderPrice $providerPrice): void
    {
        $this->em->persist($providerPrice);
    }
}
