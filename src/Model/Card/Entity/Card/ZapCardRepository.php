<?php

namespace App\Model\Card\Entity\Card;

use App\Model\Card\Entity\Measure\EdIzm;
use App\Model\Card\Entity\Measure\EdIzmRepository;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\EntityNotFoundException;
use App\Model\Shop\Entity\ShopType\ShopTypeRepository;
use App\Model\Sklad\Entity\PriceGroup\PriceGroup;
use App\Model\Sklad\Entity\PriceGroup\PriceGroupRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use DomainException;

/**
 * @method ZapCard|null find($id, $lockMode = null, $lockVersion = null)
 * @method ZapCard|null findOneBy(array $criteria, array $orderBy = null)
 * @method ZapCard[]    findAll()
 * @method ZapCard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ZapCardRepository extends ServiceEntityRepository
{
    private EntityManagerInterface $em;
    private ShopTypeRepository $shopTypeRepository;
    private PriceGroupRepository $priceGroupRepository;
    private EdIzmRepository $edIzmRepository;
    private CreaterRepository $createrRepository;

    public function __construct(ManagerRegistry $registry, EntityManagerInterface $em, ShopTypeRepository $shopTypeRepository, PriceGroupRepository $priceGroupRepository, EdIzmRepository $edIzmRepository, CreaterRepository $createrRepository)
    {
        parent::__construct($registry, ZapCard::class);
        $this->em = $em;
        $this->shopTypeRepository = $shopTypeRepository;
        $this->priceGroupRepository = $priceGroupRepository;
        $this->edIzmRepository = $edIzmRepository;
        $this->createrRepository = $createrRepository;
    }

    /**
     * @param int $id
     * @return ZapCard
     */
    public function get(int $id): ZapCard
    {
        if (!$zapCard = $this->find($id)) {
            throw new EntityNotFoundException('Деталь не найдена');
        }

        return $zapCard;
    }

    /**
     * @param DetailNumber $number
     * @param Creater $creater
     * @return ZapCard
     */
    public function getByNumberAndCreater(DetailNumber $number, Creater $creater): ZapCard
    {
        if (!$zapCard = $this->findOneBy(['number' => $number, 'creater' => $creater])) {
            throw new EntityNotFoundException('Деталь не найдена');
        }

        return $zapCard;
    }

    /**
     * @param string $number
     * @param int $createrID
     * @return ZapCard
     */
    public function getByNumberAndCreaterID(string $number, int $createrID): ?ZapCard
    {
        try {
            $creater = $this->createrRepository->get($createrID);
        } catch (DomainException $e) {
            return null;
        }

        return $this->findOneBy([
            'number' => new DetailNumber($number),
            'creater' => $creater
        ]);
    }

    /**
     * @param DetailNumber $number
     * @return ZapCard[]
     */
    public function findByNumber(DetailNumber $number): array
    {
        return $this->findBy(['number' => $number]);
    }

    /**
     * @param DetailNumber $number
     * @param Creater $creater
     * @return ZapCard
     */
    public function getOrCreate(DetailNumber $number, Creater $creater): ZapCard
    {
        $zapCard = $this->findOneBy(['number' => $number, 'creater' => $creater]);
        if (!$zapCard) {
            $zapCard = new ZapCard(
                $number,
                $creater,
                $this->shopTypeRepository->get($creater->isOriginal() ? 1 : 6),
                null,
                null,
                null,
                $this->priceGroupRepository->get(PriceGroup::DEFAULT_ID),
                $this->edIzmRepository->get(EdIzm::DEFAULT_ID)
            );
            $this->add($zapCard);
//            $this->flusher->flush();
        }
        return $zapCard;
    }

    public function add(ZapCard $zapCard): void
    {
        $this->em->persist($zapCard);
    }

    /**
     * Есть ли карточка детали с конкретным номером и производителем
     *
     * @param DetailNumber $number
     * @param Creater $creater
     * @param int $excludeID
     * @return bool
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function hasByNumber(DetailNumber $number, Creater $creater, int $excludeID = 0): bool
    {
        $query = $this->createQueryBuilder('zc')
            ->select('COUNT(zc.zapCardID)')
            ->andWhere('zc.number = :number')
            ->setParameter('number', $number->getValue())
            ->andWhere('zc.creater = :creater')
            ->setParameter('creater', $creater);

        if ($excludeID) {
            $query->andWhere('zc.zapCardID <> :id')->setParameter('id', $excludeID);
        }

        return $query->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Получение карточек деталей по массиву номеров
     *
     * @param array $numbers
     * @return ZapCard[]
     */
    public function findByNumbers(array $numbers): array
    {
        if (!$numbers) return [];

        $result = [];
        $query = $this->createQueryBuilder('zc')
        ->andWhere('zc.isDeleted = false')
        ;
        $query->andWhere($query->expr()->in('zc.number', $numbers));

        $arr = $query->getQuery()->getResult();

        foreach ($arr as $item) {
            $result[$item->getNumber()->getValue()] = $item;
        }

        return $result;
    }

    /**
     * Получение карточек деталей по массиву ID
     *
     * @param array $zapCards
     * @return ZapCard[]
     */
    public function findByZapCards(array $zapCards): array
    {
        if (!$zapCards) return [];

        $result = [];
        $query = $this->createQueryBuilder('zc');
        $query->andWhere($query->expr()->in('zc.zapCardID', $zapCards));

        $arr = $query->getQuery()->getResult();

        foreach ($arr as $item) {
            $result[$item->getId()] = $item;
        }

        return $result;
    }

    /**
     * Получение карточек деталей по массиву ID
     *
     * @param array $zapCards
     * @return ZapCard[]
     */
    public function findByZapCardsWithProfits(array $zapCards): array
    {
        if (!$zapCards) return [];
        $result = [];
        $query = $this->createQueryBuilder('zc')->select('zc', 'zco')->leftJoin('zc.profits', 'zco');
        $query->andWhere($query->expr()->in('zc.zapCardID', $zapCards));

        $arr = $query->getQuery()->getResult();

        foreach ($arr as $item) {
            $result[$item->getId()] = $item;
        }

        return $result;
    }
}
