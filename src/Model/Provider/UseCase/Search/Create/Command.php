<?php

namespace App\Model\Provider\UseCase\Search\Create;

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
    public $providerPriceID;

    /**
     * @Assert\NotBlank()
     */
    public $price;

    public $quantity;

    public function __construct()
    {

    }
}
