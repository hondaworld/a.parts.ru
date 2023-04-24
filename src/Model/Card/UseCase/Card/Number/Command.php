<?php

namespace App\Model\Card\UseCase\Card\Number;

use App\Model\Card\Entity\Card\ZapCard;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{

    /**
     * @Assert\NotBlank()
     */
    public $zapCardID;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="30",
     *     maxMessage="Номер должен быть не больше 30 символов"
     * )
     */
    public $number;

    /**
     * @Assert\NotBlank()
     */
    public $createrID;

    public function __construct(int $zapCardID)
    {
        $this->zapCardID = $zapCardID;
    }

    public static function fromEntity(ZapCard $zapCard): self
    {
        $command = new self($zapCard->getId());
        $command->number = $zapCard->getNumber()->getValue();
        $command->createrID = $zapCard->getCreater()->getId();
        return $command;
    }
}
