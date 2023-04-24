<?php

namespace App\Tests\Model\User\Firmcontr\Ur;

use App\Model\User\Entity\FirmContr\Ur;
use PHPUnit\Framework\TestCase;

class FirmcontrUrCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $ur = new Ur('Контрагент', '770012343', '770000001', '15484844989', '1545465454', true);

        $this->assertEquals('Контрагент', $ur->getOrganization());
        $this->assertEquals('770012343', $ur->getInn());
        $this->assertEquals('770000001', $ur->getKpp());
        $this->assertEquals('15484844989', $ur->getOkpo());
        $this->assertEquals('1545465454', $ur->getOgrn());
        $this->assertTrue($ur->isNDS());
        $this->assertEquals('Контрагент, ИНН/КПП 770012343/770000001', $ur->getOrganizationWithInnAndKpp());
    }

    public function testCreateNull(): void
    {
        $ur = new Ur(null, null, null, null, null, null);

        $this->assertEquals('', $ur->getOrganization());
        $this->assertEquals('', $ur->getInn());
        $this->assertEquals('', $ur->getKpp());
        $this->assertEquals('', $ur->getOkpo());
        $this->assertEquals('', $ur->getOgrn());
        $this->assertFalse($ur->isNDS());
        $this->assertEquals(', ИНН/КПП /', $ur->getOrganizationWithInnAndKpp());
    }

    public function testCreateEmpty(): void
    {
        $ur = new Ur();

        $this->assertEquals('', $ur->getOrganization());
        $this->assertEquals('', $ur->getInn());
        $this->assertEquals('', $ur->getKpp());
        $this->assertEquals('', $ur->getOkpo());
        $this->assertEquals('', $ur->getOgrn());
        $this->assertFalse($ur->isNDS());
        $this->assertEquals(', ИНН/КПП /', $ur->getOrganizationWithInnAndKpp());
    }
}