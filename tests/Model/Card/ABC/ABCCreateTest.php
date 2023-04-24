<?php

namespace App\Tests\Model\Card\ABC;

use App\Model\Card\Entity\Abc\Abc;
use PHPUnit\Framework\TestCase;

class ABCCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $abc = new Abc('A', 'Описание');

        $this->assertEquals('A', $abc->getAbc());
        $this->assertEquals('Описание', $abc->getDescription());
    }
}