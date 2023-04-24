<?php

namespace App\Tests\Model\Firm\FirmBalanceHistory;

use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory;
use App\Tests\Builder\Firm\FirmBuilder;
use App\Tests\Builder\Income\IncomeDocumentBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use App\Tests\Builder\Provider\ProviderBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class FirmBalanceHistoryCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $user = (new UserBuilder())->build();
        $provider = (new ProviderBuilder())->build();
        $manager = (new ManagerBuilder())->build();
        $incomeDocument = (new IncomeDocumentBuilder($manager))->build();
        $firm = (new FirmBuilder(true))->build();

        $firmBalanceHistory = new FirmBalanceHistory($provider, $user, '100.45', '13.44', $manager, 'Тестовое начисление', $incomeDocument, $firm);

        $this->assertEquals($provider, $firmBalanceHistory->getProvider());
        $this->assertEquals(100.45, $firmBalanceHistory->getBalance());
        $this->assertEquals(13.44, $firmBalanceHistory->getBalanceNds());
        $this->assertEquals($manager, $firmBalanceHistory->getManager());
        $this->assertEquals('Тестовое начисление', $firmBalanceHistory->getDescription());
        $this->assertEquals($incomeDocument, $firmBalanceHistory->getIncomeDocument());
        $this->assertEquals($firm, $firmBalanceHistory->getFirm());
    }
}