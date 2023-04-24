<?php

namespace App\Tests\Model\User\User\Getter;

use App\Tests\Builder\Beznal\UserBeznalBuilder;
use App\Tests\Builder\Contact\UserContactBuilder;
use App\Tests\Builder\User\FirmcontrBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateGetterTest extends TestCase
{
    public function testUpdateUser(): void
    {
        $firmContr = (new FirmcontrBuilder())->build();
        $user = (new UserBuilder())->build();
        $getter = (new UserBuilder('+7111111'))->build();
        $contact = (new UserContactBuilder($getter))->build();
        $beznal = (new UserBeznalBuilder($getter))->build();

        $user->updateGetterFirmContr($firmContr);

        $user->updateGetter($getter, $contact, $beznal);

        $this->assertEquals($getter, $user->getGruzUser());
        $this->assertEquals($contact, $user->getGruzUserContact());
        $this->assertEquals($beznal, $user->getGruzUserBeznal());
        $this->assertNull($user->getGruzFirmContr());

        $user->updateGetterFirmContr(null);

        $this->assertEquals($getter, $user->getGruzUser());
        $this->assertEquals($contact, $user->getGruzUserContact());
        $this->assertEquals($beznal, $user->getGruzUserBeznal());
        $this->assertNull($user->getGruzFirmContr());
    }

    public function testUpdateFirmcontr(): void
    {
        $firmContr = (new FirmcontrBuilder())->build();
        $user = (new UserBuilder())->build();
        $getter = (new UserBuilder('+7111111'))->build();
        $contact = (new UserContactBuilder($getter))->build();
        $beznal = (new UserBeznalBuilder($getter))->build();

        $user->updateGetter($getter, $contact, $beznal);

        $user->updateGetterFirmContr($firmContr);

        $this->assertNull($user->getGruzUser());
        $this->assertNull($user->getGruzUserContact());
        $this->assertNull($user->getGruzUserBeznal());
        $this->assertEquals($firmContr, $user->getGruzFirmContr());

        $user->updateGetter(null, $contact, $beznal);

        $this->assertNull($user->getGruzUser());
        $this->assertEquals($contact, $user->getGruzUserContact());
        $this->assertEquals($beznal, $user->getGruzUserBeznal());
        $this->assertEquals($firmContr, $user->getGruzFirmContr());
    }
}