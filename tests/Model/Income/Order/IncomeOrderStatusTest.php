<?php

namespace App\Tests\Model\Income\Order;

use App\Model\Income\Entity\Order\IncomeOrder;
use App\Tests\Builder\Card\ZapSkladBuilder;
use App\Tests\Builder\Provider\ProviderBuilder;
use PHPUnit\Framework\TestCase;

class IncomeOrderStatusTest extends TestCase
{
    public function testNotOrdered(): void
    {
        $provider = (new ProviderBuilder())->build();
        $zapSklad = (new ZapSkladBuilder())->build();
        $incomeOrder = new IncomeOrder($provider, $zapSklad, 1);

        $this->assertTrue($incomeOrder->isNotOrdered());
    }

    public function testOrdered(): void
    {
        $provider = (new ProviderBuilder())->build();
        $zapSklad = (new ZapSkladBuilder())->build();
        $incomeOrder = new IncomeOrder($provider, $zapSklad, 1);
        $incomeOrder->ordered();

        $this->assertTrue($incomeOrder->isOrdered());
    }

    public function testDeleted(): void
    {
        $provider = (new ProviderBuilder())->build();
        $zapSklad = (new ZapSkladBuilder())->build();
        $incomeOrder = new IncomeOrder($provider, $zapSklad, 1);
        $incomeOrder->deleted();

        $this->assertTrue($incomeOrder->isDeleted());
    }
}