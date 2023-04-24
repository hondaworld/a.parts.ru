<?php

namespace App\Tests\Model\User\User\Contact;

use App\Tests\Builder\Contact\UserContactBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class MainContactTest extends TestCase
{
    public function testMainContact()
    {
        $user = (new UserBuilder())->build();
        $isMain = $user->checkIsMainContact(false);

        $contact1 = (new UserContactBuilder($user, $isMain))->build();
        $user->assignContact($contact1);

        $this->assertTrue($contact1->isMain());
        $this->assertEquals($contact1, $user->getMainContact());
    }

    public function testMainTrueContact()
    {
        $user = (new UserBuilder())->build();
        $isMain = $user->checkIsMainContact(true);

        $contact1 = (new UserContactBuilder($user, $isMain))->build();
        $user->assignContact($contact1);

        $this->assertTrue($contact1->isMain());
        $this->assertEquals($contact1, $user->getMainContact());
    }

    public function testMainSomeContacts()
    {
        $user = (new UserBuilder())->build();
        $isMain = $user->checkIsMainContact(false);

        $contact1 = (new UserContactBuilder($user, $isMain))->build();
        $user->assignContact($contact1);

        $isMain = $user->checkIsMainContact(false);
        $contact2 = (new UserContactBuilder($user, $isMain))->build();
        $user->assignContact($contact1);

        $this->assertTrue($contact1->isMain());
        $this->assertFalse($contact2->isMain());
        $this->assertEquals($contact1, $user->getMainContact());
    }

    public function testMainSomeTrueContacts()
    {
        $user = (new UserBuilder())->build();
        $isMain = $user->checkIsMainContact(false);

        $contact1 = (new UserContactBuilder($user, $isMain))->build();
        $user->assignContact($contact1);

        $isMain = $user->checkIsMainContact(true);
        $contact2 = (new UserContactBuilder($user, $isMain))->build();
        $user->assignContact($contact1);

        $this->assertFalse($contact1->isMain());
        $this->assertTrue($contact2->isMain());
        $this->assertEquals($contact2, $user->getMainContact());
    }
}