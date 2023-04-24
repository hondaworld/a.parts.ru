<?php

namespace App\Model\User\UseCase\User\Password;

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
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min="6",
     *     minMessage="Пароль должен содержать не меньше 6 символов"
     * )
     */
    public $password;

    public function __construct(int $userID)
    {
        $this->userID = $userID;
    }
}
