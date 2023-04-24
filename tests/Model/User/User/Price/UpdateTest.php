<?php

namespace App\Tests\Model\User\User\Price;

use App\Model\User\Entity\User\Price;
use App\Tests\Builder\User\UserBuilder;
use PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $user = (new UserBuilder())->build();

        $user->updatePrice(
            new Price(
                'info@domen.ru',
                'price@domen.ru',
                'filename.txt',
                true,
                2,
                '0',
                '1',
                '2',
                '3',
                ''
            ),
            null,
            null
        );

        $this->assertEquals('info@domen.ru', $user->getPrice()->getEmail());
        $this->assertEquals('price@domen.ru', $user->getPrice()->getEmailSend());
        $this->assertEquals('filename.txt', $user->getPrice()->getFilename());
        $this->assertTrue($user->getPrice()->isFirstLine());
        $this->assertEquals(2, $user->getPrice()->getLine());

        $this->assertEquals('0', $user->getPrice()->getOrderNum());
        $this->assertEquals('1', $user->getPrice()->getNumberNum());
        $this->assertEquals('2', $user->getPrice()->getCreaterNum());
        $this->assertEquals('3', $user->getPrice()->getQuantityNum());
        $this->assertEquals('', $user->getPrice()->getPriceNum());
    }
}