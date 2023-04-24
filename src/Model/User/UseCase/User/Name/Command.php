<?php

namespace App\Model\User\UseCase\User\Name;

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
    public $name;
    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $firstname;

    /**
     * @var string
     */
    public $lastname;

    /**
     * @var string
     */
    public $middlename;

    /**
     * @var Town
     * @Assert\Valid()
     */
    public $town;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user, TownFetcher $townFetcher): self
    {
        $command = new self($user->getId());
        $command->name = $user->getName();
        $command->firstname = $user->getUserName()->getFirstname();
        $command->lastname = $user->getUserName()->getLastname();
        $command->middlename = $user->getUserName()->getMiddlename();
        $command->town = $user->getTown() != null ? new Town($user->getTown()->getId(), $townFetcher->findTownsById($user->getTown()->getId())->getTownFullName()) : new Town(0, '');
        return $command;
    }
}
