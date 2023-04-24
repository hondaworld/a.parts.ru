<?php

namespace App\Tests\Model\Firm\Schet;

use App\Tests\Builder\Contact\UserContactBuilder;
use App\Tests\Builder\Firm\SchetBuilder;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class SchetUpdateTest extends TestCase
{
    public function testUpdatePayUrl(): void
    {
        $schet = (new SchetBuilder())->build();

        $this->assertEquals('', $schet->getPayUrl());
        $schet->updatePayUrl('eee.ru');
        $this->assertEquals('eee.ru', $schet->getPayUrl());
        $schet->updatePayUrl(null);
        $this->assertEquals('', $schet->getPayUrl());
    }

    public function testUpdateUserContact(): void
    {
        $schet = (new SchetBuilder())->build();

        $this->assertNull($schet->getExpUserContact());

        $contact = (new UserContactBuilder((new UserBuilder())->build(), false))->build();

        $schet->updateUserContact($contact);

        $this->assertEquals($contact, $schet->getExpUserContact());
    }
}