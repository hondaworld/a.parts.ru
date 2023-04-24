<?php

namespace App\Tests\Model\Income\Income\ChangeGTD;

use App\Model\Shop\Entity\Gtd\Gtd;
use App\Model\Shop\Entity\Gtd\ShopGtd;
use App\Tests\Builder\Income\IncomeBuilder;
use PHPUnit\Framework\TestCase;

class ChangeIncomeGTDTest extends TestCase
{
    public function testChangeGTD(): void
    {
        $income = (new IncomeBuilder())->build();
        $gtd = new Gtd('123456/21/123');
        $shopGtd = new ShopGtd($gtd);
        $income->updateGtd($shopGtd);
        $this->assertTrue($gtd->isEqual($income->getShopGtd()->getName()));
    }
}