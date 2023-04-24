<?php

namespace App\Model\User\UseCase\User\Discount;

use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\Contact\TownFetcher;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $userID;

    /**
     * @var string
     */
    public $discountParts;

    /**
     * @var string
     */
    public $discountService;

    /**
     * @var int
     */
    public $schetDays;
    /**
     * @var bool
     */
    public $is_not_update_discount;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user): self
    {
        $command = new self($user->getId());
        $command->discountParts = $user->getDiscountParts();
        $command->discountService = $user->getDiscountService();
        $command->schetDays = $user->getSchetDays();
        $command->is_not_update_discount = $user->isNotUpdateDiscount();
        return $command;
    }
}
