<?php

namespace App\Model\User\UseCase\User\GetterFirmContr;

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
    public $firmcontrID;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user): self
    {
        $command = new self($user->getId());
        $command->firmcontrID = $user->getGruzFirmContr() ? $user->getGruzFirmContr()->getId() : null;
        return $command;
    }
}
