<?php

namespace App\Tests\Model\Shop\Reseller;

use App\Model\Shop\Entity\Reseller\Reseller;
use PHPUnit\Framework\TestCase;

class ResellerTest extends TestCase
{
    public function testCreate(): void
    {
        $reseller = new Reseller('Название');
        $this->assertEquals('Название', $reseller->getName());
    }

    public function testUdate(): void
    {
        $reseller = new Reseller('Название');
        $reseller->update('Название новое');
        $this->assertEquals('Название новое', $reseller->getName());
    }
}