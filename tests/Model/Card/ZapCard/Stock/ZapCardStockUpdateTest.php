<?php

namespace App\Tests\Model\Card\ZapCard\Stock;

use App\Model\Card\Entity\Stock\ZapCardStock;
use PHPUnit\Framework\TestCase;

class ZapCardStockUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $zapCadStock = new ZapCardStock('Название', 'Текст');

        $d = new \DateTime();
        $zapCadStock->update('Название 1', 'Текст 1', $d);
        $this->assertEquals('Название 1', $zapCadStock->getName());
        $this->assertEquals('Текст 1', $zapCadStock->getText());
        $this->assertEquals($d, $zapCadStock->getDateofadded());
    }

    public function testUpdateNoText(): void
    {
        $zapCadStock = new ZapCardStock('Название', 'Текст');
        $d = new \DateTime();
        $zapCadStock->update('Название 1', null, $d);
        $this->assertEquals('Название 1', $zapCadStock->getName());
        $this->assertEquals('', $zapCadStock->getText());
        $this->assertEquals($d, $zapCadStock->getDateofadded());
    }
}