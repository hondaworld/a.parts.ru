<?php

namespace App\Tests\Model\Firm\Schet\Document;

use App\Model\Firm\Entity\Schet\Document;
use PHPUnit\Framework\TestCase;

class SchetDocumentUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $document = new Document(3, 'prefix', 'sufix');

        $document->update(4, 'pr', 'su');

        $this->assertEquals(4, $document->getSchetNum());
        $this->assertEquals('pr', $document->getDocumentPrefix());
        $this->assertEquals('su', $document->getDocumentSufix());
        $this->assertEquals('pr-4-su', $document->getDocumentNum());

    }

    public function testUpdateEmpty(): void
    {
        $document = new Document(3, 'prefix', 'sufix');

        $document->update(4);

        $this->assertEquals(4, $document->getSchetNum());
        $this->assertEquals('', $document->getDocumentPrefix());
        $this->assertEquals('', $document->getDocumentSufix());
        $this->assertEquals('4', $document->getDocumentNum());

    }

    public function testUpdateNull(): void
    {
        $document = new Document(3, 'prefix', 'sufix');

        $document->update(4, null, null);

        $this->assertEquals(4, $document->getSchetNum());
        $this->assertEquals('', $document->getDocumentPrefix());
        $this->assertEquals('', $document->getDocumentSufix());
        $this->assertEquals('4', $document->getDocumentNum());

    }

    public function testUpdatePrefixes(): void
    {
        $document = new Document(3, 'prefix', 'sufix');

        $document->updatePrefixes('pr', 'su');

        $this->assertEquals('pr', $document->getDocumentPrefix());
        $this->assertEquals('su', $document->getDocumentSufix());

    }
}