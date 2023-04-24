<?php

namespace App\Tests\Builder\Income;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Income\Entity\Income\Income;
use App\Model\Income\Entity\Sklad\IncomeSklad;
use App\Model\Income\Entity\Status\IncomeStatus;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Order\Order;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Card\ZapCardBuilder;
use App\Tests\Builder\Provider\ProviderPriceBuilder;

class IncomeBuilder
{
    private IncomeStatus $incomeStatus;
    private ZapCard $zapCard;
    private ProviderPrice $providerPrice;
    private Manager $manager;
    private User $user;
    private ?ZapSklad $zapSklad = null;
    private bool $isOrderGood = false;
    private bool $isOrderGoodReserve = false;
    private int $quantity;
    private int $quantityIn;
    private int $quantityPath;
    private int $reserve;
    private int $quantityReturn;

    public function __construct(int $quantity = 0, int $quantityIn = 0, int $quantityPath = 0, int $reserve = 0, int $quantityReturn = 0)
    {
        $this->incomeStatus = new IncomeStatus();
        $this->zapCard = (new ZapCardBuilder())->build();
        $this->providerPrice = (new ProviderPriceBuilder())->build();
        $this->quantity = $quantity;
        $this->quantityIn = $quantityIn;
        $this->quantityPath = $quantityPath;
        $this->reserve = $reserve;
        $this->quantityReturn = $quantityReturn;
    }

    public function withIncomeStatus(IncomeStatus $incomeStatus): self
    {
        $clone = clone $this;
        $clone->incomeStatus = $incomeStatus;
        return $clone;
    }

    public function withIncomeSklad(?ZapSklad $zapSklad = null): self
    {
        $clone = clone $this;
        $clone->zapSklad = $zapSklad ?: new ZapSklad('Тест', 'Тест', false, '0', null, false);
        return $clone;
    }

    public function withOrderGood(Manager $manager, User $user, bool $isOrderGoodReserve = false): self
    {
        $clone = clone $this;
        $clone->isOrderGood = true;
        $clone->isOrderGoodReserve = $isOrderGoodReserve;
        $clone->manager = $manager;
        $clone->user = $user;
        return $clone;
    }

    public function build(): Income
    {
        $income = new Income(
            $this->providerPrice,
            $this->incomeStatus,
            $this->zapCard,
            1,
            10,
            2,
            13
        );

        $income->updateQuantity($this->quantity, $this->quantityIn, $this->quantityPath, $this->reserve, $this->quantityReturn);

        if ($this->zapSklad) {
            $incomeSklad = new IncomeSklad($income, $this->zapSklad, 0);
            $incomeSklad->updateQuantity($this->quantity, $this->quantityIn, $this->quantityPath, $this->reserve, $this->quantityReturn);
            $income->assignSklad($incomeSklad);
        }

        if ($this->isOrderGood) {
            $manager = $this->manager;
            $user = $this->user;
            $order = new Order($user, null, null);
            $orderGood = new OrderGood($order, $income->getZapCard()->getNumber(), $income->getZapCard()->getCreater(), null, $income->getProviderPrice(), $manager, 100, 0, $income->getQuantity(), 0, null, false);
            $order->assignOrderGood($orderGood);
            $income->assignOrderGood($orderGood);

            if ($this->isOrderGoodReserve && $this->zapSklad) {
                $income->assignZapCardReserve($this->zapSklad, $income->getZapCard()->getNumber(), $income->getQuantity(), null, $order, $orderGood, $manager);
            }
        }

        return $income;
    }
}