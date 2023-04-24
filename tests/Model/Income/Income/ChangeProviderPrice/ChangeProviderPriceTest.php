<?php

namespace App\Tests\Model\Income\Income\ChangeProviderPrice;

use App\Model\Manager\Entity\Manager\Manager;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Card\ZapCardBuilder;
use App\Tests\Builder\Income\IncomeBuilder;
use App\Tests\Builder\Provider\ProviderPriceBuilder;
use PHPUnit\Framework\TestCase;

class ChangeProviderPriceTest extends TestCase
{
    public function testChangeProviderPrice(): void
    {
        $income = (new IncomeBuilder())->build();
        $providerPrice = (new ProviderPriceBuilder('Новый прайс-лист'))->build();
        $income->updateProviderPrice($providerPrice);

        $this->assertEquals($providerPrice->getName(), $income->getProviderPrice()->getName());
    }

    public function testChangeProviderPriceWithOrderGoods(): void
    {
        $manager = $this->createMock(Manager::class);
        $user = $this->createMock(User::class);
        $income = (new IncomeBuilder())->withOrderGood($manager, $user)->build();
        $providerPrice = (new ProviderPriceBuilder('Новый прайс-лист'))->build();

        $income->updateProviderPrice($providerPrice);

        foreach ($income->getOrderGoods() as $orderGood) {
            $this->assertEquals($providerPrice->getName(), $orderGood->getProviderPrice()->getName());
        }
    }
}