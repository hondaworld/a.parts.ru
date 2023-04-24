<?php

namespace App\Tests\Model\Card\Auto;

use App\Model\Auto\Entity\Marka\AutoMarka;
use App\Model\Auto\Entity\Model\AutoModel;
use App\Model\Auto\Entity\MotoGroup\MotoGroup;
use App\Model\Auto\Entity\MotoModel\MotoModel;
use App\Tests\Builder\Card\ZapCardBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardAutoCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $auto_model = new AutoModel(new AutoMarka('Honda', 'Хонда'), 'Accord', 'Аккорд', null);

        $zapCard = (new ZapCardBuilder())->build();

        $zapCard->assignZapCardAuto($auto_model, null, 2005);
        $zapCard->assignZapCardAuto($auto_model, null, 2004);
        $zapCard->assignZapCardAuto($auto_model, null, 2004);

        $this->assertCount(2, $zapCard->getAutos());
    }

    public function testCreateMoto(): void
    {
        $moto_model = new MotoModel(new AutoMarka('Honda', 'Хонда'), new MotoGroup('Наименование группы', null), 'Accord');

        $zapCard = (new ZapCardBuilder())->build();

        $zapCard->assignZapCardAuto(null, $moto_model, 2005);
        $zapCard->assignZapCardAuto(null, $moto_model, 2004);
        $zapCard->assignZapCardAuto(null, $moto_model, 2004);

        $this->assertCount(2, $zapCard->getAutos());
    }
}