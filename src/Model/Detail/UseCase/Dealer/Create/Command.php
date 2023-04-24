<?php

namespace App\Model\Detail\UseCase\Dealer\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="30",
     *     minMessage="Номер должен быть не больше 30 символов"
     * )
     */
    public $number;

    /**
     * @Assert\NotBlank()
     */
    public $createrID;

    /**
     * @Assert\NotBlank()
     */
    public $price;
}
