<?php

namespace App\Model\Manager\UseCase\Password;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var int
     * @Assert\NotBlank()
     */
    public $managerID;

    /**
     * @Assert\NotBlank()
     */
    public $password;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="6", minMessage="Пароль не может быть меньше 6 символов")
     */
    public $password_new;

    /**
     * @Assert\NotBlank()
     */
    public $password_confirm;

    public function __construct(string $managerID)
    {
        $this->managerID = $managerID;
    }
}
