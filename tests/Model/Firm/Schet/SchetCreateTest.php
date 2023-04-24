<?php

namespace App\Tests\Model\Firm\Schet;

use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Firm\Entity\Schet\Schet;
use App\Model\User\Entity\User\User;
use App\Tests\Builder\Firm\FirmBuilder;
use PHPUnit\Framework\TestCase;

class SchetCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $financeType = $this->createMock(FinanceType::class);
        $user = $this->createMock(User::class);
        $schet = new Schet($financeType, $user, null);

        $this->assertEquals($financeType, $schet->getFinanceType());
        $this->assertEquals($user, $schet->getUser());
        $this->assertNull($schet->getFirm());
        $this->assertTrue($schet->isNew());
    }

    public function testCreateWithFirm(): void
    {
        $financeType = $this->createMock(FinanceType::class);
        $user = $this->createMock(User::class);
        $firm = (new FirmBuilder(true))->build();
        $schet = new Schet($financeType, $user, $firm);

        $this->assertEquals($financeType, $schet->getFinanceType());
        $this->assertEquals($user, $schet->getUser());
        $this->assertEquals($firm, $schet->getFirm());
        $this->assertTrue($schet->isNew());
    }
}