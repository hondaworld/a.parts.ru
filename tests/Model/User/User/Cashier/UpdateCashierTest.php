<?php

namespace App\Tests\Model\User\User\Cashier;

use App\Tests\Builder\Beznal\UserBeznalBuilder;
use App\Tests\Builder\Contact\UserContactBuilder;
use App\Tests\Builder\User\FirmcontrBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateCashierTest extends TestCase
{
    public function testUpdateUser(): void
    {
        $firmContr = (new FirmcontrBuilder())->build();
        $user = (new UserBuilder())->build();
        $cashier = (new UserBuilder('+7111111'))->build();
        $contact = (new UserContactBuilder($cashier))->build();
        $beznal = (new UserBeznalBuilder($cashier))->build();

        $user->updateCashierFirmContr($firmContr);

        $user->updateCashier($cashier, $contact, $beznal);

        $this->assertEquals($cashier, $user->getCashUser());
        $this->assertEquals($contact, $user->getCashUserContact());
        $this->assertEquals($beznal, $user->getCashUserBeznal());
        $this->assertNull($user->getCashFirmContr());

        $user->updateCashierFirmContr(null);

        $this->assertEquals($cashier, $user->getCashUser());
        $this->assertEquals($contact, $user->getCashUserContact());
        $this->assertEquals($beznal, $user->getCashUserBeznal());
        $this->assertNull($user->getCashFirmContr());
    }

    public function testUpdateFirmcontr(): void
    {
        $firmContr = (new FirmcontrBuilder())->build();
        $user = (new UserBuilder())->build();
        $cashier = (new UserBuilder('+7111111'))->build();
        $contact = (new UserContactBuilder($cashier))->build();
        $beznal = (new UserBeznalBuilder($cashier))->build();

        $user->updateCashier($cashier, $contact, $beznal);

        $user->updateCashierFirmContr($firmContr);

        $this->assertNull($user->getCashUser());
        $this->assertNull($user->getCashUserContact());
        $this->assertNull($user->getCashUserBeznal());
        $this->assertEquals($firmContr, $user->getCashFirmContr());

        $user->updateCashier(null, $contact, $beznal);

        $this->assertNull($user->getCashUser());
        $this->assertEquals($contact, $user->getCashUserContact());
        $this->assertEquals($beznal, $user->getCashUserBeznal());
        $this->assertEquals($firmContr, $user->getCashFirmContr());
    }
}