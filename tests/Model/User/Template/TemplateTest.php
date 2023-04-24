<?php

namespace App\Tests\Model\User\Template;

use App\Model\User\Entity\Template\Template;
use App\Model\User\Entity\TemplateGroup\TemplateGroup;
use PHPUnit\Framework\TestCase;

class TemplateTest extends TestCase
{
    public function testCreate(): void
    {
        $templateGroup = new TemplateGroup('Наименование группы');
        $template = new Template($templateGroup, 'Название группы', 'Название темы', 'Текст шаблона');
        $this->assertEquals($templateGroup, $template->getTemplateGroup());
        $this->assertEquals('Название группы', $template->getName());
        $this->assertEquals('Название темы', $template->getSubject());
        $this->assertEquals('Текст шаблона', $template->getText());
    }

    public function testUpdate(): void
    {
        $templateGroup = new TemplateGroup('Наименование группы');
        $templateGroup1 = new TemplateGroup('Наименование группы 1');
        $template = new Template($templateGroup, 'Название группы', 'Название темы', 'Текст шаблона');
        $template->update($templateGroup1, 'Другое наименование группы', 'Другая тема', 'Другой текст');
        $this->assertEquals($templateGroup1, $template->getTemplateGroup());
        $this->assertEquals('Другое наименование группы', $template->getName());
        $this->assertEquals('Другая тема', $template->getSubject());
        $this->assertEquals('Другой текст', $template->getText());
    }

    public function testGetText(): void
    {
        $templateGroup = new TemplateGroup('Наименование группы');
        $template = new Template($templateGroup, 'Название группы', 'Название темы', 'Номер телефона {phone} клиента {name} {lastname}.');

        $this->assertEquals('Номер телефона +7910456456456 клиента Сергей {lastname}.', $template->getText(['phone' => '+7910456456456', 'name' => 'Сергей']));
    }
}