<?php

namespace App\Tests\Model\Card\ZapCard\Description;

use App\Model\Contact\Entity\Country\Country;
use App\Tests\Builder\Card\ZapCardBuilder;
use PHPUnit\Framework\TestCase;

class ZapCardDescriptionTest extends TestCase
{
    public function testDescription(): void
    {
        $zapCard = (new ZapCardBuilder())->build();

        $zapCard->updateDescription('текст 1', 'текст 2');
        $this->assertEquals('текст 1', $zapCard->getText());
        $this->assertEquals('текст 2', $zapCard->getTextFake());

        $zapCard->updateDescription( null, null);
        $this->assertEquals('', $zapCard->getText());
        $this->assertEquals('', $zapCard->getTextFake());
    }
}