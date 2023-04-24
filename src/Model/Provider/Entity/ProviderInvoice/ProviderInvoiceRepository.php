<?php

namespace App\Model\Provider\Entity\ProviderInvoice;

use App\Model\EntityNotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProviderInvoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProviderInvoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProviderInvoice[]    findAll()
 * @method ProviderInvoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProviderInvoiceRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, ProviderInvoice::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return ProviderInvoice
     */
    public function get(int $id): ProviderInvoice
    {
        if (!$providerInvoice = $this->find($id)) {
            throw new EntityNotFoundException('Инвойс не найден');
        }

        return $providerInvoice;
    }

    /**
     * @return ProviderInvoice[]
     */
    public function findWithPriceEmail(): array
    {
        $qb = $this->createQueryBuilder('p')
            ->where("p.price_email <> '' OR p.email_from <> '' ");

        return $qb->getQuery()->getResult();
    }

    public function add(ProviderInvoice $providerInvoice): void
    {
        $this->em->persist($providerInvoice);
    }
}
