<?php

namespace App\Model\Income\UseCase\Document\Weight;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Detail\Entity\Creater\Creater;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var DetailNumber
     */
    public $number;

    /**
     * @var Creater
     */
    public $creater;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public $weight;

    public $weightIsReal = true;

    public static function fromZapCard(ZapCard $zapCard): self
    {
        $command = new self();
        $command->number = $zapCard->getNumber();
        $command->creater = $zapCard->getCreater();
        return $command;
    }
}
