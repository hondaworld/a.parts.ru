<?php

namespace App\Model\Card\UseCase\Card\Description;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $zapCardID;

    public $text;

    public $text_fake;

    public function __construct(int $zapCardID)
    {
        $this->zapCardID = $zapCardID;
    }

    public static function fromEntity(ZapCard $zapCard): self
    {
        $command = new self($zapCard->getId());
        $command->text = $zapCard->getText();
        $command->text_fake = $zapCard->getTextFake();
        return $command;
    }
}
