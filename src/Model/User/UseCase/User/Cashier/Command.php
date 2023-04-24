<?php

namespace App\Model\User\UseCase\User\Cashier;

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
     * @var \App\Model\User\UseCase\User\User
     * @Assert\Valid()
     */
    public $user;

    public $contacts;

    public $beznals;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user, array $contacts, array $beznals): self
    {
        $command = new self($user->getId());
        $command->user = $user->getCashUser() ? new \App\Model\User\UseCase\User\User(
            $user->getCashUser()->getId(),
            $user->getCashUser()->getFullNameWithPhoneMobileAndOrganization(),
            $user->getCashUserContact() ? $user->getCashUserContact()->getId() : 0,
            $user->getCashUserBeznal() ? $user->getCashUserBeznal()->getId() : 0
        ) : new \App\Model\User\UseCase\User\User();
        $command->contacts = $contacts;
        $command->beznals = $beznals;
        return $command;
    }
}
