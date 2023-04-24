<?php

namespace App\Tests\Model\Income\Income\ChangeGTD;

use App\Model\Shop\Entity\Gtd\Gtd;
use PHPUnit\Framework\TestCase;

class CheckGTDTest extends TestCase
{
    public function testEmptyGTD(): void
    {
        $gtd = new Gtd('qwerty');
        $this->assertEquals('', $gtd->getValue());
    }

    public function testNotEmptyGTD(): void
    {
        $gtd = new Gtd('123456/21ew/123');
        $this->assertEquals('123456/21/123', $gtd->getValue());
    }

    public function testDashesGTD(): void
    {
        $gtd = new Gtd('----------');
        $this->assertEquals('----------', $gtd->getValue());
    }

    public function testIsEqualGTD(): void
    {
        $gtd = new Gtd('123456/21/123');
        $gtd1 = new Gtd('123456/21/123');
        $this->assertTrue($gtd->isEqual($gtd1));
    }
}