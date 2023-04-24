<?php

namespace App\Model\Card\UseCase\Card\ProfitZapCardFromComparePrice;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $zapCardID;

    /**
     * @var array
     */
    public $opts;

    /**
     * @var array
     */
    public $profits;

    public function __construct(int $zapCardID)
    {
        $this->zapCardID = $zapCardID;
    }

    public static function fromEntity(int $zapCardID, array $opts, array $profits): self
    {
        $command = new self($zapCardID);
        $command->opts = $opts;
        $command->profits = $profits;
        return $command;
    }
}
