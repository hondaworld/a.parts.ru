<?php

namespace App\Model\Order\UseCase\Good\QuantityChange;

use App\Model\Income\Entity\Income\Income;
use App\Model\Order\Entity\Good\OrderGood;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     * @Assert\Positive()
     */
    public $goodID;

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

    public function __construct(int $goodID)
    {
        $this->goodID = $goodID;
    }

    public static function fromEntity(OrderGood $orderGood): self
    {
        $command = new self($orderGood->getId());
        $command->quantity = $orderGood->getQuantity();
        return $command;
    }
}
