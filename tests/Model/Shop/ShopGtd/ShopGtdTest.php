<?php

namespace App\Tests\Model\Shop\ShopGtd;

use App\Model\Shop\Entity\Gtd\Gtd;
use App\Model\Shop\Entity\Gtd\ShopGtd;
use PHPUnit\Framework\TestCase;

class ShopGtdTest extends TestCase
{
    public function testCreate(): void
    {
        $gtd = new Gtd('1234');
        $shopGtd = new ShopGtd($gtd);
        $this->assertEquals('1234', $shopGtd->getName()->getValue());
    }

    public function testUdate(): void
    {
        $gtd = new Gtd('1234');
        $gtd1 = new Gtd('12345');
        $shopGtd = new ShopGtd($gtd);
        $shopGtd->update($gtd1);
        $this->assertEquals('12345', $shopGtd->getName()->getValue());
    }
}