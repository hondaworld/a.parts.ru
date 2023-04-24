<?php

namespace App\Model\Provider\UseCase\Search\Edit;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @Assert\NotBlank()
     */
    public $price;

    public $quantity;

    public function __construct(float $price, int $quantity)
    {
        $this->price = $price;
        $this->quantity = $quantity;
    }
}
