<?php

namespace App\Tests\Model\Document\Document;

use App\Model\Document\Entity\Document\Document;
use App\Model\Document\Entity\Identification\DocumentIdentification;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\User\Entity\User\User;
use PHPUnit\Framework\TestCase;

class ByUserCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $documentIdentification = new DocumentIdentification('Документ');
        $user = $this->createMock(User::class);
        $d = new \DateTime();
        $document = new Document($user, $documentIdentification, '1234', '567890', 'Организация', $d, 'Описание', true);

        $this->assertEquals($user, $document->getUser());
        $this->assertNull($document->getFirm());
        $this->assertNull($document->getManager());
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
        $user = $this->createMock(User::class);
        $document = new Document($user, $documentIdentification, '1234', '567890', null, null, null, false);

        $this->assertEquals($user, $document->getUser());
        $this->assertNull($document->getFirm());
        $this->assertNull($document->getManager());
        $this->assertEquals($documentIdentification, $document->getIdentification());
        $this->assertEquals('1234', $document->getSerial());
        $this->assertEquals('567890', $document->getNumber());
        $this->assertEquals('', $document->getOrganization());
        $this->assertNull($document->getDateofdoc());
        $this->assertEquals('', $document->getDescription());
        $this->assertFalse($document->isMain());
    }
}