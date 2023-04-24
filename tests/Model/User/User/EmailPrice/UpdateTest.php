<?php

namespace App\Tests\Model\User\User\EmailPrice;

use App\Model\User\Entity\User\EmailPrice;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $user = (new UserBuilder())->build();

        $anotherEmail = new EmailPrice('info@domen.ru', 0, false, false);
        $another1Email = new EmailPrice('info1@domen.ru', 0, false, false);
        $user->updateEmailPrice(new EmailPrice('info@domen.ru', 1, true, true));

        $this->assertTrue($user->getEmailPrice()->isEqual($anotherEmail));
        $this->assertFalse($user->getEmailPrice()->isEqual($another1Email));

        $this->assertEquals('info@domen.ru', $user->getEmailPrice()->getValue());
        $this->assertEquals(1, $user->getEmailPrice()->getZapSkladID());
        $this->assertTrue($user->getEmailPrice()->isPrice());
        $this->assertTrue($user->getEmailPrice()->isPriceSummary());
    }
}