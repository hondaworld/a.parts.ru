<?php

namespace App\Model\Detail\UseCase\Zamena\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="20",
     *     minMessage="Номер должен быть не больше 20 символов"
     * )
     */
    public $number;

    /**
     * @Assert\NotBlank()
     */
    public $createrID;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="20",
     *     minMessage="Номер должен быть не больше 20 символов"
     * )
     */
    public $number2;

    /**
     * @Assert\NotBlank()
     */
    public $createrID2;
}
