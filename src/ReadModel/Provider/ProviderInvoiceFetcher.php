<?php


namespace App\ReadModel\Provider;


use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;
use Doctrine\ORM\EntityManagerInterface;

class ProviderInvoiceFetcher
{
    private $connection;
    private $repository;

    public function __construct(EntityManagerInterface $em)
    {
        $this->connection = $em->getConnection();
        $this->repository = $em->getRepository(ProviderInvoice::class);
    }

    public function get(int $id): ProviderInvoice
    {
        return $this->repository->get($id);
    }

    /**
     * @param Provider $provider
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function all(Provider $provider): array
    {
        $qb = $this->connection->createQueryBuilder()
            ->select('pi.*')
            ->from('providerInvoices', 'pi')
            ->andWhere('pi.providerID = :providerID')
            ->setParameter('providerID', $provider->getId());

        return $qb->executeQuery()->fetchAllAssociative();
    }
}