<?php

namespace App\Model\User\UseCase\Discount\Edit;

use App\Model\Shop\Entity\Discount\Discount;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $discountID;
    /**
     * @Assert\NotBlank()
     */
    public $summ;

    /**
     * @Assert\NotBlank()
     */
    public $discount_spare;

    /**
     * @Assert\NotBlank()
     */
    public $discount_service;

    public function __construct(int $discountID)
    {
        $this->discountID = $discountID;
    }

    public static function fromEntity(Discount $discount): self
    {
        $command = new self($discount->getId());
        $command->summ = $discount->getSumm();
        $command->discount_spare = $discount->getDiscountSpare();
        $command->discount_service = $discount->getDiscountService();
        return $command;
    }
}
