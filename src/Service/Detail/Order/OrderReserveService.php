<?php


namespace App\Service\Detail\Order;


use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Reserve\ZapCardReserveRepository;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Income\Entity\Income\IncomeRepository;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\AlertType\OrderAlertType;
use App\Model\Order\Entity\AlertType\OrderAlertTypeRepository;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Good\OrderGoodRepository;
use App\Model\Order\Entity\Order\Order;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\ReadModel\Income\IncomeFetcher;
use Doctrine\DBAL\Exception;
use DomainException;

class OrderReserveService
{
    private ZapCardReserveRepository $zapCardReserveRepository;
    private ZapCardRepository $zapCardRepository;
    private IncomeFetcher $incomeFetcher;
    private IncomeRepository $incomeRepository;
    private OrderAlertTypeRepository $orderAlertTypeRepository;
    private OrderGoodRepository $orderGoodRepository;

    public function __construct(
        ZapCardReserveRepository $zapCardReserveRepository,
        ZapCardRepository        $zapCardRepository,
        IncomeFetcher            $incomeFetcher,
        IncomeRepository         $incomeRepository,
        OrderAlertTypeRepository $orderAlertTypeRepository,
        OrderGoodRepository      $orderGoodRepository
    )
    {
        $this->zapCardReserveRepository = $zapCardReserveRepository;
        $this->zapCardRepository = $zapCardRepository;
        $this->incomeFetcher = $incomeFetcher;
        $this->incomeRepository = $incomeRepository;
        $this->orderAlertTypeRepository = $orderAlertTypeRepository;
        $this->orderGoodRepository = $orderGoodRepository;
    }

    public function addReserve(OrderGood $orderGood, Manager $manager, bool $isExpense = false, bool $isPerem = false, ?ZapSklad $zapSklad_to = null): void
    {
        if ($orderGood->isDeleted()) {
            throw new DomainException("Деталь " . $orderGood->getNumber()->getValue() . " удалена из заказа");
        }

        //        $zapCard = $this->zapCardRepository->findOneBy(['number' => $orderGood->getNumber(), 'creater' => $orderGood->getCreater()]);
        $zapCard = $this->zapCardRepository->getOrCreate($orderGood->getNumber(), $orderGood->getCreater());

        // Общее количество на складе
        try {
            $quantityInWareHouseAll = $this->incomeFetcher->findQuantityInWarehouseByZapCardAndZapSklad($zapCard->getId(), $orderGood->getZapSklad()->getId());
        } catch (Exception $e) {
            throw new DomainException($e->getMessage());
        }

        $quantityReserved = $orderGood->getQuantityReserved();
        $quantity = $orderGood->getQuantity();

        if ($quantity > $quantityInWareHouseAll + $quantityReserved) {
            throw new DomainException("Детали " . $orderGood->getNumber()->getValue() . " нет на складе в достаточном количестве");
        }

        // Получаем все приходы, на которых есть что-то на складе
        $incomes = $this->incomeRepository->findInWarehouseByZapCardAndZapSklad($zapCard, $orderGood->getZapSklad());

        $orderGood->reserve($incomes, $manager, $isExpense, $isPerem, $zapSklad_to);
    }

    public function addOrderGoodAndReserve(Order $order, DetailNumber $number, Creater $creater, ZapSklad $zapSklad, float $price, int $quantity, Manager $manager): OrderGood
    {
        $zapCard = $this->zapCardRepository->getByNumberAndCreater($number, $creater);
/*        if (!$zapCard) {
            $creater = $creater->getCreaterWeight();
            if ($creater) {
                $zapCard = $this->zapCardRepository->getByNumberAndCreater($number, $creater);
            }
            if (!$zapCard) {
                throw new DomainException("Деталь не найдена");
            }
        }*/

        try {
            $quantityInWareHouseAll = $this->incomeFetcher->findQuantityInWarehouseByZapCardAndZapSklad($zapCard->getId(), $zapSklad->getId());
        } catch (Exception $e) {
            throw new DomainException($e->getMessage());
        }

        if ($quantityInWareHouseAll <= 0) {
            throw new DomainException("Недостаточное количество на складе");
        }
        if ($quantity > $quantityInWareHouseAll) $quantity = $quantityInWareHouseAll;

        $orderGood = new OrderGood($order, $number, $creater, $zapSklad, null, $manager, $price, 0, $quantity, 2, null, false);
        $this->orderGoodRepository->add($orderGood);

        // Получаем все приходы, на которых есть что-то на складе
        $incomes = $this->incomeRepository->findInWarehouseByZapCardAndZapSklad($zapCard, $zapSklad);

        $orderGood->reserve($incomes, $manager);

        return $orderGood;
    }

    public function removeReserveIncomeByOrderGood(OrderGood $orderGood): void
    {
        if ($orderGood->getIncome()) {
            $income = $orderGood->getIncome();

            if ($income->getStatus()->getId() == IncomeStatus::DEFAULT_STATUS) {
                $this->incomeRepository->remove($income);
            } else {
                $income->removeReserve();
                foreach ($income->getSklads() as $incomeSklad) {
                    $incomeSklad->removeReserve();
                }
            }

            if ($income->getStatus()->getId() == IncomeStatus::IN_WAREHOUSE) {
                $income->getZapCard()->updatePrice($income->getPrice(), $income->getPriceZak() + $income->getPriceDost(), $income->getProviderPrice(), $income->getProviderPrice()->getCurrency());
//                $income->getZapCard()->updatePriceOnly($income->getPrice());
            }

            $orderGood->updateIncome(null);
        }

        $orderGood->clearZapCardReserve();
    }

    public function removeExpiredReserves(): void
    {
        foreach ($this->zapCardReserveRepository->getExpired() as $zapCardReserve) {
            $orderGood = $zapCardReserve->getOrderGood();
            $orderGood->removeZapCardReserve($zapCardReserve);
            $orderGood->assignAlert($this->orderAlertTypeRepository->get(OrderAlertType::REMOVE_RESERVE));
        }
    }
}