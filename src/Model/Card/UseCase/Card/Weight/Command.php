<?php

namespace App\Model\Card\UseCase\Card\Weight;

use App\Model\Card\Entity\Card\ZapCard;
use App\Model\Detail\Entity\Weight\Weight;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $zapCardID;

    /**
     * @var string
     */
    public $weight;

    /**
     * @var boolean
     */
    public $weightIsReal;

    public function __construct(int $zapCardID)
    {
        $this->zapCardID = $zapCardID;
    }

    public static function fromEntity(ZapCard $zapCard, ?Weight $weight): self
    {
        $command = new self($zapCard->getId());

        if ($weight) {
            $command->weight = $weight->getWeight();
            $command->weightIsReal = $weight->getWeightIsReal();
        }

        return $command;
    }
}
