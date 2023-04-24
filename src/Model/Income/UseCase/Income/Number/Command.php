<?php

namespace App\Model\Income\UseCase\Income\Number;

use App\Model\Income\Entity\Income\Income;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $incomeID;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="30",
     *     maxMessage="Номер должен быть не больше 30 символов"
     * )
     */
    public $number;

    public function __construct(int $incomeID)
    {
        $this->incomeID = $incomeID;
    }

    public static function fromEntity(Income $income): self
    {
        $command = new self($income->getId());
        $command->number = $income->getZapCard()->getNumber()->getValue();
        return $command;
    }
}
