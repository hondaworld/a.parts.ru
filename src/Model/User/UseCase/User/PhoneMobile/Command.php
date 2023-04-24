<?php

namespace App\Model\User\UseCase\User\PhoneMobile;

use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\User\Phonemob;
use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $userID;

    /**
     * @var Phonemob
     * @Assert\Valid()
     */
    public $phonemob;

    public $isSms;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user): self
    {
        $command = new self($user->getId());
        $command->phonemob = new Phonemob($user->getPhonemob());
        $command->isSms = $user->isSms();
        return $command;
    }
}
