<?php

namespace App\Model\Order\UseCase\Good\CreateFile;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCardRepository;
use App\Model\Card\Entity\Stock\ZapCardStockRepository;
use App\Model\Card\Service\ZapCardPriceService;
use App\Model\Detail\Entity\Creater\CreaterRepository;
use App\Model\Flusher;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Order\Order;
use App\Model\Order\Entity\Order\OrderRepository;
use App\Model\Provider\Entity\Price\ProviderPriceRepository;
use App\Model\Sklad\Entity\ZapSklad\ZapSkladRepository;
use App\Model\User\Entity\User\User;
use App\Service\Price\PartPriceService;
use Doctrine\DBAL\Exception;
use DomainException;
use Symfony\Component\HttpFoundation\Request;

class Handler
{
    private CreaterRepository $createrRepository;
    private ZapSkladRepository $zapSkladRepository;
    private Flusher $flusher;
    private ProviderPriceRepository $providerPriceRepository;
    private PartPriceService $partPriceService;
    private OrderRepository $orderRepository;
    private ZapCardRepository $zapCardRepository;
    private ZapCardPriceService $zapCardPriceService;
    private ZapCardStockRepository $zapCardStockRepository;

    public function __construct(
        CreaterRepository       $createrRepository,
        ZapSkladRepository      $zapSkladRepository,
        ProviderPriceRepository $providerPriceRepository,
        PartPriceService        $partPriceService,
        OrderRepository         $orderRepository,
        ZapCardRepository       $zapCardRepository,
        ZapCardPriceService     $zapCardPriceService,
        ZapCardStockRepository  $zapCardStockRepository,
        Flusher                 $flusher
    )
    {
        $this->createrRepository = $createrRepository;
        $this->zapSkladRepository = $zapSkladRepository;
        $this->flusher = $flusher;
        $this->providerPriceRepository = $providerPriceRepository;
        $this->partPriceService = $partPriceService;
        $this->orderRepository = $orderRepository;
        $this->zapCardRepository = $zapCardRepository;
        $this->zapCardPriceService = $zapCardPriceService;
        $this->zapCardStockRepository = $zapCardStockRepository;
    }

    public function handle(Request $request, User $user, Manager $manager): void
    {
        $zapSkladID = $request->query->get('zapSkladID') ?? 0;
        $providerPriceID = $request->query->get('providerPriceID') ?? 0;

        if ($zapSkladID != 0 || $providerPriceID != 0) {

            $order = $this->orderRepository->getWorking($user);
            if ($order) {
                $user = $order->getUser();
            } else {
                $order = new Order($user, $manager, null);
                $this->orderRepository->add($order);
            }

            try {
                if ($providerPriceID != 0) {
                    $providerPrice = $this->providerPriceRepository->get($providerPriceID);
                    foreach ($request->request->all() as $key => $value) {
                        $value = intval($value);
                        if ($value > 0) {
                            $arr = explode('_', $key);

                            $number = new DetailNumber($arr[2]);
                            $creater = $this->createrRepository->get($arr[1]);

                            $stock = $this->partPriceService->getPriceStock($number, $creater, $providerPrice);

                            $orderGood = OrderGood::createFromProviderPrice($order, $providerPrice, $number, $creater, $this->partPriceService, $stock->hasPrice() ? $this->zapCardStockRepository->get($stock->stockID) : null, $manager, $value);
                            $order->assignOrderGood($orderGood);

                            $manager->assignOrderOperation($user, $order, "Добавление детали в заказ", $number->getValue());
                        }
                    }
                } elseif ($zapSkladID != 0) {
                    $zapSklad = $this->zapSkladRepository->get($zapSkladID);
                    foreach ($request->request->all() as $key => $value) {
                        $value = intval($value);
                        if ($value > 0) {
                            $arr = explode('_', $key);

                            $zapCard = $this->zapCardRepository->get($arr[1]);
                            $stock = $this->partPriceService->getPriceStock($zapCard->getNumber(), $zapCard->getCreater());

                            $orderGood = OrderGood::createFromZapSklad($order, $zapSklad, $zapCard, $this->zapCardPriceService, $stock->hasPrice() ? $this->zapCardStockRepository->get($stock->stockID) : null, $manager, $value);
                            $order->assignOrderGood($orderGood);

                            $manager->assignOrderOperation($user, $order, "Добавление детали в заказ", $zapCard->getNumber()->getValue());
                        }
                    }
                }
            } catch (Exception $e) {
                throw new DomainException($e->getMessage());
            }

        }

        $this->flusher->flush();
    }
}
