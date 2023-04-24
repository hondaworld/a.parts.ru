<?php

namespace App\Tests\Model\Beznal\Bank;

use App\Model\Beznal\Entity\Bank\Bank;
use PHPUnit\Framework\TestCase;

class CreateBankTest extends TestCase
{
    public function testCreate(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');

        $this->assertEquals('123456', $bank->getBik());
        $this->assertEquals('Банк', $bank->getName());
        $this->assertEquals('40700000001', $bank->getKorschet());
        $this->assertEquals('Московская, 1', $bank->getAddress());
        $this->assertEquals('Описание', $bank->getDescription());
    }

    public function testUpdate(): void
    {
        $bank = new Bank('123456', 'Банк', '40700000001', 'Московская, 1', 'Описание');
        $bank->update('123457', 'Банк 1', '40700000002', 'Московская, 2', 'Описание 1');

        $this->assertEquals('123457', $bank->getBik());
        $this->assertEquals('Банк 1', $bank->getName());
        $this->assertEquals('40700000002', $bank->getKorschet());
        $this->assertEquals('Московская, 2', $bank->getAddress());
        $this->assertEquals('Описание 1', $bank->getDescription());
    }
}