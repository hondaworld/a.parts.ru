<?php

namespace App\Model\Sklad\Entity\PriceGroup;

use App\Model\EntityNotFoundException;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Sklad\Entity\PriceList\PriceList;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PriceGroup|null find($id, $lockMode = null, $lockVersion = null)
 * @method PriceGroup|null findOneBy(array $criteria, array $orderBy = null)
 * @method PriceGroup[]    findAll()
 * @method PriceGroup[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PriceGroupRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em)
    {
        parent::__construct($registry, PriceGroup::class);
        $this->em = $em;
    }

    /**
     * @param int $id
     * @return PriceGroup
     */
    public function get(int $id): PriceGroup
    {
        if (!$priceGroup = $this->find($id)) {
            throw new EntityNotFoundException('Группа прайс-листа не найдена');
        }

        return $priceGroup;
    }

    /**
     * @return PriceGroup|null
     */
    public function getMain(): ?PriceGroup
    {
        return $this->findOneBy(['isMain' => 1]);
    }

    public function add(PriceGroup $priceGroup): void
    {
        $this->em->persist($priceGroup);
    }

    public function updateMain(PriceList $priceList)
    {
        $this->createQueryBuilder('pg')
            ->update()
            ->set('pg.isMain', '0')
            ->where('pg.price_list = :price_list')
            ->setParameter('price_list', $priceList)
            ->getQuery()
            ->execute();
    }

    public function getForIncomeInWarehouse(ProviderPrice $providerPrice): PriceGroup
    {
        if (in_array($providerPrice->getProvider()->getId(), array(1, 21, 19, 20, 17, 79))) return $this->get(24); /* ВЛД, ЕВРОПА, ОАЭ, США, США МОТО, США ТЮН, Япония 2 */
        else if (in_array($providerPrice->getId(), array(310))) return $this->get(24); /* Восход (TEST) */
        else return $this->get(23);
//        return $this->get(1);
    }
}
