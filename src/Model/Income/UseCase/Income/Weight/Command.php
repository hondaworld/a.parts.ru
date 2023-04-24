<?php

namespace App\Model\Income\UseCase\Income\Weight;

use App\Model\Card\Entity\Card\DetailNumber;
use App\Model\Detail\Entity\Creater\Creater;
use App\Model\Income\Entity\Income\Income;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var DetailNumber
     */
    public DetailNumber $number;

    /**
     * @var Creater
     */
    public Creater $creater;

    /**
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/^\d+([.|,]\d+)?$/",
     *     message="Значение должно быть дробным числом"
     * )
     */
    public float $weight;

    public bool $weightIsReal = false;

    public array $incomes = [];

    public static function fromEntity(Income $income): self
    {
        $command = new self();
        $command->number = $income->getZapCard()->getNumber();
        $command->creater = $income->getZapCard()->getCreater();
        return $command;
    }
}
