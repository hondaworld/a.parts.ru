<?php

namespace App\Tests\Model\User\User\Beznal;

use App\Tests\Builder\Beznal\UserBeznalBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class MainBeznalTest extends TestCase
{
    public function testMainBeznal()
    {
        $user = (new UserBuilder())->build();
        $isMain = $user->checkIsMainBeznal(false);

        $beznal1 = (new UserBeznalBuilder($user, $isMain))->build();
        $user->assignBeznal($beznal1);

        $this->assertTrue($beznal1->isMain());
        $this->assertEquals($beznal1, $user->getMainBeznal());
    }

    public function testMainTrueBeznal()
    {
        $user = (new UserBuilder())->build();
        $isMain = $user->checkIsMainBeznal(true);

        $beznal1 = (new UserBeznalBuilder($user, $isMain))->build();
        $user->assignBeznal($beznal1);

        $this->assertTrue($beznal1->isMain());
        $this->assertEquals($beznal1, $user->getMainBeznal());
    }

    public function testMainSomeBeznals()
    {
        $user = (new UserBuilder())->build();
        $isMain = $user->checkIsMainBeznal(false);

        $beznal1 = (new UserBeznalBuilder($user, $isMain))->build();
        $user->assignBeznal($beznal1);

        $isMain = $user->checkIsMainBeznal(false);
        $beznal2 = (new UserBeznalBuilder($user, $isMain))->build();
        $user->assignBeznal($beznal1);

        $this->assertTrue($beznal1->isMain());
        $this->assertFalse($beznal2->isMain());
        $this->assertEquals($beznal1, $user->getMainBeznal());
    }

    public function testMainSomeTrueBeznals()
    {
        $user = (new UserBuilder())->build();
        $isMain = $user->checkIsMainBeznal(false);

        $beznal1 = (new UserBeznalBuilder($user, $isMain))->build();
        $user->assignBeznal($beznal1);

        $isMain = $user->checkIsMainBeznal(true);
        $beznal2 = (new UserBeznalBuilder($user, $isMain))->build();
        $user->assignBeznal($beznal1);

        $this->assertFalse($beznal1->isMain());
        $this->assertTrue($beznal2->isMain());
        $this->assertEquals($beznal2, $user->getMainBeznal());
    }
}