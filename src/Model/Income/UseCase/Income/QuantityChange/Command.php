<?php

namespace App\Model\Income\UseCase\Income\QuantityChange;

use App\Model\Income\Entity\Income\Income;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Positive()
     */
    public $incomeID;

    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Positive()
     */
    public $quantity;

    /**
     * @var int
     * @Assert\NotBlank(
     *     message="Количество не указано"
     * )
     * @Assert\Positive()
     */
    public $quantity_new;

    public function __construct(int $incomeID)
    {
        $this->incomeID = $incomeID;
    }

    public static function fromEntity(Income $income): self
    {
        $command = new self($income->getId());
        $command->quantity = $income->getQuantity();
        return $command;
    }
}
