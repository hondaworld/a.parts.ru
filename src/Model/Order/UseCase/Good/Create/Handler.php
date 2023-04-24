<?php

namespace App\Model\Order\UseCase\Good\Create;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Stock\ZapCardStockRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\AddReason\OrderAddReasonRepository;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Order\Order;
use App\Model\Order\Entity\Order\OrderRepository;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Model\User\Entity\User\User;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;
use DomainException;

class Handler
{
    private Flusher $flusher;
    private ProviderPriceRepository $providerPriceRepository;
    private PartPriceService $partPriceService;
    private ZapSkladRepository $zapSkladRepository;
    private ZapCardPriceService $zapCardPriceService;
    private ZapCardRepository $zapCardRepository;
    private OrderRepository $orderRepository;
    private OrderAddReasonRepository $orderAddReasonRepository;
    private CreaterRepository $createrRepository;
    private ZapCardStockRepository $zapCardStockRepository;

    public function __construct(
        ProviderPriceRepository  $providerPriceRepository,
        PartPriceService         $partPriceService,
        ZapSkladRepository       $zapSkladRepository,
        ZapCardPriceService      $zapCardPriceService,
        ZapCardRepository        $zapCardRepository,
        OrderRepository          $orderRepository,
        OrderAddReasonRepository $orderAddReasonRepository,
        CreaterRepository        $createrRepository,
        ZapCardStockRepository   $zapCardStockRepository,
        Flusher                  $flusher
    )
    {
        $this->flusher = $flusher;
        $this->partPriceService = $partPriceService;
        $this->providerPriceRepository = $providerPriceRepository;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->zapCardPriceService = $zapCardPriceService;
        $this->zapCardRepository = $zapCardRepository;
        $this->orderRepository = $orderRepository;
        $this->orderAddReasonRepository = $orderAddReasonRepository;
        $this->createrRepository = $createrRepository;
        $this->zapCardStockRepository = $zapCardStockRepository;
    }

    public function handle(Command $command, User $user, Manager $manager): void
    {
        if (!$command->orderID && !$command->order_add_reasonID) {
            throw new DomainException('Тип заказа не выбран');
        }

        if (!$command->providerPriceID && !$command->zapSkladID) {
            throw new DomainException('Поставщик не выбран');
        }

        if ($command->orderID) {
            $order = $this->orderRepository->get($command->orderID);
            $user = $order->getUser();
        } else {
            $order = new Order($user, $manager, $this->orderAddReasonRepository->get($command->order_add_reasonID));
            $this->orderRepository->add($order);
        }

        $number = new DetailNumber($command->number);
        $creater = $this->createrRepository->get($command->createrID);


        try {
            if ($command->providerPriceID) {
                $providerPrice = $this->providerPriceRepository->get($command->providerPriceID);
                $stock = $this->partPriceService->getPriceStock($number, $creater, $providerPrice);

                $orderGood = OrderGood::createFromProviderPrice($order, $providerPrice, $number, $creater, $this->partPriceService, $stock->hasPrice() ? $this->zapCardStockRepository->get($stock->stockID) : null, $manager, $command->quantity);
                $order->assignOrderGood($orderGood);
            }

            if ($command->zapSkladID) {
                $zapSklad = $this->zapSkladRepository->get($command->zapSkladID);

                $zapCard = $this->zapCardRepository->getByNumberAndCreater($number, $creater);
                $stock = $this->partPriceService->getPriceStock($number, $creater);

                $orderGood = OrderGood::createFromZapSklad($order, $zapSklad, $zapCard, $this->zapCardPriceService, $stock->hasPrice() ? $this->zapCardStockRepository->get($stock->stockID) : null, $manager, $command->quantity);
                $order->assignOrderGood($orderGood);
            }
        } catch (Exception $e) {
            throw new DomainException($e->getMessage());
        }

        $manager->assignOrderOperation($user, $order, "Добавление детали в заказ", $number->getValue());

        $this->flusher->flush();
    }
}
