<?php

namespace App\Model\User\UseCase\User\Email;

use App\Model\User\Entity\User\User;
use App\Model\User\UseCase\User\Town;
use App\ReadModel\Contact\TownFetcher;
use App\ReadModel\User\UserEmailStatusFetcher;
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
     * @Assert\Email
     */
    public $email;

    public $isNotification;

    public $isActive;

    public $excludeEmailStatuses;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user, UserEmailStatusFetcher $emailStatusFetcher): self
    {
        $command = new self($user->getId());
        $command->email = $user->getEmail()->getValue();
        $command->isNotification = $user->getEmail()->isNotification();
        $command->isActive = $user->getEmail()->isActive();

        $arr = [];
        foreach ($emailStatusFetcher->assoc() AS $id => $status) {
            if (!in_array($id, $user->getExcludeEmailStatusIds())) $arr[] = $id;
        }
        $command->excludeEmailStatuses = $arr;
        return $command;
    }
}
