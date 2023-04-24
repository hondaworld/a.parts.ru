<?php

namespace App\Tests\Model\Card\ZapCard\Manager;

use App\Model\Contact\Entity\Country\Country;
use App\Tests\Builder\Card\ZapCardBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardManagerTest extends TestCase
{
    public function testManager(): void
    {
        $zapCard = (new ZapCardBuilder())->build();

        $manager = (new ManagerBuilder())->build();

        $zapCard->updateManager($manager);
        $this->assertEquals($manager, $zapCard->getManager());
        $zapCard->updateManager(null);
        $this->assertNull($zapCard->getManager());
    }
}