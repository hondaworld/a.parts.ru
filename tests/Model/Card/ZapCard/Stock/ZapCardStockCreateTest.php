<?php

namespace App\Tests\Model\Card\ZapCard\Stock;

use App\Model\Card\Entity\Stock\ZapCardStock;
use PHPUnit\Framework\TestCase;

class ZapCardStockCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $zapCadStock = new ZapCardStock('Название', 'Текст');
        $this->assertEquals('Название', $zapCadStock->getName());
        $this->assertEquals('Текст', $zapCadStock->getText());
    }

    public function testCreateNoText(): void
    {
        $zapCadStock = new ZapCardStock('Название', null);
        $this->assertEquals('Название', $zapCadStock->getName());
        $this->assertEquals('', $zapCadStock->getText());
    }
}