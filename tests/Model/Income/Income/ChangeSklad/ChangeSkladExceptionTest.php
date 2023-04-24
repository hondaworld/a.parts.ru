<?php

namespace App\Tests\Model\Income\Income\ChangeSklad;

use App\Model\Income\Entity\Sklad\IncomeSklad;
use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeSkladExceptionTest extends TestCase
{
    public function testExceptionSomeSklads(): void
    {
        $income = (new IncomeBuilder())->withIncomeSklad()->build();
        $zapSklad = new ZapSklad('Тест1', 'Тест', false, '0', null, false);
        $incomeSklad = new IncomeSklad($income, $zapSklad, 0);
        $income->assignSklad($incomeSklad);

        $this->expectExceptionMessage('У детали 15400PLMA03 склад должен быть один');
        $income->changeSklad($zapSklad);
    }

    public function testExceptionWithoutSklad(): void
    {
        $income = (new IncomeBuilder())->build();
        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(1);
        $incomeSklad = new IncomeSklad($income, $zapSklad, 0);
        $income->assignSklad($incomeSklad);

        $this->expectExceptionMessage('У детали 15400PLMA03 склад уже указанный');
        $income->changeSklad($zapSklad);
    }
}