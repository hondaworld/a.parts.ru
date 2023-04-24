<?php

namespace App\Model\User\UseCase\User\Manager;

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
     */
    public $managerID;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user): self
    {
        $command = new self($user->getId());
        $command->managerID = $user->getOwner() ? $user->getOwner()->getId() : null;
        return $command;
    }
}
