<?php

namespace App\Tests\Model\Card\ABC;

use App\Model\Card\Entity\Abc\Abc;
use PHPUnit\Framework\TestCase;

class ABCUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $abc = new Abc('A', 'Описание');

        $abc->update('B', 'Другое описание');

        $this->assertEquals('B', $abc->getAbc());
        $this->assertEquals('Другое описание', $abc->getDescription());
    }
}