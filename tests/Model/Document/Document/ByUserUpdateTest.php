<?php

namespace App\Tests\Model\Document\Document;

use App\Model\Document\Entity\Document\Document;
use App\Model\Document\Entity\Identification\DocumentIdentification;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\User\Entity\User\User;
use PHPUnit\Framework\TestCase;

class ByUserUpdateTest extends TestCase
{
    public function testCreate(): void
    {
        $documentIdentification = new DocumentIdentification('Документ');
        $user = $this->createMock(User::class);
        $d = new \DateTime();
        $document = new Document($user, $documentIdentification, '1234', '567890', 'Организация', $d, 'Описание', true);

        $d1 = new \DateTime('+1 day');
        $documentIdentification1 = new DocumentIdentification('Документ 1');
        $document->update($documentIdentification1, '4321', '98765', 'Новая организация', $d1, 'Новое описание', false);

        $this->assertEquals($user, $document->getUser());
        $this->assertNull($document->getFirm());
        $this->assertNull($document->getManager());
        $this->assertEquals($documentIdentification1, $document->getIdentification());
        $this->assertEquals('4321', $document->getSerial());
        $this->assertEquals('98765', $document->getNumber());
        $this->assertEquals('Новая организация', $document->getOrganization());
        $this->assertEquals($d1, $document->getDateofdoc());
        $this->assertEquals('Новое описание', $document->getDescription());
        $this->assertFalse($document->isMain());
    }

    public function testCreateNull(): void
    {
        $documentIdentification = new DocumentIdentification('Документ');
        $user = $this->createMock(User::class);
        $d = new \DateTime();
        $document = new Document($user, $documentIdentification, '1234', '567890', 'Организация', $d, 'Описание', false);

        $documentIdentification1 = new DocumentIdentification('Документ 1');
        $document->update($documentIdentification1, '4321', '98765', null, null, null, true);

        $this->assertEquals($user, $document->getUser());
        $this->assertNull($document->getFirm());
        $this->assertNull($document->getManager());
        $this->assertEquals($documentIdentification1, $document->getIdentification());
        $this->assertEquals('4321', $document->getSerial());
        $this->assertEquals('98765', $document->getNumber());
        $this->assertEquals('', $document->getOrganization());
        $this->assertNull($document->getDateofdoc());
        $this->assertEquals('', $document->getDescription());
        $this->assertTrue($document->isMain());
    }
}