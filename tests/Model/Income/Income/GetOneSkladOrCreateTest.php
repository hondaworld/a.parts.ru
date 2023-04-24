<?php

namespace App\Tests\Model\Income\Income;

use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class GetOneSkladOrCreateTest extends TestCase
{
    public function testWithoutZapSklad(): void
    {
        $income = (new IncomeBuilder())->build();
        $incomeSklad = $income->getOneSkladOrCreate();
        $this->assertEquals($income->getProviderPrice()->getProvider()->getZapSklad(), $incomeSklad->getZapSklad());
        $this->assertEquals($incomeSklad->getQuantity(), $income->getQuantity());
    }

    public function testWithZapSklad(): void
    {
        $income = (new IncomeBuilder())->build();
        $zapSklad = new ZapSklad('Тест', 'Тест', false, '0', null, false);
        $incomeSklad = $income->getOneSkladOrCreate($zapSklad);
        $this->assertEquals($zapSklad, $incomeSklad->getZapSklad());
        $this->assertEquals($incomeSklad->getQuantity(), $income->getQuantity());
    }

    public function testWithoutZapSkladWithIncomeSklad(): void
    {
        $income = (new IncomeBuilder())->withIncomeSklad()->build();
        $incomeSklad = $income->getOneSkladOrCreate();
        $this->assertEquals($incomeSklad, $income->getFirstSklad());
        $this->assertEquals($incomeSklad->getQuantity(), $income->getQuantity());
    }

    public function testWithZapSkladWithIncomeSklad(): void
    {
        $income = (new IncomeBuilder())->withIncomeSklad()->build();
        $zapSklad = new ZapSklad('Тест1', 'Тест1', false, '0', null, false);
        $incomeSklad = $income->getOneSkladOrCreate($zapSklad);
        $this->assertEquals($incomeSklad, $income->getFirstSklad());
        $this->assertEquals($incomeSklad->getQuantity(), $income->getQuantity());
    }
}