<?php

namespace App\Tests\Model\Income\Income\ChangeGTD;

use App\Model\Shop\Entity\Gtd\Gtd;
use App\Model\Shop\Entity\Gtd\ShopGtd;
use PHPUnit\Framework\TestCase;

class CreateShopGtdTest extends TestCase
{
    public function testCreateShopGTD(): void
    {
        $gtd = new Gtd('123456/21/123');
        $shopGtd = new ShopGtd($gtd);
        $this->assertTrue($gtd->isEqual($shopGtd->getName()));
    }
}