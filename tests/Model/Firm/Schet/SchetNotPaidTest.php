<?php

namespace App\Tests\Model\Firm\Schet;

use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Finance\Entity\FinanceType\FinanceType;
use App\Model\Order\Entity\AlertType\OrderAlertType;
use App\Tests\Builder\Beznal\FirmBeznalBuilder;
use App\Tests\Builder\Contact\FirmContactBuilder;
use App\Tests\Builder\Contact\UserContactBuilder;
use App\Tests\Builder\Expense\ExpenseDocumentBuilder;
use App\Tests\Builder\Firm\FirmBuilder;
use App\Tests\Builder\Firm\SchetBuilder;
use App\Tests\Builder\Order\OrderBuilder;
use App\Tests\Builder\Order\OrderGoodBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class SchetNotPaidTest extends TestCase
{
    public function testFromNewToNotPaid(): void
    {
        $schet = (new SchetBuilder())->build();

        $firm = (new FirmBuilder(true))->build();
        $firmContact = (new FirmContactBuilder($firm))->build();
        $firmBeznal = (new FirmBeznalBuilder($firm))->build();
        $user = (new UserBuilder())->build();
        $userContact = (new UserContactBuilder($user))->build();
        $expenseDocument = (new ExpenseDocumentBuilder())
            ->withExpFirm($firm, $firmContact, $firmBeznal)
            ->withExpUser($user, $userContact, null)
            ->build();

        $schet->fromNewToNotPaid(4, 'pre', 'suf', $expenseDocument);

        $this->assertEquals(4, $schet->getDocument()->getSchetNum());
        $this->assertEquals('pre', $schet->getDocument()->getDocumentPrefix());
        $this->assertEquals('suf', $schet->getDocument()->getDocumentSufix());
        $this->assertEquals($firm, $schet->getFirm());
        $this->assertEquals($firmContact, $schet->getFirmContact());
        $this->assertEquals($firmBeznal, $schet->getFirmBeznal());
        $this->assertEquals($user, $schet->getExpUser());
        $this->assertEquals($userContact, $schet->getExpUserContact());
        $this->assertTrue($schet->isNotPaid());
    }

    public function testFromNewToNotPaidForCreditCard(): void
    {
        $schet = (new SchetBuilder())->build();

        $firm = (new FirmBuilder(true))->build();
        $user = (new UserBuilder())->build();
        $financeType = new FinanceType('Тип', $firm, false);

        $schet->fromNewToNotPaidForCreditCard(4, 'pre', 'suf', $firm, $user, $financeType);

        $this->assertEquals(4, $schet->getDocument()->getSchetNum());
        $this->assertEquals('pre', $schet->getDocument()->getDocumentPrefix());
        $this->assertEquals('suf', $schet->getDocument()->getDocumentSufix());
        $this->assertEquals($firm, $schet->getFirm());
        $this->assertEquals($user, $schet->getExpUser());
        $this->assertEquals($financeType, $schet->getFinanceType());
        $this->assertTrue($schet->isNotPaid());
    }
}