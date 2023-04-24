<?php

namespace App\Tests\Model\Firm\Schet\Document\DocumentDate;

use App\Tests\Builder\Firm\SchetBuilder;
use PHPUnit\Framework\TestCase;

class DocumentDateUpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $schet = (new SchetBuilder())->build();
        $dateofadded = new \DateTime('+1 day');
        $schet->updateDocumentDate($dateofadded, 'Комментарий');

        $this->assertEquals($dateofadded, $schet->getDateofadded());
        $this->assertEquals('Комментарий', $schet->getComment());
    }

    public function testUpdateNull(): void
    {
        $schet = (new SchetBuilder())->build();
        $dateofadded = new \DateTime('+1 day');
        $schet->updateDocumentDate($dateofadded, null);

        $this->assertEquals($dateofadded, $schet->getDateofadded());
        $this->assertEquals('', $schet->getComment());
    }
}