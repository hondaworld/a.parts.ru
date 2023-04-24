<?php

namespace App\Tests\Model\User\Template;

use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use PHPUnit\Framework\TestCase;

class TemplateGroupTest extends TestCase
{
    public function testCreate(): void
    {
        $templateGroup = new TemplateGroup('Наименование группы');
        $this->assertEquals('Наименование группы', $templateGroup->getName());
    }

    public function testUpdate(): void
    {
        $templateGroup = new TemplateGroup('Наименование группы');
        $templateGroup->update('Другое наименование группы');
        $this->assertEquals('Другое наименование группы', $templateGroup->getName());
    }
}