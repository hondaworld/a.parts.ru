<?php

namespace App\Tests\Model\Card\ZapCard\ABC;

use App\Model\Sklad\Entity\ZapSklad\ZapSklad;
use App\Tests\Builder\Card\ZapCardBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardAbcUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(1);
        $manager = (new ManagerBuilder())->build();

        $zapCard->updateAbc($zapSklad, 'A', $manager);

        $this->assertEquals('A', $zapCard->getZapCardAbc($zapSklad->getId()));
        $this->assertCount(1, $zapCard->getAbcHistory($zapSklad->getId()));

        $history = $zapCard->getAbcHistory($zapSklad->getId())[0];

        $this->assertEquals('A', $history['abc']);
        $this->assertEquals($manager->getName(), $history['manager']);
    }

    public function testUpdateSame(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(1);
        $manager = (new ManagerBuilder())->build();

        $zapCard->updateAbc($zapSklad, 'A', $manager);
        $zapCard->updateAbc($zapSklad, 'A', $manager);

        $this->assertEquals('A', $zapCard->getZapCardAbc($zapSklad->getId()));
        $this->assertCount(1, $zapCard->getAbcHistory($zapSklad->getId()));

        $history = $zapCard->getAbcHistory($zapSklad->getId())[0];

        $this->assertEquals('A', $history['abc']);
        $this->assertEquals($manager->getName(), $history['manager']);
    }

    public function testUpdateEmpty(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(1);
        $manager = (new ManagerBuilder())->build();

        $zapCard->updateAbc($zapSklad, 'A', $manager);
        $zapCard->updateAbc($zapSklad, '', $manager);

        $this->assertNull($zapCard->getZapCardAbc($zapSklad->getId()));
        $this->assertCount(2, $zapCard->getAbcHistory($zapSklad->getId()));

        $history = $zapCard->getAbcHistory($zapSklad->getId())[0];

        $this->assertEquals('A', $history['abc']);
        $this->assertEquals($manager->getName(), $history['manager']);

        $history1 = $zapCard->getAbcHistory($zapSklad->getId())[1];

        $this->assertEquals('', $history1['abc']);
        $this->assertEquals($manager->getName(), $history1['manager']);
    }

    public function testUpdateTwice(): void
    {
        $zapCard = (new ZapCardBuilder())->build();
        $zapSklad = $this->createMock(ZapSklad::class);
        $zapSklad->method('getId')->willReturn(1);
        $manager = (new ManagerBuilder())->build();

        $zapCard->updateAbc($zapSklad, 'A', $manager);
        $zapCard->updateAbc($zapSklad, 'B', $manager);

        $this->assertEquals('B', $zapCard->getZapCardAbc($zapSklad->getId()));
        $this->assertCount(2, $zapCard->getAbcHistory($zapSklad->getId()));

        $history = $zapCard->getAbcHistory($zapSklad->getId())[0];

        $this->assertEquals('A', $history['abc']);
        $this->assertEquals($manager->getName(), $history['manager']);

        $history1 = $zapCard->getAbcHistory($zapSklad->getId())[1];

        $this->assertEquals('B', $history1['abc']);
        $this->assertEquals($manager->getName(), $history1['manager']);
    }
}