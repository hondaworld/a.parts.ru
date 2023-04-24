<?php

namespace App\Tests\Model\Firm\Schet\Document;

use App\Model\Firm\Entity\Schet\Document;
use PHPUnit\Framework\TestCase;

class SchetDocumentCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $document = new Document();

        $this->assertEquals(0, $document->getSchetNum());
        $this->assertEquals('', $document->getDocumentPrefix());
        $this->assertEquals('', $document->getDocumentSufix());
        $this->assertEquals(0, $document->getDocumentNum());
    }

    public function testCreateNull(): void
    {
        $document = new Document(null, null, null);

        $this->assertEquals(0, $document->getSchetNum());
        $this->assertEquals('', $document->getDocumentPrefix());
        $this->assertEquals('', $document->getDocumentSufix());
        $this->assertEquals(0, $document->getDocumentNum());
    }

    public function testCreateNum(): void
    {
        $document = new Document(3);

        $this->assertEquals(3, $document->getSchetNum());
        $this->assertEquals('', $document->getDocumentPrefix());
        $this->assertEquals('', $document->getDocumentSufix());
        $this->assertEquals(3, $document->getDocumentNum());
    }

    public function testCreatePrefix(): void
    {
        $document = new Document(3, 'prefix');

        $this->assertEquals(3, $document->getSchetNum());
        $this->assertEquals('prefix', $document->getDocumentPrefix());
        $this->assertEquals('', $document->getDocumentSufix());
        $this->assertEquals('prefix-3', $document->getDocumentNum());
    }

    public function testCreateSufix(): void
    {
        $document = new Document(3, 'prefix', 'sufix');

        $this->assertEquals(3, $document->getSchetNum());
        $this->assertEquals('prefix', $document->getDocumentPrefix());
        $this->assertEquals('sufix', $document->getDocumentSufix());
        $this->assertEquals('prefix-3-sufix', $document->getDocumentNum());
    }
}