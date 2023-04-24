<?php

namespace App\Tests\Model\Expense\SchetFakKor;

use App\Model\Expense\Entity\SchetFakKor\Document;
use App\Model\Expense\Entity\SchetFakKor\SchetFakKor;
use App\Tests\Builder\Expense\SchetFakBuilder;
use App\Tests\Builder\Firm\FirmBuilder;
use PHPUnit\Framework\TestCase;

class SchetFakKorCreateTest extends TestCase
{
    public function testCreate(): void
    {
        $schetFak = (new SchetFakBuilder())->build();
        $document = new Document(5, 'pre', 'suf');
        $firm = (new FirmBuilder(true))->build();

        $schetFakKor = new SchetFakKor($document, $firm, $schetFak);

        $this->assertEquals($firm, $schetFakKor->getFirm());
        $this->assertEquals($schetFak, $schetFakKor->getSchetFak());
        $this->assertEquals(5, $schetFakKor->getDocument()->getNum());
        $this->assertEquals('pre', $schetFakKor->getDocument()->getPrefix());
        $this->assertEquals('suf', $schetFakKor->getDocument()->getSufix());

        $this->assertCount(1, $schetFakKor->getSchetFaks());
    }
}