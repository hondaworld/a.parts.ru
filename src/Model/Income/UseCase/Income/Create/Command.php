<?php

namespace App\Model\Income\UseCase\Income\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     * @Assert\Length(
     *     max="30",
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
     */
    public $providerPriceID;

    /**
     * @Assert\NotBlank()
     */
    public $quantity;
}
