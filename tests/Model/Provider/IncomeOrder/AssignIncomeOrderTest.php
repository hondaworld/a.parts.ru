<?php

namespace App\Tests\Model\Provider\IncomeOrder;

use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\Provider\ProviderBuilder;
use PHPUnit\Framework\TestCase;

class AssignIncomeOrderTest extends TestCase
{
    public function testAssign(): void
    {
        $provider = (new ProviderBuilder())->build();
        $zapSklad = new ZapSklad('Тест', 'Тест', false, '0', null, false);
        $incomeOrder = $provider->assignIncomeOrderAndReturn($zapSklad, 12);

        $this->assertEquals(12, $incomeOrder->getDocumentNum());
        $this->assertEquals($zapSklad, $incomeOrder->getZapSklad());
        $this->assertEquals($provider, $incomeOrder->getProvider());
    }
}