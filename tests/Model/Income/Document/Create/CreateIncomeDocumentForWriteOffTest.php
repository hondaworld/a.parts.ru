<?php

namespace App\Tests\Model\Income\Document\Create;

use App\Model\Document\Entity\Type\DocumentType;
use App\Model\Finance\Entity\Nalog\Nalog;
use App\Model\Firm\Entity\Firm\Firm;
use App\Model\Income\Entity\Document\Document;
use App\Model\Income\Entity\Document\IncomeDocument;
use App\Model\Income\Entity\Document\Osn;
use App\Model\Manager\Entity\Manager\Manager;
use PHPUnit\Framework\TestCase;

class CreateIncomeDocumentForWriteOffTest extends TestCase
{
    public function testCreate(): void
    {
        $manager = $this->createMock(Manager::class);
        $firm = new Firm('Тестовая компания', 'ООО "Тестовая компания"', null, null, null, null, false, true, new Nalog('Налог'), null, null);
        $document_num = 1;
        $document = new Document($document_num, 'pre', 'suf');
        $osn = new Osn('Основание');

        $incomeDocument = new IncomeDocument(new DocumentType('Тест', 'Тест', null), $document, $manager, null, null, null, $firm, $osn);

        $this->assertEquals('Тест', $incomeDocument->getDocumentType()->getName());
        $this->assertEquals(1, $incomeDocument->getDocument()->getNum());
        $this->assertEquals('pre-1-suf', $incomeDocument->getDocument()->getDocumentNum());
        $this->assertEquals($firm, $incomeDocument->getFirm());
        $this->assertNull($incomeDocument->getProvider());
        $this->assertNull($incomeDocument->getUser());
        $this->assertEquals('Основание', $incomeDocument->getOsn()->getName());
    }
}