<?php

namespace App\Tests\Model\Shop\DeleteReason;

use App\Model\Shop\Entity\DeleteReason\DeleteReason;
use PHPUnit\Framework\TestCase;

class DeleteReasonTest extends TestCase
{
    public function testCreate(): void
    {
        $deleteReason = new DeleteReason('Причина удаления', true);
        $this->assertEquals('Причина удаления', $deleteReason->getName());
        $this->assertTrue($deleteReason->isMain());
    }

    public function testUdate(): void
    {
        $deleteReason = new DeleteReason('Причина удаления', true);
        $deleteReason->update('Причина удаления новая', false);
        $this->assertEquals('Причина удаления новая', $deleteReason->getName());
        $this->assertFalse($deleteReason->isMain());
    }
}