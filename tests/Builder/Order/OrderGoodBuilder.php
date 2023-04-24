<?php

namespace App\Tests\Builder\Order;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Manager\Entity\Manager\Manager;
use App\Model\Order\Entity\Good\OrderGood;
use App\Model\Order\Entity\Order\Order;
use App\Model\Provider\Entity\Price\ProviderPrice;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\Card\ZapSkladBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use App\Tests\Builder\Provider\ProviderPriceBuilder;

class OrderGoodBuilder
{
    private Order $order;
    private Manager $manager;
    private $price;
    private $discount;
    private $quantity;
    private string $number;
    private Creater $creater;
    private ?ZapSklad $zapSklad = null;
    private ?ProviderPrice $providerPrice = null;

    public function __construct(Order $order, string $number, Creater $creater, float $price, float $discount, int $quantity)
    {
        $this->manager = (new ManagerBuilder())->build();
        $this->order = $order;
        $this->number = $number;
        $this->creater = $creater;
        $this->price = $price;
        $this->discount = $discount;
        $this->quantity = $quantity;
    }

    public function withZapSklad(?ZapSklad $zapSklad = null): self
    {
        $clone = clone $this;
        if ($zapSklad) {
            $clone->zapSklad = $zapSklad;
        } else {
            $clone->zapSklad = (new ZapSkladBuilder())->build();
        }
        return $clone;
    }

    public function withProviderPrice(?ProviderPrice $providerPrice = null): self
    {
        $clone = clone $this;
        if ($providerPrice) {
            $clone->providerPrice = $providerPrice;
        } else {
            $clone->providerPrice = (new ProviderPriceBuilder())->build();
        }
        return $clone;
    }

    public function build(): OrderGood
    {
        $orderGood = new OrderGood(
            $this->order,
            new DetailNumber($this->number),
            $this->creater,
            $this->zapSklad,
            $this->providerPrice,
            $this->manager,
            $this->price,
            $this->discount,
            $this->quantity,
            0,
            null,
            false
        );

        return $orderGood;
    }
}