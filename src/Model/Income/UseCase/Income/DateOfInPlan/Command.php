<?php

namespace App\Model\Income\UseCase\Income\DateOfInPlan;

use App\Model\Income\Entity\Income\Income;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $incomeID;

    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     */
    public $dateofinplan;

    public function __construct(int $incomeID)
    {
        $this->incomeID = $incomeID;
    }

    public static function fromEntity(Income $income): self
    {
        $command = new self($income->getId());
        $command->dateofinplan = $income->getDateofinplan();
        return $command;
    }
}
