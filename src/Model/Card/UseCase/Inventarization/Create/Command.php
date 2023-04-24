<?php

namespace App\Model\Card\UseCase\Inventarization\Create;

use Symfony\Component\Validator\Constraints as Assert;

class Command
{
    /**
     * @var \DateTime
     * @Assert\NotBlank()
     * @Assert\Type("\DateTime")
     */
    public $dateofadded;

    public function __construct()
    {
        $this->dateofadded = new \DateTime();
    }
}
