<?php

namespace App\Tests\Model\Firm\FirmBalanceHistory;

use App\Model\Firm\Entity\BalanceHistory\FirmBalanceHistory;
use App\Tests\Builder\Firm\FirmBuilder;
use App\Tests\Builder\Income\IncomeDocumentBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use App\Tests\Builder\Provider\ProviderBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class FirmBalanceHistoryUpdateTest extends TestCase
{
    public function testUpdateFirm(): void
    {
        $user = (new UserBuilder())->build();
        $provider = (new ProviderBuilder())->build();
        $manager = (new ManagerBuilder())->build();
        $incomeDocument = (new IncomeDocumentBuilder($manager))->build();
        $firm = (new FirmBuilder(true))->build();
        $firm1 = (new FirmBuilder(true, 'Другая компания'))->build();

        $firmBalanceHistory = new FirmBalanceHistory($provider, $user, '100.45', '13.44', $manager, 'Тестовое начисление', $incomeDocument, $firm);

        $firmBalanceHistory->updateFirm($firm1);

        $this->assertEquals($firm1, $firmBalanceHistory->getFirm());
    }

    public function testUpdateBalance(): void
    {
        $user = (new UserBuilder())->build();
        $provider = (new ProviderBuilder())->build();
        $manager = (new ManagerBuilder())->build();
        $incomeDocument = (new IncomeDocumentBuilder($manager))->build();
        $firm = (new FirmBuilder(true))->build();
        $firm1 = (new FirmBuilder(true, 'Другая компания'))->build();

        $firmBalanceHistory = new FirmBalanceHistory($provider, $user, '100.45', '13.44', $manager, 'Тестовое начисление', $incomeDocument, $firm);

        $firmBalanceHistory->updateBalance('34.67', 'Изменение баланса');

        $this->assertEquals(34.67, $firmBalanceHistory->getBalance());
        $this->assertEquals('Изменение баланса', $firmBalanceHistory->getDescription());
    }

    public function testUpdateBalanceNds(): void
    {
        $user = (new UserBuilder())->build();
        $provider = (new ProviderBuilder())->build();
        $manager = (new ManagerBuilder())->build();
        $incomeDocument = (new IncomeDocumentBuilder($manager))->build();
        $firm = (new FirmBuilder(true))->build();
        $firm1 = (new FirmBuilder(true, 'Другая компания'))->build();

        $firmBalanceHistory = new FirmBalanceHistory($provider, $user, '100.45', '13.44', $manager, 'Тестовое начисление', $incomeDocument, $firm);

        $firmBalanceHistory->updateBalanceNds('13.57');

        $this->assertEquals(13.57, $firmBalanceHistory->getBalanceNds());

        $firmBalanceHistory->updateBalanceNds(null);

        $this->assertEquals(0, $firmBalanceHistory->getBalanceNds());
    }
}