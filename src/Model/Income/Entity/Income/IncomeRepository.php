<?php

namespace App\Model\Income\Entity\Income;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\EntityNotFoundException;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Provider\Entity\LogInvoice\LogInvoice;
use App\Model\Provider\Entity\Provider\Provider;
use App\Model\Provider\Entity\ProviderInvoice\ProviderInvoice;
use App\Model\Shop\Entity\Gtd\Gtd;
use App\Model\Shop\Entity\Gtd\ShopGtd;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;
use function Doctrine\ORM\QueryBuilder;

/**
 * @method Income|null find($id, $lockMode = null, $lockVersion = null)
 * @method Income|null findOneBy(array $criteria, array $orderBy = null)
 * @method Income[]    findAll()
 * @method Income[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class IncomeRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, Income::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return Income
     */
    public function get(int $id): Income
    {
        if (!$income = $this->find($id)) {
            throw new EntityNotFoundException('Приход не найден');
        }

        return $income;
    }

    public function add(Income $income): void
    {
        $this->em->persist($income);
    }

    public function remove(Income $income): void
    {
        $this->em->remove($income);
    }

    /**
     * @param Provider $provider
     * @return Income[]
     */
    public function findByProviderIncomeInWarehouse(Provider $provider): array
    {
        $query = $this->createQueryBuilder('i')
            ->select('i')
            ->innerJoin('i.provider_price', 'p')
            ->innerJoin('i.status', 's')
            ->innerJoin('i.zapCard', 'z')
            ->andWhere('p.provider = :provider')
            ->setParameter('provider', $provider)
            ->andWhere('s.status = :status')
            ->setParameter('status', IncomeStatus::INCOME_IN_WAREHOUSE);

        return $query->getQuery()->getResult();
    }

    /**
     * @return Income[]
     */
    public function findIncomeNew(): array
    {
        $query = $this->createQueryBuilder('i')
            ->select('i')
            ->innerJoin('i.provider_price', 'p')
            ->innerJoin('i.status', 's')
            ->innerJoin('i.zapCard', 'z')
            ->andWhere('s.status = :status')
            ->setParameter('status', IncomeStatus::DEFAULT_STATUS);

        return $query->getQuery()->getResult();
    }

    /**
     * @param array $arr
     * @return Income[]
     */
    public function findByIDs(array $arr): array
    {
        $query = $this->createQueryBuilder('i')
            ->select('i')
            ->leftJoin('i.provider_price', 'p')
            ->innerJoin('i.zapCard', 'z')
            ->andWhere('i.incomeID IN (:incomeID)')
            ->setParameter('incomeID', $arr);

        return $query->getQuery()->getResult();
    }

    /**
     * @param ZapCard $zapCard
     * @param ZapSklad $zapSklad
     * @return Income[]
     */
    public function findInWarehouseByZapCardAndZapSklad(ZapCard $zapCard, ZapSklad $zapSklad): array
    {
        $query = $this->createQueryBuilder('i')
            ->select('i', 'isd')
            ->innerJoin('i.sklads', 'isd')
            ->innerJoin('i.status', 's')
            ->andWhere('i.zapCard = :zapCard')
            ->setParameter('zapCard', $zapCard)
            ->andWhere('isd.zapSklad = :zapSklad')
            ->setParameter('zapSklad', $zapSklad)
            ->andWhere('s.status = :status')
            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
            ->andWhere('isd.quantityIn + isd.quantityPath - isd.reserve > 0')
            ->orderBy('i.dateofadded');

        return $query->getQuery()->getResult();
    }

    /**
     * @param LogInvoice $logInvoice
     * @return Income|null
     * @throws NonUniqueResultException
     */
    public function findByLogInvoice(LogInvoice $logInvoice): ?Income
    {
        $query = $this->createQueryBuilder('i')
            ->select('i', 'zc')
            ->innerJoin('i.provider_price', 'pp')
            ->innerJoin('i.zapCard', 'zc')
            ->innerJoin('i.status', 's')
            ->andWhere('pp.provider = :provider')
            ->setParameter('provider', $logInvoice->getProviderInvoice()->getProvider())
            ->andWhere('zc.number = :number')
            ->setParameter('number', $logInvoice->getNumber()->getValue())
            ->andWhere('i.quantity = :quantity')
            ->setParameter('quantity', $logInvoice->getQuantityInvoice())
            ->orderBy('s.number', 'desc')
            ->addOrderBy('i.dateofadded')
            ->setMaxResults(1)
        ;

        $query->andWhere($query->expr()->in('s.status', explode(',', $logInvoice->getProviderInvoice()->getStatusFrom())));

        return $query->getQuery()->getOneOrNullResult();
    }

    /**
     * @param ProviderInvoice $providerInvoice
     * @return Income[]
     */
    public function findByProviderInvoice(ProviderInvoice $providerInvoice): array
    {
        $query = $this->createQueryBuilder('i')
            ->select('i', 'zc')
            ->innerJoin('i.provider_price', 'pp')
            ->innerJoin('i.zapCard', 'zc')
            ->innerJoin('i.status', 's')
            ->andWhere('pp.provider = :provider')
            ->setParameter('provider', $providerInvoice->getProvider())
            ->orderBy('s.number', 'desc')
            ->addOrderBy('i.dateofadded');

        $query->andWhere($query->expr()->in('s.status', explode(',', $providerInvoice->getStatusFrom())));

        return $query->getQuery()->getResult();
    }

    public function updateGtd(int $incomeID, ShopGtd $gtd): void
    {
        $this->createQueryBuilder('i')
            ->update()
            ->set('i.shop_gtd1', $gtd->getId())
            ->andWhere('i.incomeID = :incomeID')
            ->setParameter('incomeID', $incomeID)
            ->getQuery()
            ->execute();
    }

    public function getNotOrderedByZapCard(ZapCard $zapCard): ?Income
    {
//        SELECT incomeID FROM income WHERE zapCardID = '".AddSlashes($zapCardID)."' AND status = 1 AND incomeID NOT IN (SELECT incomeID FROM order_goods WHERE incomeID <> 0)

        $query = $this->createQueryBuilder('i')
            ->select('i')
            ->innerJoin('i.status', 's')
            ->andWhere('i.zapCard = :zapCard')
            ->setParameter('zapCard', $zapCard)
            ->andWhere('s.status = :status')
            ->setParameter('status', IncomeStatus::DEFAULT_STATUS)
            ->leftJoin('i.order_goods', 'og')
            ->andWhere('og.goodID IS NULL')
            ->setMaxResults(1)
        ;
//        $query->expr()->not($query->expr()->exists('SELECT 1 FROM order_goods og WHERE og.incomeID =  incomeID IS NOT NULL'));

        return $query->getQuery()->getOneOrNullResult();
    }

//    /**
//     * @param ZapCard $zapCard
//     * @param ZapSklad $zapSklad
//     * @return int
//     */
//    public function findQuantityInWarehouseByZapCardAndZapSklad(ZapCard $zapCard, ZapSklad $zapSklad): int
//    {
//        $query = $this->createQueryBuilder('i')
//            ->select('Sum(isd.quantityIn + isd.quantityPath - isd.reserve)')
//            ->innerJoin('i.sklads', 'isd')
//            ->innerJoin('i.status', 's')
//            ->andWhere('i.zapCard = :zapCard')
//            ->setParameter('zapCard', $zapCard)
//            ->andWhere('isd.zapSklad = :zapSklad')
//            ->setParameter('zapSklad', $zapSklad)
//            ->andWhere('s.status = :status')
//            ->setParameter('status', IncomeStatus::IN_WAREHOUSE)
//        ;
//
//        return $query->getQuery()->getSingleScalarResult() ?: 0;
//    }
}
