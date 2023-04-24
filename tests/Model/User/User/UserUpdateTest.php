<?php

namespace App\Tests\Model\User\User;

use App\Model\User\Entity\Opt\Opt;
use App\Model\User\Entity\ShopPayType\ShopPayType;
use App\Model\User\Entity\User\Name;
use App\Tests\Builder\Contact\TownBuilder;
use App\Tests\Builder\Manager\ManagerBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UserUpdateTest extends TestCase
{
    public function testOpt(): void
    {
        $user = (new UserBuilder())->build();

        $opt = new Opt('Test1', 2);
        $shopPayType = new ShopPayType('имя');

        $user->updateOpt($opt, $shopPayType);

        $this->assertEquals($opt, $user->getOpt());
        $this->assertEquals($shopPayType, $user->getShopPayType());
    }

    public function testOwner(): void
    {
        $user = (new UserBuilder())->build();

        $this->assertNull($user->getOwner());

        $manager = (new ManagerBuilder())->build();
        $user->updateOwner($manager);
        $this->assertEquals($manager, $user->getOwner());

        $user->updateOwner(null);
        $this->assertNull($user->getOwner());
    }

    public function testDiscount(): void
    {
        $user = (new UserBuilder())->build();

        $this->assertEquals(0, $user->getSchetDays());
        $this->assertEquals(0, $user->getDiscountParts());
        $this->assertEquals(0, $user->getDiscountService());

        $user->updateDiscount(null, 0, 0);

        $this->assertEquals(0, $user->getSchetDays());
        $this->assertEquals(0, $user->getDiscountParts());
        $this->assertEquals(0, $user->getDiscountService());
    }

    public function testDop(): void
    {
        $user = (new UserBuilder())->build();

        $this->assertNull($user->getDateofuser());
        $this->assertEquals('', $user->getSex());

        $d = new \DateTime('-4 years');
        $user->updateDop($d, 'M');

        $this->assertEquals($d, $user->getDateofuser());
        $this->assertEquals('M', $user->getSex());
    }

    public function testName(): void
    {
        $user = (new UserBuilder())->build();

        $town = (new TownBuilder())->build();

        $name = new Name(
            'Иван',
            'Иванов',
            'Иванович'
        );

        $user->updateName($name, 'Иванов имя', $town);

        $this->assertEquals($name, $user->getUserName());
        $this->assertEquals('Иванов имя', $user->getName());
        $this->assertEquals($town, $user->getTown());

        $user->updateName($name, 'Иванов имя', null);

        $this->assertEquals($name, $user->getUserName());
        $this->assertEquals('Иванов имя', $user->getName());
        $this->assertNull($user->getTown());
    }

    public function testUserName(): void
    {
        $user = (new UserBuilder())->build();

        $name = new Name(
            'Иван',
            'Иванов',
            'Иванович'
        );

        $user->updateUserName($name);

        $this->assertEquals($name, $user->getUserName());
    }

    public function testPhoneMobile(): void
    {
        $user = (new UserBuilder())->build();

        $user->updatePhoneMobile('+74454545', true);

        $this->assertEquals('+74454545', $user->getPhonemob());
        $this->assertTrue($user->isSms());
    }
}