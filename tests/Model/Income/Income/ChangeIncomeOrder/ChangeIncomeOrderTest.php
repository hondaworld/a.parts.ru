<?php

namespace App\Tests\Model\Income\Income\ChangeIncomeOrder;

use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\Income\IncomeBuilder;
use App\Tests\Builder\Provider\ProviderBuilder;
use PHPUnit\Framework\TestCase;

class ChangeIncomeOrderTest extends TestCase
{
    public function testChangeIncomeOrder(): void
    {
        $provider = (new ProviderBuilder())->build();
        $zapSklad = new ZapSklad('Тест', 'Тест', false, '0', null, false);
        $incomeOrder = $provider->assignIncomeOrderAndReturn($zapSklad, 12);

        $income = (new IncomeBuilder())->build();
        $income->updateIncomeOrder($incomeOrder);

        $this->assertEquals($incomeOrder, $income->getIncomeOrder());
    }
}