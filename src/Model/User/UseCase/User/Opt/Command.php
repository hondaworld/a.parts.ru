<?php

namespace App\Model\User\UseCase\User\Opt;

use App\Model\User\Entity\User\User;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $userID;

    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $optID;

    /**
     * @var int
     */
    public $shopPayTypeID;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user): self
    {
        $command = new self($user->getId());
        $command->optID = $user->getOpt()->getId();
        $command->shopPayTypeID = $user->getShopPayType() ? $user->getShopPayType()->getId() : null;
        return $command;
    }
}
