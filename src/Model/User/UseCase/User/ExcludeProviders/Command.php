<?php

namespace App\Model\User\UseCase\User\ExcludeProviders;

use App\Model\Provider\Entity\Provider\Provider;
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
     * @var array
     */
    public $providers;

    /**
     * @var array
     */
    public $providersList;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }

    public static function fromEntity(User $user, array $providersList): self
    {
        $command = new self($user->getId());
        $command->providers = array_map(function (Provider $provider): int {
            return $provider->getId();
        }, $user->getExcludeProviders());
        $command->providersList = $providersList;
        return $command;
    }
}
