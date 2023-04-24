<?php

namespace App\Model\Manager\UseCase\Manager\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min="5",
     *     minMessage="Логин должен содержать не меньше 5 символов"
     * )
     */
    public $login;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Length(
     *     min="6",
     *     minMessage="Пароль должен содержать не меньше 6 символов"
     * )
     */
    public $password;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $firstname;

    /**
     * @var string
     * @Assert\NotBlank()
     */
    public $lastname;

    /**
     * @var string
     */
    public $middlename;

    /**
     * @var array
     */
    public $groups;

}
