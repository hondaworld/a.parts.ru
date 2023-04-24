<?php

namespace App\Tests\Model\Firm\Firm;

use App\Model\Manager\Entity\Manager\Manager;
use App\Tests\Builder\Firm\FirmBuilder;
use App\Tests\Builder\Income\IncomeDocumentBuilder;
use App\Tests\Builder\Provider\ProviderBuilder;
use PHPUnit\Framework\TestCase;

class AssignFirmBalanceHistoryTest extends TestCase
{
    public function testAssign():void
    {
        $firm = (new FirmBuilder(true))->build();
        $provider = (new ProviderBuilder())->build();
        $manager = $this->createMock(Manager::class);
        $incomeDocument = (new IncomeDocumentBuilder($manager))->build();
        $firm->assignFirmBalanceHistory($provider, 130.4, 22.6, $manager, 'Тестовое описание', $incomeDocument);

        foreach ($firm->getFirmBalanceHistory() as $firmBalanceHistory) {
            $this->assertEquals(130.4, $firmBalanceHistory->getBalance());
            $this->assertEquals(22.6, $firmBalanceHistory->getBalanceNds());
            $this->assertEquals($provider, $firmBalanceHistory->getProvider());
            $this->assertEquals($provider->getUser(), $firmBalanceHistory->getUser());
            $this->assertEquals('Тестовое описание', $firmBalanceHistory->getDescription());
            $this->assertEquals($incomeDocument, $firmBalanceHistory->getIncomeDocument());
        }
    }

    public function testAssignNoDescription():void
    {
        $firm = (new FirmBuilder(true))->build();
        $provider = (new ProviderBuilder())->build();
        $manager = $this->createMock(Manager::class);
        $incomeDocument = (new IncomeDocumentBuilder($manager))->build();
        $firm->assignFirmBalanceHistory($provider, 130.4, 22.6, $manager, null, $incomeDocument);

        foreach ($firm->getFirmBalanceHistory() as $firmBalanceHistory) {
            $this->assertEquals('', $firmBalanceHistory->getDescription());
        }
    }

    public function testAssignNoNds():void
    {
        $firm = (new FirmBuilder(true))->build();
        $provider = (new ProviderBuilder())->build();
        $manager = $this->createMock(Manager::class);
        $incomeDocument = (new IncomeDocumentBuilder($manager))->build();
        $firm->assignFirmBalanceHistory($provider, 130.4, null, $manager, null, $incomeDocument);

        foreach ($firm->getFirmBalanceHistory() as $firmBalanceHistory) {
            $this->assertEquals(0, $firmBalanceHistory->getBalanceNds());
        }
    }

    public function testAssignNoIncomeDocument():void
    {
        $firm = (new FirmBuilder(true))->build();
        $provider = (new ProviderBuilder())->build();
        $manager = $this->createMock(Manager::class);
        $incomeDocument = (new IncomeDocumentBuilder($manager))->build();
        $firm->assignFirmBalanceHistory($provider, 130.4, null, $manager, null, null);

        foreach ($firm->getFirmBalanceHistory() as $firmBalanceHistory) {
            $this->assertNull($firmBalanceHistory->getIncomeDocument());
        }
    }
}