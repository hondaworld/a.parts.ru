<?php

namespace App\Tests\Model\Document\Document;

use App\Model\Document\Entity\Document\Document;
use App\Model\Document\Entity\Identification\DocumentIdentification;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Manager\Entity\Manager\Manager;
use PHPUnit\Framework\TestCase;

class ByManagerCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $documentIdentification = new DocumentIdentification('Документ');
        $manager = $this->createMock(Manager::class);
        $d = new \DateTime();
        $document = new Document($manager, $documentIdentification, '1234', '567890', 'Организация', $d, 'Описание', true);

        $this->assertEquals($manager, $document->getManager());
        $this->assertNull($document->getUser());
        $this->assertNull($document->getFirm());
        $this->assertEquals($documentIdentification, $document->getIdentification());
        $this->assertEquals('1234', $document->getSerial());
        $this->assertEquals('567890', $document->getNumber());
        $this->assertEquals('Организация', $document->getOrganization());
        $this->assertEquals($d, $document->getDateofdoc());
        $this->assertEquals('Описание', $document->getDescription());
        $this->assertTrue($document->isMain());
    }

    public function testCreateNull(): void
    {
        $documentIdentification = new DocumentIdentification('Документ');
        $manager = $this->createMock(Manager::class);
        $document = new Document($manager, $documentIdentification, '1234', '567890', null, null, null, false);

        $this->assertEquals($manager, $document->getManager());
        $this->assertNull($document->getUser());
        $this->assertNull($document->getFirm());
        $this->assertEquals($documentIdentification, $document->getIdentification());
        $this->assertEquals('1234', $document->getSerial());
        $this->assertEquals('567890', $document->getNumber());
        $this->assertEquals('', $document->getOrganization());
        $this->assertNull($document->getDateofdoc());
        $this->assertEquals('', $document->getDescription());
        $this->assertFalse($document->isMain());
    }
}