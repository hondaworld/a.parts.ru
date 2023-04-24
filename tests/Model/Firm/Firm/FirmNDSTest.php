<?php

namespace App\Tests\Model\Firm\Firm;

use App\Tests\Builder\Firm\FirmBuilder;
use PHPUnit\Framework\TestCase;

class FirmNDSTest extends TestCase
{
    public function testFirmWithNDS(): void
    {
        $firm = (new FirmBuilder(true))->build();

        $sum = 11.22;
        $nds = 20;
        $this->assertEquals(round($sum / (100 + $nds) * $nds * 100) / 100, $firm->getNDS($sum));

    }
    public function testFirmWithoutNDS(): void
    {
        $firm = (new FirmBuilder(false))->build();

        $this->assertEquals(0, $firm->getNDS(11.22));
    }
}