<?php

namespace App\Model\Income\Entity\Order;

use App\Model\EntityNotFoundException;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method IncomeOrder|null find($id, $lockMode = null, $lockVersion = null)
 * @method IncomeOrder|null findOneBy(array $criteria, array $orderBy = null)
 * @method IncomeOrder[]    findAll()
 * @method IncomeOrder[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncomeOrderRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, IncomeOrder::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return IncomeOrder
     */
    public function get(int $id): IncomeOrder
    {
        if (!$incomeOrder = $this->find($id)) {
            throw new EntityNotFoundException('Заказ прихода не найден');
        }

        return $incomeOrder;
    }

    public function hasNextDocumentNum(IncomeOrder $incomeOrder): bool
    {
        $query = $this->createQueryBuilder('io')
            ->select('COUNT(io.incomeOrderID)')
            ->andWhere('io.provider = :provider')
            ->setParameter('provider', $incomeOrder->getProvider())
            ->andWhere('io.document_num > :document_num')
            ->setParameter('document_num', $incomeOrder->getDocumentNum());
        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * @param Provider $provider
     * @param ZapSklad $zapSklad
     * @return IncomeOrder|null
     */
    public function getNotOrderedByProviderAndZapSklad(Provider $provider, ZapSklad $zapSklad): ?IncomeOrder
    {
        return $this->findOneBy(['provider' => $provider, 'zapSklad' => $zapSklad, 'isOrdered' => false]);
    }

    public function add(IncomeOrder $incomeOrder): void
    {
        $this->em->persist($incomeOrder);
    }
}
